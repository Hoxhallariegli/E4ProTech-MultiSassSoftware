<div class="space-y-8 p-4 sm:p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] border border-stone-200 dark:border-gray-700 shadow-sm">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-black bg-amber-500/10 text-[#FF9F0A] mb-2">
                <span>🎨</span> Menaxhimi i Plotë i Faqes Publike
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                Faqja Ime • {{ $shop->name }}
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">
                Personalizoni të gjitha tekstet, titujt, ngjyrat dhe përkthimet e faqes tuaj te <strong class="text-blue-600 font-mono">app.e4protech.com/s/{{ $shop->slug }}</strong>
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('shop.landing', $shop->slug) }}" target="_blank"
               class="px-6 py-3.5 rounded-2xl bg-[#FF9F0A] hover:bg-amber-400 text-black font-black text-xs shadow-lg transition transform active:scale-95 flex items-center gap-2">
                <span>Shiko Faqen Publike</span>
                <span>↗</span>
            </a>
        </div>
    </div>

    <!-- Success Message -->
    @include('errors.messages')

    <!-- Form Section -->
    <form wire:submit.prevent="saveSettings" class="space-y-8">

        <!-- 1. Branding & Theme Colors -->
        <div class="bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] border border-stone-200 dark:border-gray-700 shadow-sm space-y-6">
            <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                <span>🎨</span> Identiteti &amp; Ngjyrat e Temës
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Emri i Dyqanit *</label>
                    <input type="text" wire:model="name" class="w-full px-4 py-3 rounded-2xl bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 font-bold text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Titulli i Logos (Navbar)</label>
                    <input type="text" wire:model="app_name" class="w-full px-4 py-3 rounded-2xl bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 font-bold text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Lloji i Biznesit</label>
                    <select wire:model="business_type" class="w-full px-4 py-3 rounded-2xl bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 font-bold text-sm">
                        <option value="barber">💈 Barber Shop / Sallon Qethje</option>
                        <option value="beauty">💇‍♀️ Beauty Salon / Parukeri</option>
                        <option value="nails">💅 Nail Studio / Qendër Thonjsh</option>
                        <option value="spa">🌸 Spa &amp; Massage</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Ngjyra Kryesore (Primary Hex)</label>
                    <div class="flex items-center gap-3">
                        <input type="color" wire:model.live="primary_color" class="w-12 h-12 rounded-2xl cursor-pointer border-0">
                        <input type="text" wire:model.live="primary_color" class="w-full px-4 py-3 rounded-2xl bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 font-mono font-bold text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Ngjyra Dytësore (Secondary Hex)</label>
                    <div class="flex items-center gap-3">
                        <input type="color" wire:model.live="secondary_color" class="w-12 h-12 rounded-2xl cursor-pointer border-0">
                        <input type="text" wire:model.live="secondary_color" class="w-full px-4 py-3 rounded-2xl bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 font-mono font-bold text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-gray-300 uppercase tracking-wider mb-2">Numri i Telefonit</label>
                    <input type="text" wire:model="contact_phone" placeholder="p.sh: +355691234567" class="w-full px-4 py-3 rounded-2xl bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 font-bold text-sm">
                </div>
            </div>
        </div>

        <!-- 2. Dynamic Language Selector & Complete Front Page Content Editor -->
        <div class="bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] border border-stone-200 dark:border-gray-700 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                        <span>🌍</span> Menaxhimi i Plotë i Teksteve te Faqja Publike
                    </h3>
                    <p class="text-xs text-gray-500 font-medium mt-1">Zgjidhni gjuhën dhe menaxhoni të GJITHA tekstet e faqeve publike.</p>
                </div>

                <!-- Dynamic Registered Language Tabs -->
                <div class="flex items-center gap-2 overflow-x-auto py-1">
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
                                class="px-5 py-2.5 rounded-2xl text-xs font-black transition {{ $activeLocale === $langKey ? 'bg-blue-600 text-white shadow-md' : 'bg-stone-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300 hover:bg-stone-200' }}">
                            {{ $flag }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="p-6 sm:p-8 rounded-[2rem] bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 space-y-6">
                <div class="flex items-center justify-between border-b border-stone-200 dark:border-gray-800 pb-4">
                    <span class="text-xs font-black uppercase tracking-wider text-blue-600">Gjuha Aktive e Zgjedhur: {{ strtoupper($activeLocale) }}</span>
                    <span class="text-xs text-stone-400 font-semibold">Gjuhët e sistemit: {{ implode(', ', array_map('strtoupper', $this->supportedLocales)) }}</span>
                </div>

                <!-- A. Header & Working Status -->
                <div class="space-y-4">
                    <h4 class="text-xs font-black uppercase tracking-wider text-amber-500">1. Koka e Faqes (Header &amp; Nav)</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Banderola e Orarit të Punës</label>
                            <input type="text" wire:model="translations.{{ $activeLocale }}.working_hours_notice" placeholder="Hapur tani • 09:00 - 19:00" class="w-full px-4 py-3 rounded-2xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 font-bold text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Butoni i Kontaktit (Header)</label>
                            <input type="text" wire:model="translations.{{ $activeLocale }}.contact_button_text" placeholder="📞 Kontakt" class="w-full px-4 py-3 rounded-2xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 font-bold text-sm">
                        </div>
                    </div>
                </div>

                <!-- B. Hero Section -->
                <div class="space-y-4 pt-4 border-t border-stone-200 dark:border-gray-800">
                    <h4 class="text-xs font-black uppercase tracking-wider text-amber-500">2. Seksioni Hero (Ballina)</h4>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Badge i Sipërm (Hero Badge)</label>
                            <input type="text" wire:model="translations.{{ $activeLocale }}.hero_badge_text" placeholder="✨ Sallon Bukurie Zyrtare" class="w-full px-4 py-3 rounded-2xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 font-bold text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Titulli Kryesor (Hero Title) *</label>
                            <input type="text" wire:model="translations.{{ $activeLocale }}.hero_title" placeholder="Eksperiencë Premium për Shërbime & Stilim" class="w-full px-4 py-3 rounded-2xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 font-extrabold text-base">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Përshkrimi i Ballinës (Hero Subtitle)</label>
                            <textarea wire:model="translations.{{ $activeLocale }}.hero_subtitle" rows="2" class="w-full px-4 py-3 rounded-2xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 font-medium text-sm"></textarea>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Butoni i Rezervimit (Kryesor)</label>
                                <input type="text" wire:model="translations.{{ $activeLocale }}.hero_button_text" placeholder="Rezervo Takim Online ↗" class="w-full px-4 py-3 rounded-2xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 font-bold text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Butoni Dytësor</label>
                                <input type="text" wire:model="translations.{{ $activeLocale }}.secondary_button_text" placeholder="Shiko Shërbimet & Çmimet" class="w-full px-4 py-3 rounded-2xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 font-bold text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- C. Staff & Services Section -->
                <div class="space-y-4 pt-4 border-t border-stone-200 dark:border-gray-800">
                    <h4 class="text-xs font-black uppercase tracking-wider text-amber-500">3. Seksioni i Stafit &amp; Shërbimeve</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Badge i Stafit</label>
                            <input type="text" wire:model="translations.{{ $activeLocale }}.staff_badge_text" placeholder="Ekipi Ynë" class="w-full px-4 py-3 rounded-2xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 font-bold text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Titulli i Stafit</label>
                            <input type="text" wire:model="translations.{{ $activeLocale }}.staff_title" placeholder="Parukierët Tanë" class="w-full px-4 py-3 rounded-2xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 font-bold text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Badge i Shërbimeve</label>
                            <input type="text" wire:model="translations.{{ $activeLocale }}.services_badge_text" placeholder="Çmimet & Kohëzgjatja" class="w-full px-4 py-3 rounded-2xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 font-bold text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Titulli i Shërbimeve</label>
                            <input type="text" wire:model="translations.{{ $activeLocale }}.services_title" placeholder="Shërbimet e Ofruara" class="w-full px-4 py-3 rounded-2xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 font-bold text-sm">
                        </div>
                    </div>
                </div>

                <!-- D. Contact & Footer -->
                <div class="space-y-4 pt-4 border-t border-stone-200 dark:border-gray-800">
                    <h4 class="text-xs font-black uppercase tracking-wider text-amber-500">4. Kontaktet &amp; Footer</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">E-mail i Kontaktit</label>
                            <input type="email" wire:model="translations.{{ $activeLocale }}.contact_email" placeholder="info@salloni.al" class="w-full px-4 py-3 rounded-2xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 font-bold text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Adresa e Sallonit</label>
                            <input type="text" wire:model="translations.{{ $activeLocale }}.contact_address" placeholder="Tiranë, Shqipëri" class="w-full px-4 py-3 rounded-2xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 font-bold text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Teksti i Footer-it</label>
                            <input type="text" wire:model="translations.{{ $activeLocale }}.footer_text" placeholder="© 2026 Salloni Juaj — Mundësuar nga E4ProTech Engine" class="w-full px-4 py-3 rounded-2xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 font-bold text-sm">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button type="submit" wire:loading.attr="disabled"
                    class="px-10 py-4 rounded-2xl font-black text-sm text-black bg-[#FF9F0A] hover:bg-amber-400 shadow-xl transition transform active:scale-95 flex items-center gap-2">
                <span wire:loading.remove>Ruaj Konfigurimin e Plotë të Faqes 🚀</span>
                <span wire:loading>Duke ruajtur...</span>
            </button>
        </div>
    </form>
</div>
