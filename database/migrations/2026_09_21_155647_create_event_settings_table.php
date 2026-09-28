<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('event_settings', function (Blueprint $table) { 
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('barber_shop_id')->constrained('barber_shops');
            $table->foreignId('realtime_event_id')->constrained('realtime_events');
            $table->boolean('reverb_enabled');
            $table->boolean('firebase_enabled');
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('event_settings'); } };