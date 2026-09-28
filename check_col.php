<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
try {
    $tables = ['users', 'barber_shops', 'barber_shop_user'];
    foreach($tables as $table) {
        $res = DB::select("SHOW TABLE STATUS LIKE '$table'");
        if (!empty($res)) {
            echo "$table: " . $res[0]->Collation . "\n";
        } else {
            echo "$table: NOT FOUND\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
