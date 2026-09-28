<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach(['roles', 'model_has_roles'] as $table) {
    $res = DB::select("SHOW CREATE TABLE $table");
    echo "--- $table ---\n";
    echo $res[0]->{'Create Table'} . "\n\n";
}
