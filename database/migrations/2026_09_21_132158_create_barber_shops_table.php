<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('barber_shops', function (Blueprint $table) { 
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignUuid('owner_id')->constrained('users');
            $table->string('name');
            $table->string('app_name');
            $table->string('slug');
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->string('primary_color');
            $table->string('secondary_color');
            $table->datetime('trial_ends_at')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->boolean('active');
            $table->boolean('sms_enabled');
            $table->string('timezone');
            $table->integer('max_no_show_before_block')->nullable();
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('barber_shops'); } };