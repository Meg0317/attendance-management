<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\RestTime;
use Illuminate\Support\Facades\Auth;
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
        // 今日の勤怠を取得
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', today())
            ->first();

        // 初期状態（まだ出勤していない）
        $status = 'before';

        if ($attendance) {
            // 退勤済み
            if ($attendance->clock_out) {
                $status = 'finished';

            // 休憩中（休憩終了していないレコードがある）
            } elseif ($attendance->restTimes()->whereNull('rest_end')->exists()) {
                $status = 'resting';

            // 出勤中
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
        // すでに今日の出勤があるか確認（二重防止）
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

        // すでに休憩中なら何もしない（二重休憩防止）
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

        // 休憩中のレコードを取得
        $rest = RestTime::where('attendance_id', $attendance->id)
            ->whereNull('rest_end')
            ->latest()
            ->first();

        // 休憩中でなければ何もしない
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

        // すでに退勤済みなら何もしない
        if ($attendance->clock_out) {
            return redirect()->route('attendance.index');
        }

        // 休憩中なら退勤させない
        $resting = $attendance->restTimes()
            ->whereNull('rest_end')
            ->exists();

        if ($resting) {
            return redirect()->route('attendance.index');
        }

        $attendance->update([
            'clock_out' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    public function list()
    {
        $userId = Auth::id();

        // 表示したい月（今月）
        $month = request('month')
            ? Carbon::createFromFormat('Y-m', request('month'))
            : now();

        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        // その月の日付一覧
        $dates = CarbonPeriod::create($start, $end);

        // その月の勤怠データをまとめて取得
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

    public function show($id)
    {
        // attendance が存在する場合（数値 id）
        if (is_numeric($id)) {
            $attendance = Attendance::with(['user', 'restTimes'])
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            return view('attendance.show', [
                'attendance' => $attendance,
            ]);
        }

        // attendance が存在しない日（休日）
        return view('attendance.show', [
            'attendance' => null,
            'user'       => Auth::user(),
            'date'       => now(), // ← list 側で渡せるなら差し替え
        ]);
    }

    /**
     * 修正申請
     */
    public function requestCorrection(
        AttendanceUpdateRequest $request,
        $date
    ) {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', $date)
            ->firstOrFail();

        // 勤怠更新（1回だけ）
        $attendance->update([
            'clock_in'  => $request->clock_in,
            'clock_out' => $request->clock_out,
            'note'      => $request->note,
            'status'    => 'pending',
        ]);

        // 休憩
        foreach ($request->input('rests', []) as $index => $rest) {

            if (empty($rest['start']) && empty($rest['end'])) {
                continue;
            }

            $order = $index + 1;

            $restTime = $attendance->restTimes()
                ->where('order', $order)
                ->firstOrNew(['order' => $order]);

            $restTime->rest_start = $rest['start'] ?: null;
            $restTime->rest_end   = $rest['end'] ?: null;
            $restTime->save();
        }

        return redirect()
            ->route('attendance.detail', $date)
            ->with('success', '修正申請を送信しました');
    }
}