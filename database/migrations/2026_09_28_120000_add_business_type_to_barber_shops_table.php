<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barber_shops', function (Blueprint $table) {
            $table->string('business_type')->default('barbershop')->after('name');
            $table->string('staff_label')->nullable()->after('business_type');
            $table->string('staff_label_plural')->nullable()->after('staff_label');
            $table->string('shop_label')->nullable()->after('staff_label_plural');
            $table->string('service_label')->nullable()->after('shop_label');
        });
    }

    public function down(): void
    {
        Schema::table('barber_shops', function (Blueprint $table) {
            $table->dropColumn([
                'business_type',
                'staff_label',
                'staff_label_plural',
                'shop_label',
                'service_label',
            ]);
        });
    }
};
