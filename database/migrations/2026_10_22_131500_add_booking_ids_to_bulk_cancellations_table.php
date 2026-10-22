<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bulk_cancellations') && !Schema::hasColumn('bulk_cancellations', 'booking_ids')) {
            Schema::table('bulk_cancellations', function (Blueprint $table) {
                $table->json('booking_ids')->nullable()->after('affected_count');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bulk_cancellations') && Schema::hasColumn('bulk_cancellations', 'booking_ids')) {
            Schema::table('bulk_cancellations', function (Blueprint $table) {
                $table->dropColumn('booking_ids');
            });
        }
    }
};
