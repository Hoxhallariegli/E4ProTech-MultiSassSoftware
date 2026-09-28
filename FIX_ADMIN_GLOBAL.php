<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

try {
    $user = User::where('email', 'demo@e4protech.com')->first();
    if (!$user) {
        die("User not found");
    }

    $adminRole = Role::where('name', 'admin')->first();
    if (!$adminRole) {
        die("Admin role not found");
    }

    echo "Fixing global access for admin...\n";

    // 1. Ensure the user has the admin role for Team 0 (GLOBAL)
    DB::table('model_has_roles')->updateOrInsert(
        [
            'role_id' => $adminRole->id,
            'model_type' => User::class,
            'model_id' => $user->id,
            'barber_shop_id' => 0
        ],
        ['barber_shop_id' => 0]
    );

    // 2. Clear cache
    Cache::forget('is_global_admin_' . $user->id);
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    echo "✅ Global Admin access restored for demo@e4protech.com\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
