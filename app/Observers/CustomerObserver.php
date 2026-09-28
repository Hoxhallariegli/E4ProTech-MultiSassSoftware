<?php

namespace App\Observers;

use App\Events\CustomerChanged;
use App\Models\Customer;
use App\Services\NotificationRouter;

class CustomerObserver
{
    public function created(Customer $item): void
    {
        try {
            event(new CustomerChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('customers.created', $item, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for Customer: " . $e->getMessage());
        }
    }

    public function updated(Customer $item): void
    {
        try {
            event(new CustomerChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('customers.updated', $item, 'updated');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for Customer: " . $e->getMessage());
        }
    }

    public function deleted(Customer $item): void
    {
        try {
            event(new CustomerChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('customers.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for Customer: " . $e->getMessage());
        }
    }
}
