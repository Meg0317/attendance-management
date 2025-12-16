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
        $attendances = Attendance::whereNotNull('clock_in')->get();

        foreach ($attendances as $att) {

            $breaks = rand(1, 2); // 1〜2回の休憩

            for ($i = 0; $i < $breaks; $i++) {

                $start = Carbon::parse($att->clock_in)->addHours(2 + $i);
                $end   = $start->copy()->addMinutes(30);

                RestTime::create([
                    'attendance_id' => $att->id,
                    'rest_start' => $start,
                    'rest_end' => $end,
                ]);
            }
        }
    }
}
