<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subscription_renewals', function (Blueprint $table) {
            $table->foreignId('barber_shop_id')->after('id')->nullable()->constrained('barber_shops')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('subscription_renewals', function (Blueprint $table) {
            $table->dropForeign(['barber_shop_id']);
            $table->dropColumn('barber_shop_id');
        });
    }
};
