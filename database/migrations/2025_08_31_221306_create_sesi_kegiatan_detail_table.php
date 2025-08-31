<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sesi_kegiatan_detail', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->foreignId('sesi_kegiatan_id');
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->datetime('check_in')->nullable();
            $t->unsignedInteger('late_minutes')->default(0);
            $t->enum('status', ['hadir','terlambat','tidak_hadir'])->default('tidak_hadir');
            $t->text('notes')->nullable();
            $t->timestamps();

            $t->unique(['sesi_kegiatan_id','user_id']);
            $t->index(['sesi_kegiatan_id','status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_kegiatan_detail');
    }
};
