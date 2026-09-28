<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$roleId = '98c2b068-a59c-49ec-8d8e-fdf87e816ede';
$role = DB::table('roles')->where('id', $roleId)->first();

echo "Role Info:\n";
print_r($role);

$count = DB::table('role_has_permissions')->where('role_id', $roleId)->count();
echo "\nPermissions Count for this role: " . $count . "\n";

// Check if teams is enabled in config
echo "Spatie Teams Enabled: " . (config('permission.teams') ? 'YES' : 'NO') . "\n";
echo "Spatie Team Foreign Key: " . config('permission.column_names.team_foreign_key') . "\n";
