<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestTime extends Model
{
    use HasFactory;
    protected $guarded = ['id',];

    public function attendance() {
        return $this->belongsTo(Attendance::class);
    }

    public function stampCorrectionRequests() {
        return $this->hasMany(StampCorrectionRequest::class);
    }
}
