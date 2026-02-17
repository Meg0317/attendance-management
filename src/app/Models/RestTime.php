<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestTime extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'rest_start' => 'datetime',
        'rest_end'   => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
