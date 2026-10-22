<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;
use App\Models\Role;
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
            'SubscriptionRenewal' => 'subscription_renewals',
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
            'BulkCancellation' => 'bulk_cancellations',
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

        // Additional permissions
        $extraPerms = [
            ['name' => 'view_dashboard', 'label' => 'View Dashboard', 'module' => 'App'],
            ['name' => 'view_notifications', 'label' => 'View Notifications', 'module' => 'App'],
            ['name' => 'view_roles', 'label' => 'View Roles', 'module' => 'Roles'],
            ['name' => 'add_roles', 'label' => 'Add Roles', 'module' => 'Roles'],
            ['name' => 'edit_roles', 'label' => 'Edit Roles', 'module' => 'Roles'],
            ['name' => 'delete_roles', 'label' => 'Delete Roles', 'module' => 'Roles'],
        ];

        foreach ($extraPerms as $p) {
            if (!Permission::where('name', $p['name'])->exists()) {
                Permission::create($p);
                $this->line("  ✓ Created permission: <info>{$p['name']}</info>");
                $newCount++;
            }
        }

        if ($newCount === 0) {
            $this->info('✅ All permissions are already synchronized in the database.');
        } else {
            $this->info("✅ Successfully created {$newCount} new permission(s).");
        }

        return Command::SUCCESS;
    }
}
