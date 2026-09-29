<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customers')) {
            DB::statement('ALTER TABLE customers MODIFY total_bookings INT NOT NULL DEFAULT 0, MODIFY no_show_count INT NOT NULL DEFAULT 0');
        }
    }

    public function down(): void
    {
    }
};
