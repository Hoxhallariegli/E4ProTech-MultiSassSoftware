<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class FirebaseSettings extends Component
{
    public string $firebaseCredentials = '';
    public bool $isFirebaseEnabled = false;
    public string $firebaseProjectId = '';
    public string $firebaseWebConfig = '';
    public bool $isFirebaseDebugEnabled = false;
    public string $browserToken = '';

    protected $listeners = ['fcm-token-received' => 'setBrowserToken'];

    public function setBrowserToken($token): void
    {
        $this->browserToken = $token;
    }

    public function mount(): void
    {
        $this->firebaseCredentials = Setting::where('key', 'firebase_credentials')->value('value') ?? '';
        $this->isFirebaseEnabled = (bool) Setting::where('key', 'firebase_enabled')->value('value');
        $this->firebaseProjectId = Setting::where('key', 'firebase_project_id')->value('value') ?? '';
        $this->firebaseWebConfig = Setting::where('key', 'firebase_web_config')->value('value') ?? '';
        $this->isFirebaseDebugEnabled = (bool) Setting::where('key', 'firebase_debug')->value('value');
    }

    public function render(): View
    {
        return view('livewire.admin.settings.firebase-settings');
    }

    public function update(): void
    {
        Setting::updateOrCreate(['key' => 'firebase_credentials'], ['value' => $this->firebaseCredentials]);
        Setting::updateOrCreate(['key' => 'firebase_enabled'], ['value' => $this->isFirebaseEnabled]);
        Setting::updateOrCreate(['key' => 'firebase_project_id'], ['value' => $this->firebaseProjectId]);
        Setting::updateOrCreate(['key' => 'firebase_web_config'], ['value' => $this->firebaseWebConfig]);
        Setting::updateOrCreate(['key' => 'firebase_debug'], ['value' => $this->isFirebaseDebugEnabled]);

        \Illuminate\Support\Facades\Cache::forget('settings');

        add_user_log([
            'title' => 'updated firebase settings',
            'link' => route('admin.settings'),
            'reference_id' => auth()->id(),
            'section' => 'Settings',
            'type' => 'Update',
        ]);

        $this->dispatch('toast', ['message' => __('settings.updated'), 'type' => 'success']);
    }

    public function testNotification(\App\Services\FirebaseService $service): void
    {
        $deviceCount = \App\Models\DeviceToken::count();

        if ($deviceCount > 0) {
            $sent = $service->sendToAllDevices(
                'Njoftim Testues 🔥',
                'Nëse e shihni këtë mesazh, Firebase Push Notifications funksionon 100%!'
            );

            if ($sent > 0) {
                $this->dispatch('toast', ['message' => "Njoftimi u dërgua me sukses te {$sent} pajisje!", 'type' => 'success']);
                return;
            }
        }

        $sent = $service->sendNotification(
            'Njoftim Testues 🔥',
            'Nëse e shihni këtë mesazh, Firebase Push Notifications funksionon 100%!',
            'all'
        );

        if ($sent) {
            $this->dispatch('toast', ['message' => 'Njoftimi u dërgua me sukses te tema (topic: all)!', 'type' => 'success']);
        } else {
            $this->dispatch('toast', ['message' => 'Dërgimi i njoftimit dështoi. Kontrolloni logjet te storage/logs/laravel.log.', 'type' => 'error']);
        }
    }
}
