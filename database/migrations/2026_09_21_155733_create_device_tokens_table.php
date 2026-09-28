<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('device_tokens', function (Blueprint $table) { 
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('barber_shop_id')->constrained('barber_shops');
            $table->foreignUuid('user_id')->constrained('users');
            $table->string('fcm_token');
            $table->enum('platform', ['android', 'ios']);
            $table->datetime('last_used_at')->nullable();
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('device_tokens'); } };