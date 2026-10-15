<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barber_shop_id')->constrained('barber_shops')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('user_prompt');
            $table->string('status')->default('success'); // success, error, info
            $table->text('error_message')->nullable();
            $table->json('results')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('ai_conversations');
    }
};
