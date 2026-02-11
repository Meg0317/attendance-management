<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\StampCorrectionRequest;
use App\Models\Attendance;

class StampCorrectionRequestController extends Controller
{
    // 一般ユーザー
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'pending');

        $requests = StampCorrectionRequest::where('user_id', Auth::id())
            ->when($tab === 'pending', fn ($q) => $q->where('status', 0))
            ->when($tab === 'approved', fn ($q) => $q->where('status', 1))
            ->with(['user', 'attendance'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('stamp_correction_request.index', [
            'requests' => $requests,
            'tab'      => $tab,
            'isAdmin'  => false,
        ]);
    }

    // 一般ユーザー詳細
    public function show(StampCorrectionRequest $stampRequest)
    {
        // 承認待ち → 確認画面（修正不可）
        if ($stampRequest->status === 0) {
            return redirect()->route(
                'attendance.request.confirm',
                $stampRequest->attendance_id
            );
        }

        // 承認済み → 通常の勤怠詳細（修正可）
        return redirect()->route(
            'attendance.detail',
            $stampRequest->attendance->date->format('Y-m-d')
        );
    }

    // 管理者一覧
    public function adminIndex(Request $request)
    {
        $tab = $request->query('tab', 'pending');

        $requests = StampCorrectionRequest::when($tab === 'pending', fn ($q) => $q->where('status', 0))
            ->when($tab === 'approved', fn ($q) => $q->where('status', 1))
            ->with(['user', 'attendance'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('stamp_correction_request.index', [
            'requests' => $requests,
            'tab'      => $tab,
            'isAdmin'  => true,
        ]);
    }

    // 🔹 承認画面（表示）
    public function approve(StampCorrectionRequest $stampCorrectionRequest)
    {
        return view('stamp_correction_request.approve', [
            'stampCorrectionRequest' => $stampCorrectionRequest,
        ]);
    }

    // 🔹 承認処理（POST）
    public function approveStore(StampCorrectionRequest $stampCorrectionRequest)
    {
        DB::transaction(function () use ($stampCorrectionRequest) {

            if ($stampCorrectionRequest->status === 1) {
                abort(403);
            }

            $attendance = Attendance::with('restTimes')
                ->findOrFail($stampCorrectionRequest->attendance_id);

            $after = $stampCorrectionRequest->after_data;

            /** =========================
             * 出勤・退勤
             ========================= */
            $attendance->update([
                'clock_in'  => $after['clock_in'],
                'clock_out' => $after['clock_out'],
                'note'      => $stampCorrectionRequest->reason,
                'status'    => 'normal',
            ]);

            /** =========================
             * 休憩
             ========================= */
            foreach ($after['rests'] ?? [] as $index => $rest) {
                $order = $index + 1;

                // 両方空 → 削除
                if (empty($rest['start']) && empty($rest['end'])) {
                    $attendance->restTimes()
                        ->where('order', $order)
                        ->delete();
                    continue;
                }

                // 更新 or 作成
                $attendance->restTimes()->updateOrCreate(
                    ['order' => $order],
                    [
                        'rest_start' => $rest['start'],
                        'rest_end'   => $rest['end'],
                    ]
                );
            }

            /** =========================
             * 申請を承認済みに
             ========================= */
            $stampCorrectionRequest->update([
                'status' => 1,
            ]);
        });

        return redirect()
            ->route('admin.stamp_correction_request.list');
    }
}
