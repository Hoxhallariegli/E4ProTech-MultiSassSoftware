<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use Livewire\Component;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\Title;

#[Title('Notification Settings')]
class NotificationSettings extends Component
{
    public array $modules = [];
    public array $activeNotifications = [];

    public function mount()
    {
        // Gjejmë të gjithë modulet e krijuara te app/Domain
        $domainPath = app_path('Domain');
        if (File::exists($domainPath)) {
            foreach (File::directories($domainPath) as $dir) {
                $name = basename($dir);
                if ($name !== 'Shared') {
                    $this->modules[] = $name;
                    $this->activeNotifications[$name] = (bool) Setting::where('key', "notify_firebase_$name")->value('value');
                }
            }
        }
    }

    public function toggleNotification($module)
    {
        $newValue = !($this->activeNotifications[$module] ?? false);
        $this->activeNotifications[$module] = $newValue;

        // 1. Update Global Setting
        \App\Models\Setting::updateOrCreate(
            ['key' => "notify_firebase_$module"],
            ['value' => $newValue]
        );

        // 2. Update RealtimeEvents for this module (Surgical Sync)
        // If module is "TestModule", events are "test-modules.*"
        $prefix = \Illuminate\Support\Str::kebab(\Illuminate\Support\Str::plural($module));
        \App\Models\RealtimeEvent::where('event', 'like', "$prefix.%")
            ->update(['firebase_enabled' => $newValue]);

        // Clear cache if needed (RealtimeEvent has booted to handle this usually)
        if (class_exists(\Illuminate\Support\Facades\Cache::class)) {
            // RealtimeEvent handles cache clearing in booted() for individual rows,
            // but mass update might need manual clearing for all affected.
            $events = \App\Models\RealtimeEvent::where('event', 'like', "$prefix.%")->get();
            foreach($events as $e) {
                \Illuminate\Support\Facades\Cache::forget("realtime_event:firebase:{$e->event}");
            }
        }

        $this->dispatch('toast', ['message' => "Notifications for $module updated!", 'type' => 'success']);
    }

    public function render()
    {
        return view('livewire.admin.settings.notification-settings')->layout('components.layouts.app');
    }
}
