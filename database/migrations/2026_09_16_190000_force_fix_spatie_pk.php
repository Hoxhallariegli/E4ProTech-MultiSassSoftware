<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. model_has_roles
        try {
            DB::statement("ALTER TABLE model_has_roles DROP FOREIGN KEY model_has_roles_role_id_foreign");
        } catch(\Exception $e) {}

        try {
            DB::statement("ALTER TABLE model_has_roles DROP PRIMARY KEY");
        } catch(\Exception $e) {}

        DB::statement("ALTER TABLE model_has_roles ADD PRIMARY KEY (barber_shop_id, role_id, model_id, model_type)");

        DB::statement("ALTER TABLE model_has_roles ADD CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE");

        // 2. model_has_permissions
        try {
            DB::statement("ALTER TABLE model_has_permissions DROP FOREIGN KEY model_has_permissions_permission_id_foreign");
        } catch(\Exception $e) {}

        try {
            DB::statement("ALTER TABLE model_has_permissions DROP PRIMARY KEY");
        } catch(\Exception $e) {}

        DB::statement("ALTER TABLE model_has_permissions ADD PRIMARY KEY (barber_shop_id, permission_id, model_id, model_type)");

        DB::statement("ALTER TABLE model_has_permissions ADD CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE");

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void {}
};
