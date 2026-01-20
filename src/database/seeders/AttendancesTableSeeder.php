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
        $userA = User::where('email', 'reina.n@coachtech.com')->value('id');
        $userB = User::where('email', 'taro.y@coachtech.com')->value('id');
        $userC = User::where('email', 'issei.m@coachtech.com')->value('id');

        // 念のため存在チェック
        if (! $userA || ! $userB || ! $userC) {
            return;
        }

        // ----- Aさん：1ヶ月分 + 前月2日 + 翌月2日 -----
        $this->createAttendancesForMonth($userA, 2025, 11);
        $this->createExtraDays($userA, 2025, 10, [30, 31]);
        $this->createExtraDays($userA, 2025, 12, [1, 2]);

        // ----- Bさん：2日分 -----
        $this->createSpecificDays($userB, [
            '2025-11-05',
            '2025-11-10',
        ]);

        // ----- Cさん：2日分 -----
        $this->createSpecificDays($userC, [
            '2025-11-02',
            '2025-11-20',
        ]);
    }

    // ---------- 1ヶ月分 ----------
    private function createAttendancesForMonth($userId, $year, $month)
    {
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $this->createOneDay(
                $userId,
                Carbon::create($year, $month, $day)
            );
        }
    }

    // ---------- 特定日 ----------
    private function createSpecificDays($userId, array $days)
    {
        foreach ($days as $day) {
            $this->createOneDay($userId, Carbon::parse($day));
        }
    }

    // ---------- 前月・翌月 ----------
    private function createExtraDays($userId, $year, $month, array $days)
    {
        foreach ($days as $day) {
            $this->createOneDay(
                $userId,
                Carbon::create($year, $month, $day)
            );
        }
    }

    /**
     * ---------- 1日分の勤怠 ----------
     * ・土日/平日 区別なし
     * ・30% の確率で休み
     * ・休みの日は attendance を作らない
     */
    private function createOneDay($userId, Carbon $date)
    {
        // サービス業っぽいランダム休日（30%）
        $isHoliday = rand(1, 100) <= 30;

        Attendance::create([
            'user_id'   => $userId,
            'date'      => $date->toDateString(),

            // 休日なら null、出勤日なら時刻あり
            'clock_in'  => $isHoliday ? null : $date->copy()->setTime(9, 0),
            'clock_out' => $isHoliday ? null : $date->copy()->setTime(18, 0),
            'status'    => 'approved',
            'note'      => null,
        ]);
    }
}
