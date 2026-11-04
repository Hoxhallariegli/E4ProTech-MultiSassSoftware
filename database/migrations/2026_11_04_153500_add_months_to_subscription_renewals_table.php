<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('subscription_renewals') && !Schema::hasColumn('subscription_renewals', 'months')) {
            Schema::table('subscription_renewals', function (Blueprint $table) {
                $table->integer('months')->default(1)->after('amount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('subscription_renewals') && Schema::hasColumn('subscription_renewals', 'months')) {
            Schema::table('subscription_renewals', function (Blueprint $table) {
                $table->dropColumn('months');
            });
        }
    }
};
