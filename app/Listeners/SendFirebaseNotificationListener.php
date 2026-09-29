<?php

namespace App\Listeners;

use App\Events\FirebaseNotificationRequested;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Log;

class SendFirebaseNotificationListener
{
    public function __construct(protected FirebaseService $firebaseService)
    {
    }

    public function handle(FirebaseNotificationRequested $event): void
    {
        try {
            $actionLabel = match ($event->action) {
                'created' => 'krijua',
                'updated' => 'përditësua',
                'deleted' => 'fshi',
                default => 'ndryshua',
            };

            $moduleName = match (class_basename($event->modelClass)) {
                'Booking' => 'Rezervim',
                'Customer' => 'Klient',
                'Payment' => 'Pagesë',
                'Barber' => 'Punonjës',
                'Service' => 'Shërbim',
                'BarberShop' => 'Sallon',
                default => class_basename($event->modelClass),
            };

            $title = "Njoftim: {$moduleName} u {$actionLabel}! 🔔";
            $body = "Regjistrimi #{$event->modelId} te moduli {$moduleName} u {$actionLabel} me sukses.";

            $sentCount = $this->firebaseService->sendToAllDevices($title, $body);

            Log::info("FCM Push Notification sent for event [{$event->event}] to {$sentCount} devices.");
        } catch (\Throwable $e) {
            Log::error("FCM Push Notification Listener Error for event [{$event->event}]: " . $e->getMessage());
        }
    }
}
