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
            ->firstOrCreate(
                [
                    'user_id' => Auth::id(),
                    'date'    => $date,
                ],
                [
                    'status' => 'normal',
                ]
            );

        return view('attendance.show', [
            'attendance' => $attendance,
            'user'       => Auth::user(),
            'date'       => $date,
        ]);
    }

    /**
     * 修正申請（登録 or 更新）
     */
    public function storeOrUpdate(AttendanceUpdateRequest $request)
    {
        // 空文字 → null 正規化用
        $normalize = fn ($v) => $v === '' ? null : $v;

        // 何も入力されていない送信は無視
        if (
            empty($request->clock_in) &&
            empty($request->clock_out) &&
            empty($request->note)
        ) {
            return back();
        }

        $attendance = Attendance::with('restTimes')
            ->firstOrNew([
                'user_id' => Auth::id(),
                'date'    => $request->date,
            ]);

        /** =========================
         * 変更前の値
         ========================= */
        $beforeClockIn  = optional($attendance->clock_in)?->format('H:i');
        $beforeClockOut = optional($attendance->clock_out)?->format('H:i');
        $beforeNote     = $attendance->note;

        /** =========================
         * Attendance 更新
         ========================= */
        $attendance->fill([
            'clock_in'  => $normalize($request->clock_in),
            'clock_out' => $normalize($request->clock_out),
            'note'      => $normalize($request->note),
            'status'    => 'pending',
        ]);

        $attendance->save();

        /** =========================
         * 休憩時間 更新
         ========================= */
        foreach ($request->rests ?? [] as $index => $rest) {
            if (empty($rest['start']) && empty($rest['end'])) {
                continue;
            }

            $attendance->restTimes()->updateOrCreate(
                ['order' => $index + 1],
                [
                    'rest_start' => $rest['start'] ?? null,
                    'rest_end'   => $rest['end'] ?? null,
                ]
            );
        }

        /** =========================
         * 実際の変更判定（null / 空文字対策済）
         ========================= */
        $changed =
            $normalize($beforeClockIn)  !== $normalize($request->clock_in) ||
            $normalize($beforeClockOut) !== $normalize($request->clock_out) ||
            $normalize($beforeNote)     !== $normalize($request->note);

        /** =========================
         * 既存 pending 確認
         ========================= */
        $alreadyPending = StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 0)
            ->exists();

        /** =========================
         * 修正あり + 備考あり のみ申請作成
         ========================= */
        if (
            $changed &&
            !$alreadyPending &&
            filled($request->note)
        ) {
            StampCorrectionRequest::create([
                'user_id'       => Auth::id(),
                'attendance_id' => $attendance->id,
                'before_value'  => $beforeClockIn,
                'after_value'   => $request->clock_in,
                'reason'        => $request->note,
                'status'        => 0,
            ]);
        }

        return redirect()
            ->route('attendance.detail', $attendance->date->format('Y-m-d'));
    }

    /**
     * 申請確認
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
}
