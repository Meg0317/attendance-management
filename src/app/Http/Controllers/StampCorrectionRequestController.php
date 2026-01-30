<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\StampCorrectionRequest;

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
        return redirect()->route(
            'attendance.request.confirm',
            $stampRequest->attendance_id
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
    public function approve($attendance_correct_request_id)
    {
        $request = StampCorrectionRequest::with(['user', 'attendance'])
            ->findOrFail($attendance_correct_request_id);

        // すでに承認済みは弾く（保険）
        if ($request->status === 1) {
            abort(403, 'すでに承認済みです');
        }

        return view('stamp_correction_request.approve', [
            'request' => $request,
        ]);
    }

    // 🔹 承認処理（POST）
    public function approveStore(Request $request)
    {
        DB::transaction(function () use ($request) {

            $stampRequest = StampCorrectionRequest::findOrFail(
                $request->stamp_correction_request_id
            );

            if ($stampRequest->status === 1) {
                abort(403);
            }

            $attendance = Attendance::findOrFail(
                $stampRequest->attendance_id
            );

            // 勤怠を修正内容で更新
            $attendance->update([
                'clock_in'  => $stampRequest->requested_clock_in,
                'clock_out' => $stampRequest->requested_clock_out,
                'note'      => $stampRequest->requested_note,
            ]);

            // 承認済みに変更
            $stampRequest->update([
                'status' => 1,
            ]);
        });

        return redirect()
            ->route('admin.stamp_correction_request.list')
            ->with('success', '修正申請を承認しました');
    }
}
