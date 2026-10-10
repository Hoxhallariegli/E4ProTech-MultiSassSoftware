<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('subscription_renewals', function (Blueprint $table) { 
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('plan_id')->constrained('plans');
            $table->enum('payment_method', ['bank_transfer', 'cash', 'card', 'online']);
            $table->string('transfer_document')->nullable();
            $table->decimal('amount', 12, 2);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected']);
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('subscription_renewals'); } };