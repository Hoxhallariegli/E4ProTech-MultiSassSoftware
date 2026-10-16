<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('shop_front_page_settings')) {
            Schema::table('shop_front_page_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('shop_front_page_settings', 'hero_badge_text')) {
                    $table->string('hero_badge_text')->nullable()->after('barber_shop_id');
                }
                if (!Schema::hasColumn('shop_front_page_settings', 'translations')) {
                    $table->json('translations')->nullable()->after('footer_text');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('shop_front_page_settings')) {
            Schema::table('shop_front_page_settings', function (Blueprint $table) {
                if (Schema::hasColumn('shop_front_page_settings', 'hero_badge_text')) {
                    $table->dropColumn('hero_badge_text');
                }
                if (Schema::hasColumn('shop_front_page_settings', 'translations')) {
                    $table->dropColumn('translations');
                }
            });
        }
    }
};
