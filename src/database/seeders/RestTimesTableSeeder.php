<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\RestTime;
use Carbon\Carbon;

class RestTimesTableSeeder extends Seeder
{
    public function run()
    {
        $attendances = Attendance::whereNotNull('clock_in')
            ->whereNotNull('clock_out')
            ->get();

        foreach ($attendances as $att) {

            $clockIn  = Carbon::parse($att->clock_in);
            $clockOut = Carbon::parse($att->clock_out);

            // 1回 or 2回休憩
            $breakCount = rand(0, 1) ? 2 : 1;
            $durations = $breakCount === 2 ? [30, 30] : [60];

            // 出勤～退勤の間で安全な開始時間
            $baseStart = $clockIn->copy()->addHours(3);

            foreach ($durations as $i => $minutes) {

                $start = $baseStart->copy()->addHours($i);
                $end   = $start->copy()->addMinutes($minutes);

                // 退勤を超える場合は作らない
                if ($end->gt($clockOut)) {
                    continue;
                }

                RestTime::create([
                    'attendance_id' => $att->id,
                    'order'         => $i + 1,
                    'rest_start'    => $start,
                    'rest_end'      => $end,
                ]);
            }
        }
    }
}
