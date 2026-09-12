<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        Permission::firstOrCreate(['name' => 'view_users', 'label' => 'View Users', 'module' => 'Users']);
        Permission::firstOrCreate(['name' => 'view_users_profiles', 'label' => 'View Users Profiles', 'module' => 'Users']);
        Permission::firstOrCreate(['name' => 'view_users_activity', 'label' => 'View Users Activity', 'module' => 'Users']);
        Permission::firstOrCreate(['name' => 'add_users', 'label' => 'Add Users', 'module' => 'Users']);
        Permission::firstOrCreate(['name' => 'edit_users', 'label' => 'Edit Users', 'module' => 'Users']);
        Permission::firstOrCreate(['name' => 'edit_own_account', 'label' => 'Edit Own Account', 'module' => 'Users']);
        Permission::firstOrCreate(['name' => 'delete_users', 'label' => 'Delete Users', 'module' => 'Users']);

        // Create dynamic demo user account
        $user = User::firstOrCreate(
            ['email' => 'demo@e4protech.com'],
            [
                'name' => 'Demo Admin E4ProTech',
                'slug' => 'demo-admin-e4protech',
                'password' => Hash::make('E4ProTech'),
                'is_active' => true,
                'email_verified_at' => now(),
                'joined_at' => now(),
            ]
        );

        // Assign admin role to bypass all gates
        $user->assignRole('admin');
    }
}
