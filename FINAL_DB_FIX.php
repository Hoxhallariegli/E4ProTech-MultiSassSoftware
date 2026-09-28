<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

try {
    Schema::disableForeignKeyConstraints();

    // 1. Create the fundamental pivot table
    if (!Schema::hasTable('barber_shop_user')) {
        Schema::create('barber_shop_user', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('barber_shop_id');
            $table->timestamps();
            $table->unique(['user_id', 'barber_shop_id']);
        });
        echo "✓ Table barber_shop_user created.\n";
    }

    // 2. Fix Database Collation once and for all
    $dbName = config('database.connections.mysql.database');
    DB::statement("ALTER DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    $tables = DB::select('SHOW TABLES');
    $key = "Tables_in_$dbName";
    foreach ($tables as $table) {
        $t = $table->$key;
        DB::statement("ALTER TABLE `$t` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
    echo "✓ Global Collation fixed (utf8mb4_unicode_ci).\n";

    // 3. Ensure users table has barber_shop_id
    if (!Schema::hasColumn('users', 'barber_shop_id')) {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('barber_shop_id')->nullable()->after('id');
        });
        echo "✓ Added barber_shop_id to users.\n";
    }

    Schema::enableForeignKeyConstraints();
    echo "✅ DATABASE IS NOW STABLE!\n";

} catch (Exception $e) {
    echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
}
