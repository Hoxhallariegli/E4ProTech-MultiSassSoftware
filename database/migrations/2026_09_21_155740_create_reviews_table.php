<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('reviews', function (Blueprint $table) { 
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('barber_shop_id')->constrained('barber_shops');
            $table->foreignId('barber_id')->constrained('barbers');
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('booking_id')->constrained('bookings');
            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('reviews'); } };