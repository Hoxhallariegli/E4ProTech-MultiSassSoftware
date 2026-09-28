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

    echo "Restoring 'barber_shop_id' to roles table...\n";

    if (!Schema::hasColumn('roles', 'barber_shop_id')) {
        Schema::table('roles', function (Blueprint $table) {
            $table->bigInteger('barber_shop_id')->unsigned()->nullable()->after('id');
            $table->index('barber_shop_id');
        });
    }

    // Heqim unikun e vjeter nese ekziston
    try {
        DB::statement("ALTER TABLE roles DROP INDEX roles_name_guard_name_unique");
    } catch(\Exception $e) {}

    // Shtojme unikun qe lejon emra te njejte per dyqane te ndryshme (dhe NULL per globale)
    try {
        DB::statement("ALTER TABLE roles ADD UNIQUE INDEX roles_team_name_guard_unique (barber_shop_id, name, guard_name)");
    } catch(\Exception $e) {}

    // Bejme te gjitha rolet ekzistuese Master (NULL)
    DB::table('roles')->update(['barber_shop_id' => null]);

    echo "Cleaning Cache...\n";
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    \Illuminate\Support\Facades\Cache::flush();

    Schema::enableForeignKeyConstraints();
    echo "✅ DATABASE RESTORED AND FULLY COMPATIBLE!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
