<?php

namespace Database\Seeders;

use App\Models\RealtimeEvent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RealtimeEventsSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'BarberShop' => 'barber-shops',
            'Plan' => 'plans',
            'Subscription' => 'subscriptions',
            'Barber' => 'barbers',
            'Service' => 'services',
            'WorkingHour' => 'working-hours',
            'Customer' => 'customers',
            'NotificationChannel' => 'notification-channels',
            'EventSetting' => 'event-settings',
            'MessageTemplate' => 'message-templates',
            'Booking' => 'bookings',
            'Payment' => 'payments',
            'MessageQueue' => 'message-queues',
            'MessageLog' => 'message-logs',
            'DeviceToken' => 'device-tokens',
            'Review' => 'reviews',
        ];

        foreach ($modules as $name => $pluralKebab) {
            RealtimeEvent::updateOrCreate(
                ['event' => "{$pluralKebab}.created"],
                [
                    'label' => "New " . Str::title(str_replace('-', ' ', $pluralKebab)),
                    'description' => "Push notification when a new record is created in " . Str::plural($name) . ".",
                    'firebase_enabled' => true,
                ]
            );

            // Adding updated/deleted as well if needed for the UI management
            RealtimeEvent::updateOrCreate(
                ['event' => "{$pluralKebab}.updated"],
                [
                    'label' => Str::title(str_replace('-', ' ', $pluralKebab)) . " Updated",
                    'description' => "Push notification when a record is updated in " . Str::plural($name) . ".",
                    'firebase_enabled' => false,
                ]
            );

            RealtimeEvent::updateOrCreate(
                ['event' => "{$pluralKebab}.deleted"],
                [
                    'label' => Str::title(str_replace('-', ' ', $pluralKebab)) . " Deleted",
                    'description' => "Push notification when a record is deleted from " . Str::plural($name) . ".",
                    'firebase_enabled' => false,
                ]
            );
        }

        $this->command->info('✅ Realtime events for all modules have been generated.');
    }
}
