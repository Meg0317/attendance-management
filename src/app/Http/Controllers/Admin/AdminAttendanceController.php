<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Http\Requests\AttendanceUpdateRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAttendanceController extends Controller
{
    /**
     * 勤怠一覧（日別）
     */
    public function index(Request $request)
    {
        $date = Carbon::parse($request->date ?? today());

        $attendances = Attendance::with('user')
            ->whereDate('date', $date)
            ->get();

        return view('admin.attendance.list', compact('date', 'attendances'));
    }

    /**
     * 勤怠詳細（★空日OK）
     */
    public function show(User $user, string $date)
    {
        $date = Carbon::parse($date);

        $attendance = Attendance::with(['user', 'restTimes'])
            ->where('user_id', $user->id)
            ->whereDate('date', $date)
            ->first(); // ← null OK

        return view('admin.attendance.show', [
            'attendance' => $attendance,
            'user'       => $user,
            'date'       => $date,
        ]);
    }

    /**
     * 登録 or 更新
     */
    public function storeOrUpdate(AttendanceUpdateRequest $request)
    {
        $attendance = Attendance::with('restTimes')->firstOrNew([
            'user_id' => $request->user_id,
            'date'    => $request->date,
        ]);

        // 承認待ちは修正不可（既存のみ）
        if ($attendance->exists && $attendance->status === 'pending') {
            return back();
        }

        $attendance->fill([
            'clock_in'  => $request->clock_in,
            'clock_out' => $request->clock_out,
            'note'      => $request->note,
        ]);

        $attendance->save();

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
            ->route('admin.attendance.show', [
                'user' => $attendance->user_id,
                'date' => $attendance->date->format('Y-m-d'),
            ])
            ->with('success', '勤怠を修正しました');
    }

    /**
     * CSV出力
     */
    public function exportCsv(Request $request)
    {
        $month  = Carbon::createFromFormat('Y-m', $request->month);
        $userId = $request->user;

        $attendances = Attendance::where('user_id', $userId)
            ->whereBetween('date', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ])
            ->orderBy('date')
            ->get();

        $headers = ['日付', '出勤', '退勤', '休憩', '合計'];

        return new StreamedResponse(function () use ($headers, $attendances) {
            $fp = fopen('php://output', 'w');

            mb_convert_variables('SJIS-win', 'UTF-8', $headers);
            fputcsv($fp, $headers);

            foreach ($attendances as $attendance) {
                $row = [
                    $attendance->date->format('Y/m/d'),
                    optional($attendance->clock_in)->format('H:i'),
                    optional($attendance->clock_out)->format('H:i'),
                    $attendance->rest_time ? gmdate('G:i', $attendance->rest_time) : '',
                    $attendance->work_time ? gmdate('G:i', $attendance->work_time) : '',
                ];

                mb_convert_variables('SJIS-win', 'UTF-8', $row);
                fputcsv($fp, $row);
            }

            fclose($fp);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendance_' . $month->format('Ym') . '.csv"',
        ]);
    }
}
