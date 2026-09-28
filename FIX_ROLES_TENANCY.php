<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "Converting base roles to GLOBAL (Team 0)...\n";

    // Bejme rolet kryesore globale (ID 0) qe t'i shohin te gjithe dyqanet
    DB::table('roles')
        ->whereIn('name', ['admin', 'owner', 'berber', 'menaxher', 'qqq'])
        ->update(['barber_shop_id' => 0]);

    // Pastrojme keshin e Spatie
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    echo "✅ ROLES ARE NOW GLOBAL TEMPLATES!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
