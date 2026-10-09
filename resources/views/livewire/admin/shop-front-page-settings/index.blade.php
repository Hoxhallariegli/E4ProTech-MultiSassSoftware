<div class="space-y-10">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4 px-1">
        <div>
            <x-h1>Faqja Ime • {{ $shop->name }}</x-h1>
            <x-short-description class="dark:text-gray-400">Personalizoni të gjitha tekstet, titujt, ngjyrat dhe përkthimet e faqes tuaj publike te app.e4protech.com/s/{{ $shop->slug }}</x-short-description>
        </div>
        <x-btn :href="route('shop.landing', $shop->slug)" target="_blank" variant="blue" class="!px-6 !py-3 !rounded-2xl">
            Shiko Faqen Publike ↗
        </x-btn>
    </div>

    @include('errors.errors')
    @include('errors.messages')

    <!-- Form Section -->
    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700">
        <form wire:submit.prevent="saveSettings" class="space-y-10">

            <!-- 1. Branding & Theme Colors -->
            <div class="space-y-6">
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-gray-100 text-lg flex items-center gap-2">
                        <span>🎨</span> Identiteti &amp; Ngjyrat e Temës
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">Vendosni logon, emrin dhe ngjyrat e faqes tuaj publike.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <x-form.input name="name" type="text" wire:model="name" label="Emri i Dyqanit *" class="dark:bg-gray-900" />
                    </div>
                    <div>
                        <x-form.input name="app_name" type="text" wire:model="app_name" label="Titulli i Logos (Navbar)" class="dark:bg-gray-900" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Lloji i Biznesit (Business Type)</label>
                        <select wire:model="business_type" class="w-full px-4 py-3 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 text-sm font-medium">
                            <option value="barbershop">💈 Berberanë (Barbershop)</option>
                            <option value="beauty_salon">💇‍♀️ Sallon Bukurie / Parukeri (Beauty Salon)</option>
                            <option value="nail_studio">💅 Studio Thonjsh (Nail Studio)</option>
                            <option value="spa">💆‍♀️ Qendër Estetike &amp; Spa (Aesthetic Center)</option>
                        </select>
                    </div>
                </div>

                <!-- 10 Color Presets Card & Color Picker -->
                <div class="bg-gray-50 dark:bg-gray-900/60 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 space-y-4">
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-gray-100 text-sm">Zgjidhni Ngjyrën e Temës (Color Picker &amp; 10 Presets)</h4>
                        <p class="text-xs text-gray-500">Zgjidhni një nga ngjyrat automatike ose përdorni color picker-in për ngjyrën tuaj unike.</p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 pt-2">
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
                                    wire:click="$set('primary_color', '{{ $preset['hex'] }}')"
                                    class="p-2.5 rounded-xl border text-left flex items-center gap-2.5 transition-all {{ $primary_color === $preset['hex'] ? 'border-blue-600 ring-2 ring-blue-500/20 bg-blue-50/50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300' }}">
                                <span class="w-5 h-5 rounded-full shrink-0 shadow-sm" style="background-color: {{ $preset['hex'] }};"></span>
                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate">{{ $preset['name'] }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Ngjyra Kryesore (Primary Hex)</label>
                            <div class="flex items-center gap-3">
                                <input type="color" wire:model.live="primary_color" class="w-12 h-12 rounded-2xl cursor-pointer border-0 bg-transparent shrink-0">
                                <x-form.input name="primary_color" type="text" wire:model.live="primary_color" label="none" placeholder="#FF9F0A" class="dark:bg-gray-900 font-mono w-full" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Ngjyra Dytësore (Secondary Hex)</label>
                            <div class="flex items-center gap-3">
                                <input type="color" wire:model.live="secondary_color" class="w-12 h-12 rounded-2xl cursor-pointer border-0 bg-transparent shrink-0">
                                <x-form.input name="secondary_color" type="text" wire:model.live="secondary_color" label="none" placeholder="#1C1C1E" class="dark:bg-gray-900 font-mono w-full" />
                            </div>
                        </div>

                        <div>
                            <x-form.input name="contact_phone" type="text" wire:model="contact_phone" label="Numri i Telefonit" placeholder="+355691234567" class="dark:bg-gray-900" />
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                    <div>
                        <x-file-upload name="logo" wire:model="logo" label="Logoja e Dyqanit" id="logo" :isEditing="true" />
                    </div>
                    <div>
                        <x-file-upload name="banner" wire:model="banner" label="Imazhi i Ballinës (Banner)" id="banner" :isEditing="true" />
                    </div>
                </div>
            </div>

            <!-- 2. Dynamic Language Selector & Complete Front Page Content Editor -->
            <div class="space-y-6 pt-8 border-t border-gray-100 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 text-lg flex items-center gap-2">
                            <span>🌍</span> Përkthimet Shumë-Gjuhësh për Faqen Publike
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">Zgjidhni gjuhën dhe menaxhoni të gjitha tekstet e faqeve publike.</p>
                    </div>

                    <!-- Dynamic Registered Language Tabs -->
                    <div class="flex items-center gap-2">
                        @foreach($this->supportedLocales as $langKey)
                            @php
                                $flag = match($langKey) {
                                    'sq' => '🇦🇱 SQ',
                                    'en' => '🇬🇧 EN',
                                    'it' => '🇮🇹 IT',
                                    'de' => '🇩🇪 DE',
                                    'fr' => '🇫🇷 FR',
                                    default => strtoupper($langKey),
                                };
                            @endphp
                            <button type="button" wire:click="setLocale('{{ $langKey }}')"
                                    class="px-5 py-2.5 rounded-2xl text-xs font-bold transition {{ $activeLocale === $langKey ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200' }}">
                                {{ $flag }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="p-6 sm:p-8 rounded-[2rem] bg-gray-50 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-700 space-y-6">
                    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-4">
                        <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Gjuha Aktive e Zgjedhur: {{ strtoupper($activeLocale) }}</span>
                        <span class="text-xs text-gray-400">Gjuhët e sistemit: {{ implode(', ', array_map('strtoupper', $this->supportedLocales)) }}</span>
                    </div>

                    <!-- A. Header & Working Status -->
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">1. Koka e Faqes (Header &amp; Nav)</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-form.input name="working_hours_notice" type="text" wire:model="translations.{{ $activeLocale }}.working_hours_notice" label="Banderola e Orarit të Punës" placeholder="Hapur tani • 09:00 - 19:00" class="dark:bg-gray-900" />
                            </div>
                            <div>
                                <x-form.input name="contact_button_text" type="text" wire:model="translations.{{ $activeLocale }}.contact_button_text" label="Butoni i Kontaktit (Header)" placeholder="📞 Kontakt" class="dark:bg-gray-900" />
                            </div>
                        </div>
                    </div>

                    <!-- B. Hero Section -->
                    <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">2. Seksioni Hero (Ballina)</h4>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <x-form.input name="hero_badge_text" type="text" wire:model="translations.{{ $activeLocale }}.hero_badge_text" label="Badge i Sipërm (Hero Badge)" placeholder="✨ Sallon Bukurie Zyrtare" class="dark:bg-gray-900" />
                            </div>
                            <div>
                                <x-form.input name="hero_title" type="text" wire:model="translations.{{ $activeLocale }}.hero_title" label="Titulli Kryesor (Hero Title) *" placeholder="Eksperiencë Premium për Shërbime & Stilim" class="dark:bg-gray-900" />
                            </div>
                            <div>
                                <x-form.textarea name="hero_subtitle" wire:model="translations.{{ $activeLocale }}.hero_subtitle" label="Përshkrimi i Ballinës (Hero Subtitle)" class="dark:bg-gray-900" />
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <x-form.input name="hero_button_text" type="text" wire:model="translations.{{ $activeLocale }}.hero_button_text" label="Butoni i Rezervimit (Kryesor)" placeholder="Rezervo Takim Online ↗" class="dark:bg-gray-900" />
                                </div>
                                <div>
                                    <x-form.input name="secondary_button_text" type="text" wire:model="translations.{{ $activeLocale }}.secondary_button_text" label="Butoni Dytësor" placeholder="Shiko Shërbimet & Çmimet" class="dark:bg-gray-900" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- C. Staff & Services Section -->
                    <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">3. Seksioni i Stafit &amp; Shërbimeve</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-form.input name="staff_badge_text" type="text" wire:model="translations.{{ $activeLocale }}.staff_badge_text" label="Badge i Stafit" placeholder="Ekipi Ynë" class="dark:bg-gray-900" />
                            </div>
                            <div>
                                <x-form.input name="staff_title" type="text" wire:model="translations.{{ $activeLocale }}.staff_title" label="Titulli i Stafit" placeholder="Parukierët Tanë" class="dark:bg-gray-900" />
                            </div>
                            <div>
                                <x-form.input name="services_badge_text" type="text" wire:model="translations.{{ $activeLocale }}.services_badge_text" label="Badge i Shërbimeve" placeholder="Çmimet & Kohëzgjatja" class="dark:bg-gray-900" />
                            </div>
                            <div>
                                <x-form.input name="services_title" type="text" wire:model="translations.{{ $activeLocale }}.services_title" label="Titulli i Shërbimeve" placeholder="Shërbimet e Ofruara" class="dark:bg-gray-900" />
                            </div>
                        </div>
                    </div>

                    <!-- D. Contact & Footer -->
                    <div class="space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">4. Kontaktet &amp; Footer</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-form.input name="contact_email" type="email" wire:model="translations.{{ $activeLocale }}.contact_email" label="E-mail i Kontaktit" placeholder="info@salloni.al" class="dark:bg-gray-900" />
                            </div>
                            <div>
                                <x-form.input name="contact_address" type="text" wire:model="translations.{{ $activeLocale }}.contact_address" label="Adresa e Sallonit" placeholder="Tiranë, Shqipëri" class="dark:bg-gray-900" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-form.input name="footer_text" type="text" wire:model="translations.{{ $activeLocale }}.footer_text" label="Teksti i Footer-it" placeholder="© 2026 Salloni Juaj — Mundësuar nga E4ProTech Engine" class="dark:bg-gray-900" />
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-10 flex justify-end">
                <x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl">
                    Ruaj Konfigurimin e Plotë të Faqes 🚀
                </x-button>
            </div>
        </form>
    </div>
</div>
