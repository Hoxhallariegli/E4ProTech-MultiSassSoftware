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
            try {
                DB::statement("ALTER TABLE device_tokens MODIFY barber_shop_id BIGINT(20) UNSIGNED NULL");
            } catch (\Throwable $e) {}

            try {
                DB::statement("ALTER TABLE device_tokens MODIFY user_id CHAR(36) NULL");
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('device_tokens')) {
            try {
                DB::statement("ALTER TABLE device_tokens MODIFY barber_shop_id BIGINT(20) UNSIGNED NOT NULL");
            } catch (\Throwable $e) {}

            try {
                DB::statement("ALTER TABLE device_tokens MODIFY user_id CHAR(36) NOT NULL");
            } catch (\Throwable $e) {}
        }
    }
};
