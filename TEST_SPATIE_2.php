<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

$userId = '04732a98-8b73-4005-9c1f-55db6d3ab283';
$user = User::find($userId);
$shopId = 8;

echo "Testing for Shop ID: $shopId\n";
setPermissionsTeamId($shopId);

echo "User Roles for this team: " . $user->getRoleNames()->implode(', ') . "\n";

$allPerms = $user->getAllPermissions();
echo "Total Permissions for this team: " . $allPerms->count() . "\n";

echo "\nChecking a specific permission (e.g., 'view_barbers'):\n";
echo "Has 'view_barbers': " . ($user->hasPermissionTo('view_barbers') ? 'YES' : 'NO') . "\n";
