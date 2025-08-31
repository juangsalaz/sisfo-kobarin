<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class SesiKegiatan extends Model
{
    use HasFactory;

    protected $table = 'sesi_kegiatan'; // nama tabel sesuai migration

    protected $fillable = [
        'session_date',
        'weekday',
        'start_at_local',
        'end_at_local',
    ];

    protected $casts = [
        'session_date'   => 'date',
        'start_at_local' => 'datetime',
        'end_at_local'   => 'datetime',
    ];

    /**
     * Accessor: tampilkan nama hari lengkap dari field weekday
     */
    public function getHariAttribute()
    {
        return $this->weekday === 'mon' ? 'Senin' : 'Kamis';
    }

    /**
     * Accessor: range waktu dalam format jam
     */
    public function getJamKegiatanAttribute()
    {
        return Carbon::parse($this->start_at_local)->format('H:i') . ' - ' .
               Carbon::parse($this->end_at_local)->format('H:i');
    }

    // Relasi ke hasil kehadiran (kalau ada tabel AttendanceSessionResult)
    public function results()
    {
        return $this->hasMany(AttendanceSessionResult::class, 'session_occurrence_id');
    }
}
