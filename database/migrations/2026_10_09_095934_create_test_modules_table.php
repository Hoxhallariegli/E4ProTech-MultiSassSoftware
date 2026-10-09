<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('test_modules', function (Blueprint $table) { 
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('qty', 12, 2);
            $table->decimal('price', 12, 2);
            $table->boolean('is_active');
            $table->date('due_date');
            $table->datetime('event_at');
            $table->foreignUuid('user_id')->constrained('users');
            $table->enum('priority', ['Low', 'Medium', 'High']);
            $table->string('image')->nullable();
            $table->string('cover_photo')->nullable();
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('test_modules'); } };