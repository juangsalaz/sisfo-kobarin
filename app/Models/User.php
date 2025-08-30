<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name','email','password','is_admin',
        'pin','privilege','fp_password','rfid','fp_template', 'no_hp',
    ];

    protected $hidden = ['password','remember_token','fp_password'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
        // Enkripsi transparan kolom sensitif (tetap bisa kirim plaintext ke API saat perlu)
        'fp_password' => 'encrypted',
    ];
}
