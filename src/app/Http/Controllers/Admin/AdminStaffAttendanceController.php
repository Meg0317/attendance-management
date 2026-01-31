<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AdminStaffAttendanceController extends Controller
{
    /**
     * スタッフ別 月次勤怠一覧（管理者）
     */
    public function list(Request $request, User $user)
    {
        // 表示する月（指定がなければ今月）
        $month = $request->month
            ? Carbon::parse($request->month . '-01')
            : now()->startOfMonth();

        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        // カレンダー用日付一覧
        $dates = CarbonPeriod::create($start, $end);

        // 対象スタッフの勤怠
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->with('restTimes')
            ->get()
            ->keyBy(fn ($att) => $att->date->toDateString());

        return view('attendance.list', [
            'dates'       => $dates,
            'attendances' => $attendances,
            'month'       => $month,
            'user'        => $user,   // ★ 表示対象（スタッフ）
            'isAdmin'     => true,    // ★ 管理者表示
        ]);
    }
}
