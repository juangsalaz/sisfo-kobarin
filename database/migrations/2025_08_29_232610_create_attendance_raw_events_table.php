<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('attendance_raw_events', function (Blueprint $t) {
      $t->bigIncrements('id');
      $t->string('cloud_id')->nullable();          // dari webhook
      $t->string('pin');                            // data.pin
      $t->timestampTz('event_time');               // parse data.scan -> UTC
      $t->string('verify')->nullable();            // data.verify (disimpan mentah)
      $t->string('status_scan')->nullable();       // data.status_scan (disimpan mentah)
      $t->json('payload')->nullable();             // simpan JSON utuh untuk audit
      $t->timestampTz('received_at')->useCurrent();
      $t->timestampTz('processed_at')->nullable();

      // Idempotensi: kombinasi alami (cloud_id + pin + event_time)
      $t->unique(['cloud_id','pin','event_time']);

      $t->index(['pin','event_time']);
    });
  }
  
  public function down(): void {
    Schema::dropIfExists('attendance_raw_events');
  }
};
