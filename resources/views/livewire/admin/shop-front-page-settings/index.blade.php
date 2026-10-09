<div class="space-y-8 p-4 sm:p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] border border-stone-200 dark:border-gray-700 shadow-sm">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-black bg-amber-500/10 text-[#FF9F0A] mb-2">
                <span>🎨</span> Menaxhimi i Faqes Publike
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                Faqja Ime • {{ $shop->name }}
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">
                Personalizoni të gjitha tekstet, titujt, ngjyrat dhe të dhënat e faqes tuaj publike te <strong class="text-blue-600 font-mono">app.e4protech.com/s/{{ $shop->slug }}</strong>
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
                    <input type="text" wire:model="phone" placeholder="p.sh: +355691234567" class="w-full px-4 py-3 rounded-2xl bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 font-bold text-sm">
                </div>
            </div>
        </div>

        <!-- 2. Hero Section Content -->
        <div class="bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] border border-stone-200 dark:border-gray-700 shadow-sm space-y-6">
            <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                <span>🚀</span> Përmbajtja e Seksionit Hero (Ballina)
            </h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Teksti i Badge-it të Sipërm</label>
                    <input type="text" wire:model="hero_badge_text" placeholder="✨ Sallon Bukurie Zyrtare" class="w-full px-4 py-3 rounded-2xl bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 font-bold text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Titulli Kryesor i Ballinës (Hero Title) *</label>
                    <input type="text" wire:model="hero_title" placeholder="Eksperiencë Premium për Shërbime & Stilim" class="w-full px-4 py-3 rounded-2xl bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 font-extrabold text-base">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Përshkrimi i Ballinës (Hero Subtitle)</label>
                    <textarea wire:model="hero_subtitle" rows="3" class="w-full px-4 py-3 rounded-2xl bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 font-medium text-sm"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Teksti i Butonit të Rezervimit</label>
                    <input type="text" wire:model="hero_button_text" placeholder="Rezervo Takim Online ↗" class="w-full px-4 py-3 rounded-2xl bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 font-bold text-sm">
                </div>
            </div>
        </div>

        <!-- 3. Multi-Language Translations Editor -->
        <div class="bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] border border-stone-200 dark:border-gray-700 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                        <span>🌍</span> Përkthimet Shumë-Gjuhësh për Faqen Publike
                    </h3>
                    <p class="text-xs text-gray-500 font-medium mt-1">Menaxhoni tekstet e faqeve në çdo gjuhë për klientët tuaj ndërkombëtarë.</p>
                </div>

                <div class="flex items-center gap-2">
                    @foreach(['sq' => '🇦🇱 SQ', 'en' => '🇬🇧 EN', 'it' => '🇮🇹 IT', 'de' => '🇩🇪 DE', 'fr' => '🇫🇷 FR'] as $langKey => $langLabel)
                        <button type="button" wire:click="setLocale('{{ $langKey }}')"
                                class="px-4 py-2 rounded-xl text-xs font-black transition {{ $activeLocale === $langKey ? 'bg-blue-600 text-white shadow-md' : 'bg-stone-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300 hover:bg-stone-200' }}">
                            {{ $langLabel }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="p-6 rounded-2xl bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 space-y-4">
                <span class="text-xs font-black uppercase text-blue-600">Gjuha Aktive: {{ strtoupper($activeLocale) }}</span>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Titulli i Ballinës ({{ strtoupper($activeLocale) }})</label>
                    <input type="text" wire:model="translations.{{ $activeLocale }}.hero_title" class="w-full px-4 py-3 rounded-2xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 font-bold text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Përshkrimi i Ballinës ({{ strtoupper($activeLocale) }})</label>
                    <textarea wire:model="translations.{{ $activeLocale }}.hero_subtitle" rows="2" class="w-full px-4 py-3 rounded-2xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 font-medium text-sm"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Butoni ({{ strtoupper($activeLocale) }})</label>
                    <input type="text" wire:model="translations.{{ $activeLocale }}.hero_button_text" class="w-full px-4 py-3 rounded-2xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 font-bold text-sm">
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button type="submit" wire:loading.attr="disabled"
                    class="px-10 py-4 rounded-2xl font-black text-sm text-black bg-[#FF9F0A] hover:bg-amber-400 shadow-xl transition transform active:scale-95 flex items-center gap-2">
                <span wire:loading.remove>Ruaj Konfigurimin e Faqes 🚀</span>
                <span wire:loading>Duke ruajtur...</span>
            </button>
        </div>
    </form>
</div>
