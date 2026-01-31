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
    public function approve(StampCorrectionRequest $stampCorrectionRequest)
    {
        return view('stamp_correction_request.approve', [
            'stampCorrectionRequest' => $stampCorrectionRequest,
        ]);
    }

    // 🔹 承認処理（POST）
    public function approveStore(
        StampCorrectionRequest $stampCorrectionRequest
    ) {
        DB::transaction(function () use ($stampCorrectionRequest) {

            if ($stampCorrectionRequest->status === 1) {
                abort(403);
            }

            $attendance = Attendance::findOrFail(
                $stampCorrectionRequest->attendance_id
            );

            $data = [];

            if (!is_null($stampCorrectionRequest->requested_clock_in)) {
                $data['clock_in'] = $stampCorrectionRequest->requested_clock_in;
            }

            if (!is_null($stampCorrectionRequest->requested_clock_out)) {
                $data['clock_out'] = $stampCorrectionRequest->requested_clock_out;
            }

            if (!is_null($stampCorrectionRequest->requested_note)) {
                $data['note'] = $stampCorrectionRequest->requested_note;
            }

            $attendance->update($data);

            $stampCorrectionRequest->update([
                'status' => 1,
            ]);
        });

        return redirect()
            ->route('admin.stamp_correction_request.list')
            ->with('success', '修正申請を承認しました');
    }
}
