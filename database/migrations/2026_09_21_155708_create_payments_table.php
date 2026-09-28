<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('payments', function (Blueprint $table) { 
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('barber_shop_id')->constrained('barber_shops');
            $table->foreignId('booking_id')->constrained('bookings');
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['cash', 'card']);
            $table->enum('status', ['pending', 'paid', 'refunded', 'failed']);
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('payments'); } };