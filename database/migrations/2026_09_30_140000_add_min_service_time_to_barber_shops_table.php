<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barber_shops', function (Blueprint $table) {
            $table->integer('min_service_time')->nullable()->after('max_no_show_before_block');
        });
    }

    public function down(): void
    {
        Schema::table('barber_shops', function (Blueprint $table) {
            $table->dropColumn('min_service_time');
        });
    }
};
