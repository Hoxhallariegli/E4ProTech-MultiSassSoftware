<?php

namespace App\Observers;

use App\Events\BarberShopChanged;
use App\Models\BarberShop;
use App\Models\EventSetting;
use App\Models\MessageTemplate;
use App\Models\Plan;
use App\Models\RealtimeEvent;
use App\Models\Subscription;
use App\Services\NotificationRouter;

class BarberShopObserver
{
    public function created(BarberShop $item): void
    {
        try {
            if (!$item->subscriptions()->exists()) {
                $trialPlan = Plan::where('name', 'like', '%Trial%')->first() ?? Plan::first();
                if ($trialPlan) {
                    Subscription::create([
                        'barber_shop_id' => $item->id,
                        'plan_id' => $trialPlan->id,
                        'starts_at' => now(),
                        'ends_at' => $item->trial_ends_at ?? now()->addDays(30),
                        'status' => 'trial',
                        'auto_renew' => false,
                    ]);
                }
            }

            // Create default SMS templates for the new shop (Albanian + English)
            $defaultTemplates = [
                'confirmation' => [
                    'sq' => 'Pershendetje {customer_name}! Rezervimi juaj per {service_name} me {staff_name} ne {shop_name} u konfirmua per ne oren {time} {date}. Faleminderit!',
                    'en' => 'Hello {customer_name}! Your booking for {service_name} at {shop_name} with {staff_name} is confirmed for {time} {date}. Thank you!',
                ],
                'reminder' => [
                    'sq' => 'Rikujtese: Pershendetje {customer_name}, ju kujtojme takimin tuaj per {service_name} ne {shop_name} sot ne oren {time}. Faleminderit!',
                    'en' => 'Reminder: Hello {customer_name}! Your appointment for {service_name} at {shop_name} is today at {time}. Thank you!',
                ],
                'welcome' => [
                    'sq' => 'Mire se erdhe {customer_name}! Faleminderit qe zgjodhe {shop_name}.',
                    'en' => 'Welcome {customer_name}! Thank you for choosing {shop_name}.',
                ],
                'reschedule' => [
                    'sq' => 'Pershendetje {customer_name}! Takimi juaj ne {shop_name} me {staff_name} u ndryshua per ne oren {time} {date}. Faleminderit!',
                    'en' => 'Hello {customer_name}! Your appointment at {shop_name} with {staff_name} is updated to {time} {date}. Thank you!',
                ],
                'cancellation' => [
                    'sq' => 'Pershendetje {customer_name}! Takimi juaj ne {shop_name} per ne oren {time} {date} u anulua. Faleminderit!',
                    'en' => 'Hello {customer_name}! Your appointment at {shop_name} for {time} {date} was cancelled. Thank you!',
                ],
            ];

            foreach ($defaultTemplates as $type => $content) {
                MessageTemplate::firstOrCreate(
                    [
                        'barber_shop_id' => $item->id,
                        'channel' => 'sms',
                        'type' => $type,
                    ],
                    [
                        'content' => $content,
                    ]
                );
            }

            // Automatically create default EventSetting rows for all RealtimeEvents for the new shop
            $realtimeEvents = RealtimeEvent::all();
            if ($realtimeEvents->isEmpty() && class_exists(\Database\Seeders\RealtimeEventsSeeder::class)) {
                app(\Database\Seeders\RealtimeEventsSeeder::class)->run();
                $realtimeEvents = RealtimeEvent::all();
            }

            foreach ($realtimeEvents as $event) {
                EventSetting::firstOrCreate(
                    [
                        'barber_shop_id' => $item->id,
                        'realtime_event_id' => $event->id,
                    ],
                    [
                        'reverb_enabled' => false,
                        'firebase_enabled' => (bool) $event->firebase_enabled,
                    ]
                );
            }

            event(new BarberShopChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('barber-shops.created', $item, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for BarberShop: " . $e->getMessage());
        }
    }

    public function updated(BarberShop $item): void
    {
        try {
            if ($item->isDirty('expires_at') || $item->isDirty('trial_ends_at')) {
                $newEnd = $item->expires_at ?? $item->trial_ends_at;
                if ($newEnd) {
                    $item->subscriptions()->update([
                        'ends_at' => $newEnd
                    ]);
                }
            }
            event(new BarberShopChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('barber-shops.updated', $item, 'updated');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for BarberShop: " . $e->getMessage());
        }
    }

    public function deleted(BarberShop $item): void
    {
        try {
            event(new BarberShopChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('barber-shops.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for BarberShop: " . $e->getMessage());
        }
    }
}
