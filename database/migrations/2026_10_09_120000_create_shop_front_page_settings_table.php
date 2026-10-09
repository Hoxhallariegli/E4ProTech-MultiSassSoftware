<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shop_front_page_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barber_shop_id')->constrained('barber_shops')->cascadeOnDelete();
            $table->string('hero_badge_text')->nullable();
            $table->string('hero_title')->nullable();
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
            $table->json('translations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_front_page_settings');
    }
};
