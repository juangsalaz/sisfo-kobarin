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
        Schema::create('sesi_kegiatan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('session_date');
            $table->enum('weekday', ['mon','thu']);
            $table->datetime('start_at_local');
            $table->datetime('end_at_local');
            $table->timestamps();

            $table->unique(['session_date','weekday']);
            $table->index('session_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_kegiatan');
    }
};
