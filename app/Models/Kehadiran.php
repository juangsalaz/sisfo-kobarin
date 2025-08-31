<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kehadiran extends Model
{
    use HasFactory;

    protected $table = 'kehadiran';   // nama tabel sesuai migration

    protected $fillable = [
        'user_id',
        'event_time',
        'local_time',
        'method',
        'device',
        'raw_id',
        'is_in_session_window',
    ];

    protected $casts = [
        'event_time' => 'datetime',
        'local_time' => 'datetime',
        'is_in_session_window' => 'boolean',
    ];

    // relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // kalau nanti ada relasi ke raw events
    public function raw()
    {
        return $this->belongsTo(AttendanceRawEvent::class, 'raw_id');
    }
}
