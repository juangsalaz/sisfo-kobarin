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
        Schema::create('kegiatan', function (Blueprint $table) {
            $table->id();
            $table->enum('weekday', ['mon','thu']);      // senin=mon, kamis=thu
            $table->time('start_time');                  // 19:45:00
            $table->time('end_time');                    // 21:15:00
            $table->unsignedInteger('grace_in_minutes')->default(15); // toleransi telat
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan');
    }
};
