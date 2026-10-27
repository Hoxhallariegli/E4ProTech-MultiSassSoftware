<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('barber_shops')) {
            Schema::table('barber_shops', function (Blueprint $table) {
                if (!Schema::hasColumn('barber_shops', 'whatsapp_enabled')) {
                    $table->boolean('whatsapp_enabled')->default(true)->after('sms_enabled');
                }
            });
        }

        if (Schema::hasTable('barbers')) {
            Schema::table('barbers', function (Blueprint $table) {
                if (!Schema::hasColumn('barbers', 'whatsapp_enabled')) {
                    $table->boolean('whatsapp_enabled')->default(true);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('barber_shops')) {
            Schema::table('barber_shops', function (Blueprint $table) {
                if (Schema::hasColumn('barber_shops', 'whatsapp_enabled')) {
                    $table->dropColumn('whatsapp_enabled');
                }
            });
        }

        if (Schema::hasTable('barbers')) {
            Schema::table('barbers', function (Blueprint $table) {
                if (Schema::hasColumn('barbers', 'whatsapp_enabled')) {
                    $table->dropColumn('whatsapp_enabled');
                }
            });
        }
    }
};
