<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $dbName = config('database.connections.mysql.database');

        // 1. Change database default
        DB::statement("ALTER DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // 2. Change existing tables
        $tables = DB::select('SHOW TABLES');
        $key = "Tables_in_$dbName";

        foreach ($tables as $table) {
            $tableName = $table->$key;
            DB::statement("ALTER TABLE `$tableName` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
    }

    public function down(): void {}
};
