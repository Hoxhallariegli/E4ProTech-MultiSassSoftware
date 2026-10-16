<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;
use Illuminate\Support\Str;

class SyncPermissionsCommand extends Command
{
    protected $signature = 'permissions:sync';
    protected $description = 'Sync and create missing module permissions without altering existing database data.';

    public function handle(): int
    {
        $this->info('🚀 Starting Permission Sync...');

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
            'WebsiteOfferRequest' => 'website_offer_requests',
        ];

        $newCount = 0;

        foreach ($modules as $name => $pluralSnake) {
            foreach (['view', 'add', 'edit', 'delete'] as $act) {
                $permName = "{$act}_{$pluralSnake}";
                $exists = Permission::where('name', $permName)->exists();

                if (!$exists) {
                    Permission::create([
                        'name' => $permName,
                        'label' => ucfirst($act) . ' ' . $name,
                        'module' => Str::plural($name)
                    ]);
                    $this->line("  ✓ Created permission: <info>{$permName}</info>");
                    $newCount++;
                }
            }
        }

        if ($newCount === 0) {
            $this->info('✅ All permissions are already synchronized and up to date.');
        } else {
            $this->info("✅ Successfully created {$newCount} new permission(s).");
        }

        return Command::SUCCESS;
    }
}
