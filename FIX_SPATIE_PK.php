<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    Schema::disableForeignKeyConstraints();

    echo "Fixing model_has_roles...\n";
    DB::statement("ALTER TABLE model_has_roles DROP PRIMARY KEY");
    DB::statement("ALTER TABLE model_has_roles ADD PRIMARY KEY (barber_shop_id, role_id, model_id, model_type)");

    echo "Fixing model_has_permissions...\n";
    DB::statement("ALTER TABLE model_has_permissions DROP PRIMARY KEY");
    DB::statement("ALTER TABLE model_has_permissions ADD PRIMARY KEY (barber_shop_id, permission_id, model_id, model_type)");

    Schema::enableForeignKeyConstraints();
    echo "✅ SPATIE PRIMARY KEYS FIXED!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
