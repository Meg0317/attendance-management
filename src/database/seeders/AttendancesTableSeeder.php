<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    public function run()
    {
        // --- 対象ユーザーをメールで取得（IDが変わっても安全） ---
        $userA = User::where('email', 'reina.n@coachtech.com')->first()->id;
        $userB = User::where('email', 'taro.y@coachtech.com')->first()->id;
        $userC = User::where('email', 'issei.m@coachtech.com')->first()->id;

        // ----- Aさん：1ヶ月分 + 前月2日 + 翌月2日 -----
        $this->createAttendancesForMonth($userA, 2025, 11); // 11月の全件
        $this->createExtraDays($userA, 2025, 10, [30, 31]); // 前月
        $this->createExtraDays($userA, 2025, 12, [1, 2]);   // 翌月

        // ----- Bさん：2日分 -----
        $this->createSpecificDays($userB, [
            ['2025-11-05'],
            ['2025-11-10'],
        ]);

        // ----- Cさん：2日分 -----
        $this->createSpecificDays($userC, [
            ['2025-11-02'],
            ['2025-11-20'],
        ]);
    }

    // ---------- 1ヶ月分の出退勤 ----------
    private function createAttendancesForMonth($userId, $year, $month)
    {
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $this->createOneDay($userId, Carbon::create($year, $month, $d));
        }
    }

    // ---------- 特定日 ----------
    private function createSpecificDays($userId, array $days)
    {
        foreach ($days as $day) {
            $this->createOneDay($userId, Carbon::parse($day[0]));
        }
    }

    // ---------- 前月・翌月 ----------
    private function createExtraDays($userId, $year, $month, array $days)
    {
        foreach ($days as $d) {
            $this->createOneDay($userId, Carbon::create($year, $month, $d));
        }
    }

    // ---------- 1日分の出勤データ（休憩は別 Seeder で生成） ----------
    private function createOneDay($userId, Carbon $date)
    {
        $isHoliday = rand(1, 10) === 1; // 10％は休み

        Attendance::create([
            'user_id' => $userId,
            'date' => $date->toDateString(),
            'clock_in' => $isHoliday ? null : $date->copy()->setTime(9, 0),
            'clock_out' => $isHoliday ? null : $date->copy()->setTime(18, 0),
            'total_work_time' => $isHoliday ? null : '08:00',
        ]);
    }
}
