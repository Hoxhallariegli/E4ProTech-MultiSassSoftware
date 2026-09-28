<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('barbers', function (Blueprint $table) { 
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('barber_shop_id')->constrained('barber_shops');
            $table->foreignUuid('user_id')->constrained('users')->nullable();
            $table->string('name');
            $table->string('phone');
            $table->string('photo')->nullable();
            $table->text('bio')->nullable();
            $table->boolean('active');
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('barbers'); } };