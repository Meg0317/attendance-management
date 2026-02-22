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
     * 出勤打刻
     */
    public function start()
    {
        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'date'    => today(),
            ]
        );

        if ($attendance->clock_in) {
            return back(); // すでに出勤済み
        }

        $attendance->update([
            'clock_in' => now(),
            'status'   => 'working',
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

        RestTime::create([
            'attendance_id' => $attendance->id,
            'rest_start'    => now(),
            'order'         => $attendance->restTimes()->count() + 1,
        ]);

        return redirect()->route('attendance.index');
    }

    /**
     * 休憩終了
     */
    public function restEnd()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', today())
            ->with('restTimes')
            ->firstOrFail();

        $rest = $attendance->restTimes()
            ->whereNull('rest_end')
            ->latest()
            ->first();

        if ($rest) {
            $rest->update([
                'rest_end' => now(),
            ]);
        }

        return redirect()->route('attendance.index');
    }

    /**
     * 退勤打刻
     */
    public function clockout()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', today())
            ->firstOrFail();

        $attendance->update([
            'clock_out' => now(),
            'status'    => 'finished',
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
        $date = Carbon::parse($date)->toDateString();

        $attendance = Attendance::with('restTimes')
            ->where('user_id', Auth::id())
            ->whereDate('date', $date)
            ->first();

        // 🔥 無ければ「保存しない」仮オブジェクトを作る
        if (!$attendance) {
            $attendance = new Attendance([
                'user_id' => Auth::id(),
                'date'    => $date,
                'status'  => 'normal',
            ]);
        }

        $latestRequest = null;

        if ($attendance->exists) {
            $latestRequest = StampCorrectionRequest::where('attendance_id', $attendance->id)
                ->latest()
                ->first();
        }

        $readonly = $latestRequest
            && $latestRequest->status === StampCorrectionRequest::STATUS_PENDING;

        return view('attendance.show', compact(
            'attendance',
            'latestRequest',
            'readonly'
        ));
    }


    /**
     * 修正申請（登録 or 更新）
     */
    public function storeOrUpdate(AttendanceUpdateRequest $request)
    {
        // 何も入力がなければ何もしない
        if (
            empty($request->clock_in) &&
            empty($request->clock_out) &&
            empty($request->rests) &&
            empty($request->note)
        ) {
            return back();
        }

        // 勤怠は「存在保証」だけ
        $attendance = Attendance::with('restTimes')
            ->firstOrCreate(
                [
                    'user_id' => Auth::id(),
                    'date'    => $request->date,
                ],
                [
                    'status' => 'pending',
                ]
            );

        // すでに承認待ちがあれば二重申請させない
        $alreadyPending = StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', StampCorrectionRequest::STATUS_PENDING)
            ->exists();

        if ($alreadyPending) {
            return back();
        }

        /** =========================
         * before_data
         ========================= */
        $beforeData = [
            'clock_in'  => optional($attendance->clock_in)?->format('H:i'),
            'clock_out' => optional($attendance->clock_out)?->format('H:i'),
            'rests'     => $attendance->restTimes
                ->sortBy('order')
                ->values()
                ->map(fn ($r) => [
                    'start' => optional($r->rest_start)?->format('H:i'),
                    'end'   => optional($r->rest_end)?->format('H:i'),
                ])
                ->toArray(),
        ];

        /** =========================
         * after_data
         ========================= */
        $afterData = [
            'clock_in'  => $request->clock_in,
            'clock_out' => $request->clock_out,
            'rests'     => collect($request->rests ?? [])
                ->map(fn ($r) => [
                    'start' => $r['start'] ?? null,
                    'end'   => $r['end'] ?? null,
                ])
                ->toArray(),
        ];

        /** =========================
         * 修正申請を 1 レコード作成
         ========================= */
        StampCorrectionRequest::create([
            'user_id'       => Auth::id(),
            'attendance_id' => $attendance->id,
            'before_data'   => $beforeData,
            'after_data'    => $afterData,
            'reason'        => $request->note,
            'status'        => StampCorrectionRequest::STATUS_PENDING,
        ]);

        // 勤怠は承認待ち状態に
        $attendance->update([
            'status' => 'pending',
        ]);

        return redirect()
        ->route('attendance.detail', [
            'date' => $attendance->date->format('Y-m-d'),
        ]);
    }


    /**
     * 申請確認
     */
    public function requestConfirm(Attendance $attendance)
    {
        if ($attendance->user_id !== Auth::id()) {
            abort(403);
        }

        return redirect()->route('attendance.detail', [
            'date' => $attendance->date->format('Y-m-d'),
        ]);
    }
}
