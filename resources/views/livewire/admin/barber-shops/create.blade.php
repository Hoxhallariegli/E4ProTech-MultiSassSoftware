<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1"><div><x-h1>{{ __('barber-shops.Add BarberShop') }}</x-h1><x-short-description class="dark:text-gray-400">{{ __('barber-shops.Create new') }}</x-short-description></div><x-back-btn route="admin.barber-shops.index" /></div>
    @include('errors.errors')
    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700">
        <form wire:submit.prevent="store" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                <div>
                    <div class="flex items-end gap-2">
                        <div class="flex-1"><x-form.dropdown-search name="owner_id" wire:model.live="owner_id" :label="__('barber-shops.Owner Id')" :data="$owners" /></div>
                        <x-modal>
                            <x-slot name="trigger"><button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button></x-slot>
                            <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Add New User</div></x-slot>
                            <x-slot name="content"><livewire:admin.users.quick-create /></x-slot>
                        </x-modal>
                    </div>
                </div>
                <div><x-form.input name="name" type="text" wire:model.live="name" :label="__('barber-shops.Name')" class="dark:bg-gray-900" /></div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Lloji i Biznesit (Business Type)</label>
                    <select wire:model.live="business_type" class="w-full px-4 py-3 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        <option value="barbershop">💈 Berberanë (Barbershop)</option>
                        <option value="beauty_salon">💇‍♀️ Sallon Bukurie / Parukeri (Beauty Salon)</option>
                        <option value="nail_studio">💅 Studio Thonjsh (Nail Studio)</option>
                        <option value="spa">💆‍♀️ Qendër Estetike & Spa (Aesthetic Center)</option>
                        <option value="general">✨ Biznes Përgjithshëm (General Staff)</option>
                    </select>
                </div>
                <div><x-form.input name="app_name" type="text" wire:model.live="app_name" :label="__('barber-shops.App Name')" class="dark:bg-gray-900" /></div>
                <div><x-form.input name="slug" type="text" wire:model.live="slug" :label="__('barber-shops.Slug')" class="dark:bg-gray-900" /></div>
                <div><x-file-upload name="logo" wire:model="logo" :label="__('barber-shops.Logo')" id="logo" :isEditing="false" /></div>
                <div><x-file-upload name="banner" wire:model="banner" :label="__('barber-shops.Banner')" id="banner" :isEditing="false" /></div>

                <!-- 10 Color Presets Card -->
                <div class="col-span-1 md:col-span-2 bg-slate-50 dark:bg-gray-900/60 p-6 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-4">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 text-sm">Zgjidhni Ngjyrën e Temës së Biznesit (Primary & Secondary Color)</h3>
                        <p class="text-xs text-gray-500">10 ngjyrat më të përshtatshme që shkojnë në mënyrë të përsosur si në Dritë (Light) ashtu edhe në Errët (Dark Mode).</p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 pt-2">
                        @php
                            $colorPresets = [
                                ['name' => 'Indigo Blue', 'hex' => '#2563EB'],
                                ['name' => 'Royal Purple', 'hex' => '#7C3AED'],
                                ['name' => 'Rose Pink', 'hex' => '#EC4899'],
                                ['name' => 'Emerald Green', 'hex' => '#059669'],
                                ['name' => 'Luxury Gold', 'hex' => '#D97706'],
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                        <div>
                            <x-form.input name="primary_color" type="text" wire:model.live="primary_color" label="Ngjyra Kryesore (Primary Hex)" placeholder="#2563EB" class="dark:bg-gray-900 font-mono" />
                        </div>
                        <div>
                            <x-form.input name="secondary_color" type="text" wire:model.live="secondary_color" label="Ngjyra Dytësore (Secondary Hex)" placeholder="#F59E0B" class="dark:bg-gray-900 font-mono" />
                        </div>
                    </div>
                </div>

                <div class="col-span-1 md:col-span-2 bg-gray-50 dark:bg-gray-900/50 p-6 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-4">
                    <h3 class="font-bold text-gray-800 dark:text-gray-200">Etiketat e Personalizuara (Custom Labels Override)</h3>
                    <p class="text-xs text-gray-500">Lërini bosh nëse dëshironi të përdoren emërtimet automatike të llojit të biznesit.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><x-form.input name="staff_label" type="text" wire:model.live="staff_label" label="Emri i Stafit (Tëskës, p.sh: Parukier, Berber, Specialist)" class="dark:bg-gray-900" /></div>
                        <div><x-form.input name="staff_label_plural" type="text" wire:model.live="staff_label_plural" label="Emri i Stafit (Shumës, p.sh: Parukierët, Berberët, Stafi)" class="dark:bg-gray-900" /></div>
                        <div><x-form.input name="shop_label" type="text" wire:model.live="shop_label" label="Emri i Sallonit (p.sh: Salloni, Berberana, Studio)" class="dark:bg-gray-900" /></div>
                        <div><x-form.input name="service_label" type="text" wire:model.live="service_label" label="Emri i Shërbimit (p.sh: Shërbimi, Trajtimi)" class="dark:bg-gray-900" /></div>
                    </div>
                </div>

                <div><x-form.input name="trial_ends_at" type="datetime-local" wire:model.live="trial_ends_at" :label="__('barber-shops.Trial Ends At')" class="dark:bg-gray-900" /></div>
                <div><x-form.input name="expires_at" type="datetime-local" wire:model.live="expires_at" :label="__('barber-shops.Expires At')" class="dark:bg-gray-900" /></div>
                <div><x-form.checkbox name="active" wire:model="active" :label="__('barber-shops.Active')" /></div>
                <div><x-form.checkbox name="sms_enabled" wire:model="sms_enabled" :label="__('barber-shops.Sms Enabled')" /></div>
                <div><x-form.input name="timezone" type="text" wire:model.live="timezone" :label="__('barber-shops.Timezone')" class="dark:bg-gray-900" /></div>
                <div><x-form.input name="max_no_show_before_block" type="number" step="1" wire:model.live="max_no_show_before_block" :label="__('barber-shops.Max No Show Before Block')" class="dark:bg-gray-900" /></div>
                <div><x-form.input name="min_service_time" type="number" step="1" wire:model.live="min_service_time" label="Koha Minimale e Shërbimit / Hapi i Orareve (Minuta)" placeholder="Automatik nga shërbimet nëse lihet bosh" class="dark:bg-gray-900" /></div>
            </div>
            <div class="mt-10 flex justify-end">
                <x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl">{{ __('barber-shops.Save') }}</x-button>
            </div>
        </form>
    </div>
</div>
