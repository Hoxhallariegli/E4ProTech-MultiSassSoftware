<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('customers', function (Blueprint $table) { 
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('barber_shop_id')->constrained('barber_shops');
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('photo')->nullable();
            $table->integer('total_bookings');
            $table->integer('no_show_count');
            $table->datetime('blocked_at')->nullable();
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('customers'); } };