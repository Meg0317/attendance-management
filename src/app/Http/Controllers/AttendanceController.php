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
     * 打刻画面
     */
    public function index()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', today())
            ->with('restTimes')
            ->first();

        $status = 'before';

        if ($attendance) {
            if ($attendance->clock_out) {
                $status = 'finished';
            } elseif ($attendance->restTimes->whereNull('rest_end')->isNotEmpty()) {
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

        // すでに休憩中なら何もしない
        if ($attendance->restTimes()->whereNull('rest_end')->exists()) {
            return back();
        }

        $order = $attendance->restTimes()->count() + 1;

        RestTime::create([
            'attendance_id' => $attendance->id,
            'rest_start'    => now(),
            'order'         => $order,
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

        $rest = $attendance->restTimes()
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
            ->with('restTimes')
            ->firstOrFail();

        if ($attendance->clock_out) {
            return redirect()->route('attendance.index');
        }

        // 休憩中は退勤不可
        if ($attendance->restTimes->whereNull('rest_end')->isNotEmpty()) {
            return redirect()->route('attendance.index');
        }

        $attendance->update([
            'clock_out' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    /**
     * 勤怠一覧（月次）
     */
    public function list()
    {
        $user = Auth::user();

        $month = request('month')
            ? Carbon::parse(request('month') . '-01')
            : now()->startOfMonth();

        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        $dates = CarbonPeriod::create($start, $end);

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->with('restTimes')
            ->get()
            ->keyBy(fn ($a) => $a->date->toDateString());

        return view('attendance.list', [
            'dates'       => $dates,
            'attendances' => $attendances,
            'month'       => $month,
            'user'        => $user,
            'isAdmin'     => false,
        ]);
    }

    /**
     * 勤怠詳細（date基準・空日OK）
     */
    public function show(string $date)
    {
        $date = Carbon::parse($date);

        $attendance = Attendance::with('restTimes')
            ->where('user_id', Auth::id())
            ->whereDate('date', $date)
            ->first(); // null OK

        return view('attendance.show', [
            'attendance' => $attendance,
            'user'       => Auth::user(),
            'date'       => $date,
        ]);
    }

    /**
     * 修正申請後 確認
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
            ->where('status', 0)
            ->latest()
            ->first();

        return view('attendance.show', [
            'attendance'   => $attendance,
            'user'         => Auth::user(),
            'date'         => $attendance->date,
            'stampRequest' => $stampRequest,
        ]);
    }

    /**
     * 修正申請（登録 or 更新）
     */
    public function storeOrUpdate(AttendanceUpdateRequest $request)
    {
        $attendance = Attendance::with('restTimes')->firstOrNew([
            'user_id' => Auth::id(),
            'date'    => $request->date,
        ]);

        $attendance->fill([
            'clock_in'  => $request->clock_in,
            'clock_out' => $request->clock_out,
            'note'      => $request->note,
            'status'    => 'pending',
        ]);

        $attendance->save();

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

        StampCorrectionRequest::create([
            'user_id'       => Auth::id(),
            'attendance_id' => $attendance->id,
            'before_value'  => optional($attendance->clock_in)?->format('H:i'),
            'after_value'   => $request->clock_in,
            'reason'        => $request->note,
            'status'        => 0,
        ]);

        return redirect()
            ->route('attendance.detail', $attendance->date->format('Y-m-d'));
    }
}
