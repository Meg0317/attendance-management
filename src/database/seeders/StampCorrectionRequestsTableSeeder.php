<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;

class StampCorrectionRequestsTableSeeder extends Seeder
{
    public function run()
    {
        // メールでユーザー取得
        $userA = User::where('email', 'reina.n@coachtech.com')->first();
        $userB = User::where('email', 'taro.y@coachtech.com')->first();
        $userC = User::where('email', 'issei.m@coachtech.com')->first();

        // ユーザーが存在する場合のみ処理
        if ($userA) {
            $attA = Attendance::where('user_id', $userA->id)
                ->where('date', '2025-11-05')
                ->first();
            $this->createRequest($userA->id, $attA);
        }

        if ($userB) {
            $attB = Attendance::where('user_id', $userB->id)->first();
            $this->createRequest($userB->id, $attB);
        }

        if ($userC) {
            $attC = Attendance::where('user_id', $userC->id)->first();
            $this->createRequest($userC->id, $attC);
        }
    }

    private function createRequest($userId, $attendance)
    {
        // ★ ここが最重要：null ガード
        if (!$attendance || !$attendance->clock_in) {
            return;
        }

        StampCorrectionRequest::create([
            'user_id'        => $userId,
            'attendance_id'  => $attendance->id,
            'rest_time_id'   => null,
            'before_value'   => $attendance->clock_in,
            'after_value'    => Carbon::parse($attendance->clock_in)->subMinutes(10),
            'reason'         => '電車遅延のため。',
            'status'         => 0, // 申請中
        ]);
    }
}
