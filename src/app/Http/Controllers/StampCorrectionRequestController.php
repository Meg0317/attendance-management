<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StampCorrectionRequest;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'pending');

        $requests = StampCorrectionRequest::where('user_id', Auth::id())
            ->when($tab === 'pending', function ($q) {
                $q->where('status', 0); // 申請中
            })
            ->when($tab === 'approved', function ($q) {
                $q->where('status', 1); // 承認済み
            })
            ->with(['user', 'attendance'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('stamp_correction_request.index', compact('requests', 'tab'));
    }

    public function show(StampCorrectionRequest $request)
    {
        // 修正押下後と同じ画面へ
        return redirect()->route(
            'attendance.request.confirm',
            $request->attendance_id
        );
    }
}
