<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

try {
    $elton = User::where('email', 'barber2@test.com')->first();
    if ($elton) {
        echo "Cleaning Elton's roles...\n";
        // Fshijme cdo rol te Eltonit qe eshte "Global" (ID 0) qe te mos kete bypass
        DB::table('model_has_roles')
            ->where('model_id', $elton->id)
            ->where('barber_shop_id', 0)
            ->delete();

        echo "✓ Elton is no longer a Global Admin.\n";
    }

    echo "✅ CLEANUP COMPLETE!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
