<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Spatie Roles table
        if (Schema::hasTable('roles')) {
            if (!Schema::hasColumn('roles', 'barber_shop_id')) {
                Schema::table('roles', function (Blueprint $table) {
                    $table->unsignedBigInteger('barber_shop_id')->nullable()->after('id');
                    $table->index('barber_shop_id');
                });
            }
            try {
                DB::statement("ALTER TABLE roles DROP INDEX roles_name_guard_name_unique");
            } catch(\Exception $e) {}
            try {
                Schema::table('roles', function (Blueprint $table) {
                    $table->unique(['barber_shop_id', 'name', 'guard_name']);
                });
            } catch(\Exception $e) {}
        }

        // 2. Spatie model_has_roles
        if (Schema::hasTable('model_has_roles') && !Schema::hasColumn('model_has_roles', 'barber_shop_id')) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->unsignedBigInteger('barber_shop_id')->nullable()->after('model_id');
            });
        }

        // 3. Spatie model_has_permissions
        if (Schema::hasTable('model_has_permissions') && !Schema::hasColumn('model_has_permissions', 'barber_shop_id')) {
            Schema::table('model_has_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('barber_shop_id')->nullable()->after('model_id');
            });
        }

        // 4. Users table foundation
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'barber_shop_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('barber_shop_id')->nullable()->after('id');
            });
        }

        // 5. Pivot Table
        if (!Schema::hasTable('barber_shop_user')) {
            Schema::create('barber_shop_user', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('barber_shop_id');
                $table->timestamps();
                $table->unique(['user_id', 'barber_shop_id']);
            });
        }
    }

    public function down(): void {}
};
