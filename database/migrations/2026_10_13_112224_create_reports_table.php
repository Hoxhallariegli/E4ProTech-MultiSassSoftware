<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('reports', function (Blueprint $table) { 
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->enum('period_type', ['daily', 'weekly', 'monthly', 'custom']);
            $table->date('report_date');
            $table->integer('total_bookings');
            $table->decimal('total_revenue', 12, 2);
            $table->integer('total_completed');
            $table->integer('total_cancelled');
            $table->text('notes')->nullable();
            $table->foreignId('barber_id')->constrained('barbers')->nullable();
            $table->foreignId('barber_shop_id')->constrained('barber_shops');
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('reports'); } };