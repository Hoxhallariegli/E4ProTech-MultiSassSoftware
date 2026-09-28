<?php

use App\Models\Role;
use App\Models\Permission;

$ownerRole = Role::where('name', 'owner')->first();

if (!$ownerRole) {
    echo "Role 'owner' not found.\n";
    exit(1);
}

$modules = [
    'barbers',
    'services',
    'customers',
    'bookings',
    'payments',
];

$permissionsToAssign = [];

foreach ($modules as $module) {
    $permissionsToAssign[] = "view_{$module}";
    $permissionsToAssign[] = "add_{$module}";
    $permissionsToAssign[] = "edit_{$module}";
    $permissionsToAssign[] = "delete_{$module}";
}

// Shto nja dy specifikë për dyqanin dhe abonimin
$permissionsToAssign[] = "view_barber_shops";
$permissionsToAssign[] = "edit_barber_shops";
$permissionsToAssign[] = "view_subscriptions";

// Gjejmë të gjitha këto leje në bazën e të dhënave
$permissions = Permission::whereIn('name', $permissionsToAssign)->get();

// I lidhim me rolin owner
$ownerRole->syncPermissions($permissions);

echo "Permissions synced for 'owner' role!\n";
echo "Total permissions assigned: " . $permissions->count() . "\n";
foreach ($permissions->pluck('name') as $pName) {
    echo "- $pName\n";
}
