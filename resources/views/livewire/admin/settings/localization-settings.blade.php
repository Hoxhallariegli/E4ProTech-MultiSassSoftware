<div>
    <div class="card">
        <h3 class="mb-4">{{ __('settings.Localization Settings') }}</h3>

        <div class="space-y-6">
            <div>
                <x-form wire:submit="update" method="put">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.input wire:model="supportedLocales" name="supportedLocales" :label="__('settings.Supported Locales (comma separated)')" placeholder="en, sq, de, it" />
                    </div>
                    <div class="mt-4">
                        <x-button>{{ __('settings.Update Localization Settings') }}</x-button>
                    </div>
                </x-form>
            </div>

            <div class="pt-6 border-t border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">{{ __('settings.APK Localization Sync') }}</h4>
                        <p class="text-xs text-gray-500 mt-1">{{ __('settings.Sync all existing module translations to the Flutter mobile app files.') }}</p>
                    </div>
                    <x-button wire:click="syncApk" variant="secondary">
                        <x-heroicon-o-arrow-path wire:loading.class="animate-spin" wire:target="syncApk" class="size-4 mr-2" />
                        {{ __('settings.Sync to APK') }} @l10n
                    </x-button>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">{{ __('settings.Deep Translation Editor') }}</h4>
                        <p class="text-xs text-gray-500 mt-1">{{ __('settings.Manage every key/value pair for all modules and languages.') }}</p>
                    </div>
                    <x-a :href="route('admin.settings.languages.index')" variant="outline" size="sm">
                        <x-heroicon-o-language class="size-4 mr-2" />
                        {{ __('settings.Open Editor') }}
                    </x-a>
                </div>
            </div>
        </div>
    </div>
</div>
