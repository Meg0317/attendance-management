<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Http\Requests\AttendanceUpdateRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;


class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = Carbon::parse($request->date ?? today());

        $attendances = Attendance::with('user')
            ->whereDate('date', $date)
            ->get();

        return view('admin.attendance.list', compact('date', 'attendances'));
    }

    public function show(Attendance $attendance)
    {
        return view('admin.attendance.show', compact('attendance'));
    }

    public function showByDate(Request $request, $userId, $date)
    {
        $attendance = Attendance::with(['restTimes', 'user'])
            ->where('user_id', $userId)
            ->whereDate('date', $date)
            ->first();

        return view('admin.attendance.show', [
            'attendance' => $attendance,
            'user'       => User::findOrFail($userId),
            'date'       => Carbon::parse($date),
        ]);
    }

    public function update(AttendanceUpdateRequest $request, Attendance $attendance)
    {
        // 承認待ちは修正不可
        if ($attendance->status === 'pending') {
            return back();
        }

        // 勤怠更新
        $attendance->update([
            'clock_in'  => $request->clock_in,
            'clock_out' => $request->clock_out,
            'note'      => $request->note,
        ]);

        // 休憩更新
        foreach ($request->rests ?? [] as $index => $rest) {
            $attendance->restTimes()->updateOrCreate(
                ['order' => $index + 1],
                [
                    'rest_start' => $rest['start'] ?? null,
                    'rest_end'   => $rest['end'] ?? null,
                ]
            );
        }

        return redirect()
            ->route('admin.attendance.show', $attendance)
            ->with('success', '勤怠を修正しました');
    }
}





