<?php

namespace App\Observers;

use App\Events\TestModuleChanged;
use App\Models\TestModule;
use App\Services\NotificationRouter;

class TestModuleObserver
{
    public function created(TestModule $item): void
    {
        try {
            event(new TestModuleChanged($item, 'created'));
            app(NotificationRouter::class)->maybeNotify('test-modules.created', $item, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (created) failure for TestModule: " . $e->getMessage());
        }
    }

    public function updated(TestModule $item): void
    {
        try {
            event(new TestModuleChanged($item, 'updated'));
            app(NotificationRouter::class)->maybeNotify('test-modules.updated', $item, 'updated');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (updated) failure for TestModule: " . $e->getMessage());
        }
    }

    public function deleted(TestModule $item): void
    {
        try {
            event(new TestModuleChanged($item, 'deleted'));
            app(NotificationRouter::class)->maybeNotify('test-modules.deleted', $item, 'deleted');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Observer (deleted) failure for TestModule: " . $e->getMessage());
        }
    }
}
