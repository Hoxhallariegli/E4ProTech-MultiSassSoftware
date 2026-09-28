<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "Converting barber_shop_id 0 to NULL in roles table...\n";

    DB::table('roles')
        ->where('barber_shop_id', 0)
        ->update(['barber_shop_id' => null]);

    // Also check model_has_roles
    // Actually, assignments to Team 0 should probably stay 0 if we use 0 as the 'Super Admin Team'.
    // But for the ROLES table, NULL means global template.

    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    echo "✅ CONVERSION COMPLETE!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
