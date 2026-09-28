<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('subscriptions', function (Blueprint $table) { 
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('barber_shop_id')->constrained('barber_shops');
            $table->foreignId('plan_id')->constrained('plans');
            $table->datetime('starts_at');
            $table->datetime('ends_at');
            $table->enum('status', ['trial', 'active', 'expired', 'cancelled']);
            $table->boolean('auto_renew');
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('subscriptions'); } };