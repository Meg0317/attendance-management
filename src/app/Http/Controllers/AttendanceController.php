<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\RestTime;
use App\Models\StampCorrectionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Http\Requests\AttendanceUpdateRequest;

class AttendanceController extends Controller
{
    /**
     * 打刻画面表示
     */
    public function index()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', today())
            ->first();

        $status = 'before';

        if ($attendance) {
            if ($attendance->clock_out) {
                $status = 'finished';
            } elseif ($attendance->restTimes()->whereNull('rest_end')->exists()) {
                $status = 'resting';
            } else {
                $status = 'working';
            }
        }

        return view('attendance.index', compact('status'));
    }

    /**
     * 出勤
     */
    public function start()
    {
        $already = Attendance::where('user_id', Auth::id())
            ->whereDate('date', today())
            ->exists();

        if ($already) {
            return redirect()->route('attendance.index');
        }

        Attendance::create([
            'user_id'  => Auth::id(),
            'date'     => today(),
            'clock_in' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    /**
     * 休憩開始
     */
    public function restStart()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', today())
            ->firstOrFail();

        $alreadyResting = RestTime::where('attendance_id', $attendance->id)
            ->whereNull('rest_end')
            ->exists();

        if ($alreadyResting) {
            return back();
        }

        RestTime::create([
            'attendance_id' => $attendance->id,
            'rest_start'    => now(),
        ]);

        return back();
    }

    /**
     * 休憩終了
     */
    public function restEnd()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', today())
            ->firstOrFail();

        $rest = RestTime::where('attendance_id', $attendance->id)
            ->whereNull('rest_end')
            ->latest()
            ->first();

        if (! $rest) {
            return back();
        }

        $rest->update([
            'rest_end' => now(),
        ]);

        return back();
    }

    /**
     * 退勤
     */
    public function clockout()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', today())
            ->firstOrFail();

        if ($attendance->clock_out) {
            return redirect()->route('attendance.index');
        }

        if ($attendance->restTimes()->whereNull('rest_end')->exists()) {
            return redirect()->route('attendance.index');
        }

        $attendance->update([
            'clock_out' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    /**
     * 勤怠一覧
     */
    public function list()
    {
        $userId = Auth::id();

        $month = request('month')
            ? Carbon::parse(request('month') . '-01')
            : now()->startOfMonth();

        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        $dates = CarbonPeriod::create($start, $end);

        $attendances = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$start, $end])
            ->with('restTimes')
            ->get()
            ->keyBy(fn ($att) => $att->date->toDateString());

        return view('attendance.list', compact(
            'dates',
            'attendances',
            'month'
        ));
    }

    /**
     * 勤怠詳細（通常）
     */
    public function show(Attendance $attendance)
    {
        if ($attendance->user_id !== Auth::id()) {
            abort(403);
        }

        $attendance->load([
            'user',
            'restTimes' => fn ($q) => $q->orderBy('order'),
        ]);

        // ★ 通常表示：申請なし
        $stampRequest = null;

        return view('attendance.show', compact('attendance', 'stampRequest'));
    }

    /**
     * 修正申請
     */
    public function update(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::with('restTimes')->findOrFail($id);

        // 本人チェック
        if ($attendance->user_id !== Auth::id()) {
            abort(403);
        }

        // 修正前の値を保持
        $beforeClockIn  = $attendance->clock_in;
        $beforeClockOut = $attendance->clock_out;

        // 勤怠更新（申請なので pending）
        $attendance->update([
            'clock_in'  => $request->clock_in,
            'clock_out' => $request->clock_out,
            'note'      => $request->note,
            'status'    => 'pending',
        ]);

        // 申請レコード作成
        StampCorrectionRequest::create([
            'user_id'       => Auth::id(),
            'attendance_id' => $attendance->id,
            'before_value'  => optional($beforeClockIn)?->format('H:i'),
            'after_value'   => $request->clock_in,
            'reason'        => $request->note,
            'status'        => 0, // 承認待ち
        ]);

        // 休憩更新
        foreach ($request->rests ?? [] as $index => $rest) {
            if (empty($rest['start']) && empty($rest['end'])) {
                continue;
            }

            $attendance->restTimes()->updateOrCreate(
                ['order' => $index + 1],
                [
                    'rest_start' => $rest['start'],
                    'rest_end'   => $rest['end'],
                ]
            );
        }

        return redirect()
            ->route('attendance.request.confirm', $attendance->id);
    }

    /**
     * 修正申請後・申請一覧からの確認（readonly）
     */
    public function requestConfirm(Attendance $attendance)
    {
        if ($attendance->user_id !== Auth::id()) {
            abort(403);
        }

        $attendance->load([
            'user',
            'restTimes' => fn ($q) => $q->orderBy('order'),
        ]);

        $stampRequest = StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 0) // pending
            ->latest()
            ->first();

        return view('attendance.show', compact('attendance', 'stampRequest'));
    }
}
