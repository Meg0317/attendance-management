<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    protected $fillable = [
        'user_id',
        'attendance_id',
        'before_data',
        'after_data',
        'reason',
        'status',
    ];

    protected $casts = [
        'before_data' => 'array',
        'after_data'  => 'array',
    ];

    const STATUS_PENDING  = 0; // 承認待ち
    const STATUS_APPROVED = 1; // 承認

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function attendance() {
        return $this->belongsTo(Attendance::class);
    }
}
