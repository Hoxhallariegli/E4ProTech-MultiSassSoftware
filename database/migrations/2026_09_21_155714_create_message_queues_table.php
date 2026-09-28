<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('message_queues', function (Blueprint $table) { 
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('barber_shop_id')->constrained('barber_shops');
            $table->foreignId('booking_id')->constrained('bookings');
            $table->enum('channel', ['sms', 'whatsapp']);
            $table->string('phone_number');
            $table->text('message_content');
            $table->datetime('scheduled_at');
            $table->enum('status', ['pending', 'processing', 'sent', 'failed', 'skipped_limit']);
            $table->integer('retry_count');
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('message_queues'); } };