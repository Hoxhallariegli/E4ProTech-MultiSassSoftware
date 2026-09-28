<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

try {
    $user = User::where('email', 'demo@e4protech.com')->first();
    $adminRole = Role::where('name', 'admin')->first();

    if ($user && $adminRole) {
        echo "Lidhja e Adminit me pushtetin global (Team 0)...\n";

        // Sigurohemi qe rreshti ne model_has_roles per Team 0 ekziston
        DB::table('model_has_roles')->updateOrInsert(
            [
                'role_id' => $adminRole->id,
                'model_type' => User::class,
                'model_id' => $user->id,
                'barber_shop_id' => 0
            ],
            ['barber_shop_id' => 0]
        );

        // Pastrim keshi
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        \Illuminate\Support\Facades\Cache::forget('is_global_admin_' . $user->id);

        echo "✅ Admin access restored for Team 0!\n";
    } else {
        echo "❌ Error: User or Role not found.\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
