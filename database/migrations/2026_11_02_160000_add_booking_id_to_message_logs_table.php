<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('message_logs') && !Schema::hasColumn('message_logs', 'booking_id')) {
            Schema::table('message_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('booking_id')->nullable()->after('customer_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('message_logs') && Schema::hasColumn('message_logs', 'booking_id')) {
            Schema::table('message_logs', function (Blueprint $table) {
                $table->dropColumn('booking_id');
            });
        }
    }
};
