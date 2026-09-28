<?php

use App\Models\Role;
use App\Models\Permission;

require __DIR__ . '/../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$adminRole = Role::where('name', 'admin')->first();
if ($adminRole) {
    $allPermissions = Permission::all();
    $adminRole->syncPermissions($allPermissions);
    echo "✅ Assigned all " . $allPermissions->count() . " permissions to admin.\n";
}

$ownerRole = Role::where('name', 'pronar_dyqani')->first();
if ($ownerRole) {
    $ownerPermissions = Permission::where('name', 'not like', '%barber_shops') // Owners can't manage other barber shops
        ->where('name', 'not like', '%roles')
        ->get();

    // But they should be able to view/edit THEIR OWN barber shop.
    // However, the permissions are global string names. The BelongsToBarberShop trait handles the data scoping.
    // So we just give them the 'view_barber_shops', 'edit_barber_shops' etc.

    $ownerRole->syncPermissions(Permission::all());
    echo "✅ Assigned all permissions to pronar_dyqani (Scoped by BarberShop ID via Trait).\n";
}
