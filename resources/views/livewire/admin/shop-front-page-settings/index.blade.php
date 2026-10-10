<div>
    <div class="card !p-0 overflow-hidden shadow-none border-gray-200 dark:border-gray-700 dark:bg-gray-800">
        <!-- Header Row (Matches NewView / Photo 1 Layout) -->
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <x-h1>{{ __('shop-front-page-settings.My Front Page') }} • {{ $shop->name }}</x-h1>
                    <x-short-description class="dark:text-gray-400">
                        {{ __('shop-front-page-settings.Customize all texts, titles, colors and translations for your public page at') }}
                        <span class="font-mono font-bold text-gray-800 dark:text-gray-200">{{ url("s/{$shop->slug}") }}</span>
                    </x-short-description>
                </div>
                <div class="flex items-center gap-3">
                    <x-btn :href="route('shop.landing', $shop->slug)" target="_blank" icon="paper-airplane">
                        {{ __('shop-front-page-settings.View Public Page') }} &rarr;
                    </x-btn>
                </div>
            </div>
        </div>

        @include('errors.errors')
        @include('errors.messages')

        <!-- Form Body Area -->
        <div class="p-6 sm:p-10">
            <form wire:submit.prevent="saveSettings" class="space-y-10">

                <!-- 1. Branding & Theme Colors -->
                <div class="space-y-6">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 text-lg">
                            {{ __('shop-front-page-settings.Branding & Theme Colors') }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">{{ __('shop-front-page-settings.Set logo, name and colors for your public page') }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <x-form.input name="name" type="text" wire:model="name" :label="__('shop-front-page-settings.Shop Name') . ' *'" class="dark:bg-gray-900" />
                        </div>
                        <div>
                            <x-form.input name="app_name" type="text" wire:model="app_name" :label="__('shop-front-page-settings.Logo Title (Navbar)')" class="dark:bg-gray-900" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('shop-front-page-settings.Business Type') }}</label>
                            <select wire:model="business_type" class="w-full px-4 py-3 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 text-sm font-medium">
                                <option value="barbershop">{{ __('shop-front-page-settings.Barbershop') }}</option>
                                <option value="beauty_salon">{{ __('shop-front-page-settings.Beauty Salon') }}</option>
                                <option value="nail_studio">{{ __('shop-front-page-settings.Nail Studio') }}</option>
                                <option value="spa">{{ __('shop-front-page-settings.Aesthetic Center & Spa') }}</option>
                            </select>
                        </div>

                        <!-- Primary Color Picker Popover -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">{{ __('shop-front-page-settings.Primary Color (Primary Hex)') }}</label>
                            <div x-data="{ open: false }" class="relative">
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="open = !open"
                                            class="w-12 h-12 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-center transition hover:scale-105 shrink-0"
                                            style="background-color: {{ $primary_color }};">
                                    </button>
                                    <x-form.input name="primary_color" type="text" wire:model.live="primary_color" label="none" placeholder="#FF9F0A" class="dark:bg-gray-900 font-mono w-full" />
                                </div>

                                <!-- Popover Dropdown -->
                                <div x-show="open" @click.away="open = false" x-cloak
                                     class="absolute z-50 mt-2 p-4 w-72 bg-white dark:bg-gray-900 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700 space-y-3">
                                    <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800">
                                        <span class="text-xs font-bold uppercase text-gray-700 dark:text-gray-300">{{ __('shop-front-page-settings.Color Suggestions') }}</span>
                                        <input type="color" wire:model.live="primary_color" class="w-8 h-8 rounded-xl cursor-pointer border-0 bg-transparent">
                                    </div>
                                    <div class="grid grid-cols-5 gap-2">
                                        @php
                                            $colorPresets = [
                                                ['name' => 'Luxury Gold', 'hex' => '#FF9F0A'],
                                                ['name' => 'Indigo Blue', 'hex' => '#2563EB'],
                                                ['name' => 'Royal Purple', 'hex' => '#7C3AED'],
                                                ['name' => 'Rose Pink', 'hex' => '#EC4899'],
                                                ['name' => 'Emerald Green', 'hex' => '#059669'],
                                                ['name' => 'Crimson Red', 'hex' => '#DC2626'],
                                                ['name' => 'Coral Peach', 'hex' => '#F43F5E'],
                                                ['name' => 'Teal Cyan', 'hex' => '#0D9488'],
                                                ['name' => 'Slate Dark', 'hex' => '#334155'],
                                                ['name' => 'Bronze Brown', 'hex' => '#B45309'],
                                            ];
                                        @endphp
                                        @foreach($colorPresets as $preset)
                                            <button type="button"
                                                    wire:click="$set('primary_color', '{{ $preset['hex'] }}'); open = false;"
                                                    title="{{ $preset['name'] }}"
                                                    class="w-9 h-9 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-center transition hover:scale-110"
                                                    style="background-color: {{ $preset['hex'] }};">
                                                @if($primary_color === $preset['hex'])
                                                    <span class="text-white text-xs font-black">&check;</span>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Secondary Color Picker Popover -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">{{ __('shop-front-page-settings.Secondary Color (Secondary Hex)') }}</label>
                            <div x-data="{ open: false }" class="relative">
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="open = !open"
                                            class="w-12 h-12 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-center transition hover:scale-105 shrink-0"
                                            style="background-color: {{ $secondary_color }};">
                                    </button>
                                    <x-form.input name="secondary_color" type="text" wire:model.live="secondary_color" label="none" placeholder="#1C1C1E" class="dark:bg-gray-900 font-mono w-full" />
                                </div>

                                <!-- Popover Dropdown -->
                                <div x-show="open" @click.away="open = false" x-cloak
                                     class="absolute z-50 mt-2 p-4 w-72 bg-white dark:bg-gray-900 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700 space-y-3">
                                    <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-800">
                                        <span class="text-xs font-bold uppercase text-gray-700 dark:text-gray-300">{{ __('shop-front-page-settings.Color Suggestions') }}</span>
                                        <input type="color" wire:model.live="secondary_color" class="w-8 h-8 rounded-xl cursor-pointer border-0 bg-transparent">
                                    </div>
                                    <div class="grid grid-cols-5 gap-2">
                                        @foreach($colorPresets as $preset)
                                            <button type="button"
                                                    wire:click="$set('secondary_color', '{{ $preset['hex'] }}'); open = false;"
                                                    title="{{ $preset['name'] }}"
                                                    class="w-9 h-9 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-center transition hover:scale-110"
                                                    style="background-color: {{ $preset['hex'] }};">
                                                @if($secondary_color === $preset['hex'])
                                                    <span class="text-white text-xs font-black">&check;</span>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <x-form.input name="contact_phone" type="text" wire:model="contact_phone" :label="__('shop-front-page-settings.Phone Number')" placeholder="+355691234567" class="dark:bg-gray-900" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                        <div>
                            <x-file-upload name="logo" wire:model="logo" :label="__('shop-front-page-settings.Shop Logo')" id="logo" :isEditing="true" />
                        </div>
                        <div>
                            <x-file-upload name="banner" wire:model="banner" :label="__('shop-front-page-settings.Hero Banner Image')" id="banner" :isEditing="true" />
                        </div>
                    </div>
                </div>

                <!-- 2. Dynamic Language Selector & Complete Front Page Content Editor -->
                <div class="space-y-6 pt-8 border-t border-gray-100 dark:border-gray-700">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-gray-100 text-lg">
                                {{ __('shop-front-page-settings.Full Text Management for Public Page') }}
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">{{ __('shop-front-page-settings.Select language and manage all public page texts') }}</p>
                        </div>

                        <!-- Dynamic Registered Language Tabs -->
                        <div class="flex items-center gap-2">
                            @foreach($this->supportedLocales as $langKey)
                                <button type="button" wire:click="setLocale('{{ $langKey }}')"
                                        class="px-5 py-2.5 rounded-2xl text-xs font-bold uppercase transition {{ $activeLocale === $langKey ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200' }}">
                                    {{ strtoupper($langKey) }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="p-6 sm:p-8 rounded-[2rem] bg-gray-50 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-700 space-y-6">
                        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-4">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">{{ __('shop-front-page-settings.Selected Active Language') }}: {{ strtoupper($activeLocale) }}</span>
                            <span class="text-xs text-gray-400">{{ __('shop-front-page-settings.System Languages') }}: {{ implode(', ', array_map('strtoupper', $this->supportedLocales)) }}</span>
                        </div>

                        <!-- A. Header & Working Status -->
                        <div class="space-y-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">{{ __('shop-front-page-settings.Header & Navigation') }}</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <x-form.input name="working_hours_notice" type="text" wire:model="translations.{{ $activeLocale }}.working_hours_notice" :label="__('shop-front-page-settings.Working Hours Notice')" placeholder="Hapur tani • 09:00 - 19:00" class="dark:bg-gray-900" />
                                </div>
                                <div>
                                    <x-form.input name="contact_button_text" type="text" wire:model="translations.{{ $activeLocale }}.contact_button_text" :label="__('shop-front-page-settings.Contact Button (Header)')" placeholder="Kontakt" class="dark:bg-gray-900" />
                                </div>
                            </div>
                        </div>

                        <!-- B. Hero Section -->
                        <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">{{ __('shop-front-page-settings.Hero Section') }}</h4>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <x-form.input name="hero_badge_text" type="text" wire:model="translations.{{ $activeLocale }}.hero_badge_text" :label="__('shop-front-page-settings.Hero Badge Text')" placeholder="Sallon Bukurie Zyrtare" class="dark:bg-gray-900" />
                                </div>
                                <div>
                                    <x-form.input name="hero_title" type="text" wire:model="translations.{{ $activeLocale }}.hero_title" :label="__('shop-front-page-settings.Hero Title')" placeholder="Eksperiencë Premium për Shërbime & Stilim" class="dark:bg-gray-900" />
                                </div>
                                <div>
                                    <x-form.textarea name="hero_subtitle" wire:model="translations.{{ $activeLocale }}.hero_subtitle" :label="__('shop-front-page-settings.Hero Subtitle')" class="dark:bg-gray-900" />
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <x-form.input name="hero_button_text" type="text" wire:model="translations.{{ $activeLocale }}.hero_button_text" :label="__('shop-front-page-settings.Main Booking Button')" placeholder="Rezervo Takim Online &rarr;" class="dark:bg-gray-900" />
                                    </div>
                                    <div>
                                        <x-form.input name="secondary_button_text" type="text" wire:model="translations.{{ $activeLocale }}.secondary_button_text" :label="__('shop-front-page-settings.Secondary Button')" placeholder="Shiko Shërbimet & Çmimet" class="dark:bg-gray-900" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- C. Staff & Services Section -->
                        <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">{{ __('shop-front-page-settings.Staff & Services Section') }}</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <x-form.input name="staff_badge_text" type="text" wire:model="translations.{{ $activeLocale }}.staff_badge_text" :label="__('shop-front-page-settings.Staff Badge')" placeholder="Ekipi Ynë" class="dark:bg-gray-900" />
                                </div>
                                <div>
                                    <x-form.input name="staff_title" type="text" wire:model="translations.{{ $activeLocale }}.staff_title" :label="__('shop-front-page-settings.Staff Title')" placeholder="Parukierët Tanë" class="dark:bg-gray-900" />
                                </div>
                                <div>
                                    <x-form.input name="services_badge_text" type="text" wire:model="translations.{{ $activeLocale }}.services_badge_text" :label="__('shop-front-page-settings.Services Badge')" placeholder="Çmimet & Kohëzgjatja" class="dark:bg-gray-900" />
                                </div>
                                <div>
                                    <x-form.input name="services_title" type="text" wire:model="translations.{{ $activeLocale }}.services_title" :label="__('shop-front-page-settings.Services Title')" placeholder="Shërbimet e Ofruara" class="dark:bg-gray-900" />
                                </div>
                            </div>
                        </div>

                        <!-- D. Contact & Footer -->
                        <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">{{ __('shop-front-page-settings.Contact & Footer') }}</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <x-form.input name="contact_email" type="email" wire:model="translations.{{ $activeLocale }}.contact_email" :label="__('shop-front-page-settings.Contact Email')" placeholder="info@salloni.al" class="dark:bg-gray-900" />
                                </div>
                                <div>
                                    <x-form.input name="contact_address" type="text" wire:model="translations.{{ $activeLocale }}.contact_address" :label="__('shop-front-page-settings.Shop Address')" placeholder="Tiranë, Shqipëri" class="dark:bg-gray-900" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-form.input name="footer_text" type="text" wire:model="translations.{{ $activeLocale }}.footer_text" :label="__('shop-front-page-settings.Footer Text')" placeholder="© 2026 Salloni Juaj — Mundësuar nga E4ProTech Engine" class="dark:bg-gray-900" />
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-10 flex justify-end">
                    <x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl">
                        {{ __('shop-front-page-settings.Save Full Page Settings') }}
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>
