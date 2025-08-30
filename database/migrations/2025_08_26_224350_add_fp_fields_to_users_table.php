<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false);

            $table->string('pin')->unique()->nullable();           // PIN di mesin
            $table->unsignedTinyInteger('privilege')->default(0);  // 0=user, 1=admin (sesuaikan kebutuhan)
            $table->string('fp_password')->nullable();             // password mesin (akan dienkripsi)
            $table->string('rfid')->nullable();                    // kartu RFID (opsional)
            $table->longText('fp_template')->nullable();           // template sidik jari (jika perlu simpan)
            $table->timestamp('synced_at')->nullable();
            $table->string('last_sync_status')->nullable();
        });
    }
    
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_admin','pin','privilege','fp_password','rfid',
                'fp_template','synced_at','last_sync_status'
            ]);
        });
    }
};
