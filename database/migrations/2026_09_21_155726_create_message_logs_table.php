<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('message_logs', function (Blueprint $table) { 
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('barber_shop_id')->constrained('barber_shops');
            $table->foreignId('customer_id')->constrained('customers');
            $table->enum('channel', ['sms', 'whatsapp']);
            $table->text('message');
            $table->enum('status', ['sent', 'failed']);
            $table->datetime('sent_at')->nullable();
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('message_logs'); } };