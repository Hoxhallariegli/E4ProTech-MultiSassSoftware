<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('bulk_cancellations', function (Blueprint $table) { 
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('barber_shop_id')->constrained('barber_shops');
            $table->foreignId('barber_id')->constrained('barbers');
            $table->datetime('start_at');
            $table->datetime('end_at');
            $table->string('reason')->nullable();
            $table->integer('affected_count')->nullable();
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('bulk_cancellations'); } };