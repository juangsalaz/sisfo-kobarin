<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SesiKegiatanDetail extends Model
{
    use HasFactory;

    protected $table = 'sesi_kegiatan_detail'; // nama tabel sesuai migration

    protected $fillable = [
        'sesi_kegiatan_id',
        'user_id',
        'check_in',
        'late_minutes',
        'status',
        'notes',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'late_minutes' => 'integer',
    ];

    /**
     * Relasi ke SesiKegiatan (master)
     */
    public function sesiKegiatan()
    {
        return $this->belongsTo(SesiKegiatan::class, 'sesi_kegiatan_id');
    }

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
