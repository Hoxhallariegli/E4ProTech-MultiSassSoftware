<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('shop_front_page_settings', function (Blueprint $table) { 
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->text('hero_title')->nullable();
            $table->text('hero_subtitle')->nullable();
            $table->string('hero_button_text')->nullable();
            $table->string('services_badge_text')->nullable();
            $table->string('services_title')->nullable();
            $table->string('staff_badge_text')->nullable();
            $table->string('staff_title')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_address')->nullable();
            $table->text('google_maps_url')->nullable();
            $table->string('footer_text')->nullable();
            $table->foreignId('barber_shop_id')->constrained('barber_shops');
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('shop_front_page_settings'); } };