<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

$userId = '04732a98-8b73-4005-9c1f-55db6d3ab283';
$user = User::find($userId);

if (!$user) {
    echo "User not found\n";
    exit;
}

echo "User: " . $user->name . " (" . $user->email . ")\n";
echo "Active Shop ID (barber_shop_id in users table): " . $user->barber_shop_id . "\n";

echo "\nRoles in model_has_roles:\n";
$roles = DB::table('model_has_roles')->where('model_id', $userId)->get();
foreach ($roles as $role) {
    $roleName = DB::table('roles')->where('id', $role->role_id)->value('name');
    echo "- Role: $roleName (ID: {$role->role_id}) | Team ID (barber_shop_id): {$role->barber_shop_id}\n";
}

echo "\nPermissions Flattened (via getPermissionsFlattened()):\n";
// Set team ID to simulate request context
if ($user->barber_shop_id) {
    setPermissionsTeamId($user->barber_shop_id);
}
print_r($user->getPermissionsFlattened()->toArray());
