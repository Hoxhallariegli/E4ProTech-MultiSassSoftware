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

        $this->command->info('✅ Permissions for all modules have been generated.');
    }
}
