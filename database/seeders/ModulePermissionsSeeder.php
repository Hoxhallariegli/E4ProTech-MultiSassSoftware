<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ModulePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'BarberShop' => 'barber_shops',
            'Plan' => 'plans',
            'Subscription' => 'subscriptions',
            'Barber' => 'barbers',
            'Service' => 'services',
            'WorkingHour' => 'working_hours',
            'Customer' => 'customers',
            'NotificationChannel' => 'notification_channels',
            'EventSetting' => 'event_settings',
            'MessageTemplate' => 'message_templates',
            'Booking' => 'bookings',
            'Payment' => 'payments',
            'MessageQueue' => 'message_queues',
            'MessageLog' => 'message_logs',
            'DeviceToken' => 'device_tokens',
            'Review' => 'reviews',
            'AiAssistant' => 'ai_assistant',
            'SystemSettings' => 'system_settings',
            'AuditTrail' => 'audit_trails',
            'Report' => 'reports',
            'WebsiteOfferRequest' => 'website_offer_requests',
            'ShopFrontPageSetting' => 'shop_front_page_settings',
        ];

        foreach ($modules as $name => $pluralSnake) {
            foreach (['view', 'add', 'edit', 'delete'] as $act) {
                Permission::firstOrCreate(
                    ['name' => "{$act}_{$pluralSnake}"],
                    [
                        'label' => ucfirst($act) . ' ' . $name,
                        'module' => Str::plural($name)
                    ]
                );
            }
        }

        // Also create standard app/dashboard/role permissions
        Permission::firstOrCreate(['name' => 'view_dashboard', 'label' => 'View Dashboard', 'module' => 'App']);
        Permission::firstOrCreate(['name' => 'view_notifications', 'label' => 'View Notifications', 'module' => 'App']);
        Permission::firstOrCreate(['name' => 'view_roles', 'label' => 'View Roles', 'module' => 'Roles']);
        Permission::firstOrCreate(['name' => 'add_roles', 'label' => 'Add Roles', 'module' => 'Roles']);
        Permission::firstOrCreate(['name' => 'edit_roles', 'label' => 'Edit Roles', 'module' => 'Roles']);
        Permission::firstOrCreate(['name' => 'delete_roles', 'label' => 'Delete Roles', 'module' => 'Roles']);

        // Sync all permissions to roles
        try {
            $allPermissions = Permission::all();
            $roles = \App\Models\Role::all();
            foreach ($roles as $role) {
                setPermissionsTeamId($role->barber_shop_id ?? 0);
                $role->syncPermissions($allPermissions);
            }
            setPermissionsTeamId(0);
        } catch (\Throwable $e) {}

        $this->command->info('✅ Permissions for all modules have been generated.');
    }
}
