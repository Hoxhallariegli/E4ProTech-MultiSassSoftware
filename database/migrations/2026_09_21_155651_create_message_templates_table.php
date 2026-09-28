<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('message_templates', function (Blueprint $table) { 
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('barber_shop_id')->constrained('barber_shops');
            $table->enum('channel', ['sms', 'whatsapp']);
            $table->enum('type', ['reminder', 'confirmation', 'welcome']);
            $table->text('content');
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('message_templates'); } };