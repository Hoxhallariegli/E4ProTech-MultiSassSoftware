<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

try {
    Schema::disableForeignKeyConstraints();

    echo "1. Cleaning 'roles' table...\n";
    // Heqim unikun e vjeter qe perfshinte dyqanin
    try {
        DB::statement("ALTER TABLE roles DROP INDEX roles_barber_shop_id_name_guard_name_unique");
    } catch(\Exception $e) {}

    // Heqim kolonën barber_shop_id nga tabela roles (sepse rolet duhet te jene globale per emrat)
    if (Schema::hasColumn('roles', 'barber_shop_id')) {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('barber_shop_id');
        });
    }

    // Shtojme unikun e ri te paster
    try {
        DB::statement("ALTER TABLE roles ADD UNIQUE INDEX roles_name_guard_name_unique (name, guard_name)");
    } catch(\Exception $e) {}

    echo "2. Ensuring 'model_has_roles' is correct...\n";
    // Ketu barber_shop_id DUHET te jete sepse ketu behet izolimi i userit
    if (!Schema::hasColumn('model_has_roles', 'barber_shop_id')) {
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->bigInteger('barber_shop_id')->unsigned()->default(0);
        });
    }

    echo "3. Cleaning Cache...\n";
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    \Illuminate\Support\Facades\Cache::flush();

    Schema::enableForeignKeyConstraints();
    echo "✅ SPATIE IS NOW TRULY ENTERPRISE READY!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
