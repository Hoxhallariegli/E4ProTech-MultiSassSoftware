<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

try {
    $user = User::where('email', 'demo@e4protech.com')->first();
    if (!$user) {
        echo "ERROR: User demo@e4protech.com not found!\n";
        exit;
    }

    echo "User ID: " . $user->id . "\n";
    echo "User Active Shop ID: " . ($user->barber_shop_id ?? 'NULL') . "\n";

    // Clear cache first to be sure
    Cache::forget('is_global_admin_' . $user->id);
    Cache::forget('global_admin_' . $user->id);

    $roles = DB::table('model_has_roles')
        ->where('model_id', $user->id)
        ->get();

    echo "Roles found in DB:\n";
    foreach ($roles as $role) {
        $roleName = DB::table('roles')->where('id', $role->role_id)->value('name');
        echo "- Role: $roleName | Team ID: {$role->barber_shop_id}\n";
    }

    echo "Is Global Admin (Attribute): " . ($user->is_global_admin ? 'YES' : 'NO') . "\n";

    echo "✅ DIAGNOSIS COMPLETE!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
