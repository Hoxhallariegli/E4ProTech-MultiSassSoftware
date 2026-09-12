<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Illuminate\Support\Facades\File;

class LocalizationSettings extends Component
{
    public string $supportedLocales = 'en, sq';

    public function mount(): void
    {
        $this->supportedLocales = Setting::where('key', 'supported_locales_csv')->value('value') ?? 'en, sq';
    }

    public function render(): View
    {
        return view('livewire.admin.settings.localization-settings');
    }

    public function update(): void
    {
        $locales = array_map('trim', explode(',', $this->supportedLocales));
        $locales = array_values(array_unique(array_filter($locales)));

        Setting::updateOrCreate(['key' => 'supported_locales_csv'], ['value' => implode(', ', $locales)]);
        Setting::updateOrCreate(['key' => 'supported_locales'], ['value' => json_encode($locales)]);

        // Ensure directories exist for all locales
        foreach ($locales as $locale) {
            $path = lang_path($locale);
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }
        }

        \Illuminate\Support\Facades\Cache::forget('settings');

        add_user_log([
            'title' => 'updated localization settings',
            'link' => route('admin.settings'),
            'reference_id' => auth()->id(),
            'section' => 'Settings',
            'type' => 'Update',
        ]);

        $this->dispatch('toast', ['message' => 'Localization Settings Updated!', 'type' => 'success']);
    }

    public function syncApk(): void
    {
        try {
            // Trigger the sync all logic from Languages component or Service
            $languagesComponent = new Languages();
            $languagesComponent->loadLanguages(); // Ensure languages are loaded
            $languagesComponent->syncAllToFlutter(app(\App\Services\FlutterL10nService::class));
            $this->dispatch('toast', ['message' => 'Translations synced to Flutter APK!', 'type' => 'success']);
        } catch (\Exception $e) {
            $this->dispatch('toast', ['message' => 'Sync failed: ' . $e->getMessage(), 'type' => 'error']);
        }
    }
}
