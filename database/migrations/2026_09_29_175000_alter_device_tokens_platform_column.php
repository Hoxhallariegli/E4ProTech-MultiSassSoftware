<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('device_tokens')) {
            DB::statement("ALTER TABLE device_tokens MODIFY platform VARCHAR(50) NOT NULL DEFAULT 'android'");
        }
    }

    public function down(): void
    {
    }
};
