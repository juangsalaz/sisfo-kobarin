<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRawEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'cloud_id','pin','event_time','verify','status_scan',
        'payload','received_at','processed_at',
    ];

    protected $casts = [
        'event_time'  => 'datetime',
        'received_at' => 'datetime',
        'processed_at'=> 'datetime',
        'payload'     => 'array',
    ];
}
