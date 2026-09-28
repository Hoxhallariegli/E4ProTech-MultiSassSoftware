<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('bookings', function (Blueprint $table) { 
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('barber_shop_id')->constrained('barber_shops');
            $table->foreignId('barber_id')->constrained('barbers');
            $table->foreignId('service_id')->constrained('services');
            $table->foreignId('customer_id')->constrained('customers');
            $table->datetime('appointment_at');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled', 'no-show']);
            $table->decimal('total_price', 12, 2);
            $table->text('notes')->nullable();
            $table->enum('source', ['online', 'walk-in', 'phone']);
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('bookings'); } };