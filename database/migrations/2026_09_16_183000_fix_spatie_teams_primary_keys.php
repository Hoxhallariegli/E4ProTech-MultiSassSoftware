<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Spatie with Teams requires the team_id to be part of the primary key in pivot tables

        // 1. model_has_roles
        if (Schema::hasTable('model_has_roles')) {
            try {
                DB::statement("ALTER TABLE model_has_roles DROP PRIMARY KEY");
                DB::statement("ALTER TABLE model_has_roles ADD PRIMARY KEY (barber_shop_id, role_id, model_id, model_type)");
            } catch(\Exception $e) {
                // Already fixed or other issue
            }
        }

        // 2. model_has_permissions
        if (Schema::hasTable('model_has_permissions')) {
            try {
                DB::statement("ALTER TABLE model_has_permissions DROP PRIMARY KEY");
                DB::statement("ALTER TABLE model_has_permissions ADD PRIMARY KEY (barber_shop_id, permission_id, model_id, model_type)");
            } catch(\Exception $e) {
                // Already fixed or other issue
            }
        }
    }

    public function down(): void {}
};
