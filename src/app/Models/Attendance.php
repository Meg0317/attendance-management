<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'date'      => 'date',
        'clock_in'  => 'datetime',
        'clock_out' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | リレーション
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restTimes()
    {
        return $this->hasMany(RestTime::class);
    }

    public function stampCorrectionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }

    /*
    |--------------------------------------------------------------------------
    | アクセサ（計算）
    |--------------------------------------------------------------------------
    */

    /**
     * 休憩合計時間（秒）
     */
    public function getRestTimeAttribute()
    {
        $seconds = 0;

        foreach ($this->restTimes as $rest) {
            if ($rest->rest_start && $rest->rest_end) {
                $seconds += $rest->rest_end->diffInSeconds($rest->rest_start);
            }
        }

        return $seconds;
    }
        /**
         * 勤務時間（秒）
         */
    public function getWorkTimeAttribute()
    {
        if (! $this->clock_in || ! $this->clock_out) {
            return null;
        }

        return $this->clock_out->diffInSeconds($this->clock_in)
            - $this->rest_time;
    }
}
