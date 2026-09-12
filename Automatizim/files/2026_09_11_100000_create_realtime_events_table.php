<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realtime_events', function (Blueprint $table) {
            $table->id();
            $table->string('event')->unique();   // p.sh. 'job-cards.created'
            $table->string('label');              // p.sh. 'JobCards — Krijuar'
            $table->string('channel')->nullable(); // p.sh. 'mobile.job-cards' (informativ)
            $table->string('description')->nullable();
            $table->boolean('firebase_enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realtime_events');
    }
};
