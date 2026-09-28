<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('notification_channels', function (Blueprint $table) { 
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('barber_shop_id')->constrained('barber_shops');
            $table->enum('channel', ['sms', 'whatsapp']);
            $table->boolean('enabled');
            $table->integer('daily_limit')->nullable();
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('notification_channels'); } };