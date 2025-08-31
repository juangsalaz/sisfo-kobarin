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
        Schema::create('kehadiran', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->datetime('event_time');
            $table->datetime('local_time');
            $table->string('method')->nullable();
            $table->string('device')->nullable();
            $table->unsignedBigInteger('raw_id');
            $table->boolean('is_in_session_window')->default(false);
            $table->timestamps();
            $table->index(['user_id','local_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kehadiran');
    }
};
