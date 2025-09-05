<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Kegiatan extends Model
{
    use HasFactory;

    protected $table = 'kegiatan'; // nama tabel sesuai migration

    protected $fillable = [
        'weekday',
        'start_time',
        'end_time',
        'grace_in_minutes',
    ];

    protected $casts = [
        'start_time' => 'string',
        'end_time'   => 'string',
        'grace_in_minutes' => 'integer',
        'check_in' => 'datetime'
    ];

    /**
     * Accessor untuk nama hari dalam bahasa Indonesia
     */
    public function getHariAttribute()
    {
        return $this->weekday === 'mon' ? 'Senin' : 'Kamis';
    }

    /**
     * Accessor untuk jam kegiatan (mis. "19:45 - 21:15")
     */
    public function getJamKegiatanAttribute()
    {
        return Carbon::parse($this->start_time)->format('H:i') . ' - ' .
               Carbon::parse($this->end_time)->format('H:i');
    }

    /**
     * Relasi ke sesi kegiatan (occurrence per tanggal)
     */
    public function sesi()
    {
        return $this->hasMany(SesiKegiatan::class, 'kegiatan_id');
    }
}
