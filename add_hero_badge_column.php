<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasColumn('shop_front_page_settings', 'hero_badge_text')) {
    Schema::table('shop_front_page_settings', function (Blueprint $table) {
        $table->string('hero_badge_text')->nullable()->after('barber_shop_id');
    });
    echo "Added hero_badge_text column to shop_front_page_settings.\n";
}

if (!Schema::hasColumn('shop_front_page_settings', 'translations')) {
    Schema::table('shop_front_page_settings', function (Blueprint $table) {
        $table->json('translations')->nullable()->after('footer_text');
    });
    echo "Added translations column to shop_front_page_settings.\n";
}

echo "Columns checked successfully.\n";
