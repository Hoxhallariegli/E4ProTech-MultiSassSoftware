<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('barber_shops') && Schema::hasColumn('barber_shops', 'reminder_hours_before')) {
            DB::statement("ALTER TABLE `barber_shops` MODIFY COLUMN `reminder_hours_before` DECIMAL(8,2) NOT NULL DEFAULT 2.00");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('barber_shops') && Schema::hasColumn('barber_shops', 'reminder_hours_before')) {
            DB::statement("ALTER TABLE `barber_shops` MODIFY COLUMN `reminder_hours_before` INT NOT NULL DEFAULT 2");
        }
    }
};
