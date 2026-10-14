<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('barber_shops') && !Schema::hasColumn('barber_shops', 'currency')) {
            Schema::table('barber_shops', function (Blueprint $table) {
                $table->string('currency')->default('Lekë')->after('timezone');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('barber_shops') && Schema::hasColumn('barber_shops', 'currency')) {
            Schema::table('barber_shops', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }
    }
};
