<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('plans', function (Blueprint $table) { 
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->integer('duration_months');
            $table->integer('max_barbers');
            $table->integer('max_services');
            $table->integer('max_shops');
            $table->boolean('active');
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('plans'); } };