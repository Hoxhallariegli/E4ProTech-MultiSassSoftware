<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1"><div><x-h1>{{ __('barber-shops.Edit BarberShop') }}</x-h1><x-short-description class="dark:text-gray-400">{{ __('barber-shops.Update info') }}</x-short-description></div><x-back-btn route="admin.barber-shops.index" /></div>
    @include('errors.errors')
    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700">
        <form wire:submit.prevent="update" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                @if(auth()->check() && auth()->user()->is_global_admin)
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
                @endif
                <div><x-form.input name="name" type="text" wire:model.live="name" :label="__('barber-shops.Name')" class="dark:bg-gray-900" /></div>
                <div>
                    @php
                        $businessTypes = [
                            'barbershop' => __('barber-shops.Barbershop'),
                            'beauty_salon' => __('barber-shops.Beauty Salon'),
                            'nail_studio' => __('barber-shops.Nail Studio'),
                            'spa' => __('barber-shops.Aesthetic Center'),
                            'general' => __('barber-shops.General Business'),
                        ];
                    @endphp
                    <x-form.dropdown-search
                        name="business_type"
                        wire:model.live="business_type"
                        :label="__('barber-shops.Business Type')"
                        :data="$businessTypes"
                    />
                </div>
                <div><x-form.input name="app_name" type="text" wire:model.live="app_name" :label="__('barber-shops.App Name')" class="dark:bg-gray-900" /></div>
                <div><x-form.input name="slug" type="text" wire:model.live="slug" :label="__('barber-shops.Slug')" class="dark:bg-gray-900" /></div>
                <div><x-file-upload name="logo" wire:model="logo" :label="__('barber-shops.Logo')" id="logo" :isEditing="true" /></div>
                <div><x-file-upload name="banner" wire:model="banner" :label="__('barber-shops.Banner')" id="banner" :isEditing="true" /></div>

                <!-- WhatsApp Gateway Integration Card per Barber Shop -->
                <div class="col-span-1 md:col-span-2 bg-emerald-50/60 dark:bg-emerald-950/20 p-6 rounded-3xl border border-emerald-200 dark:border-emerald-800 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="font-black text-gray-900 dark:text-white text-base flex items-center gap-2">
                                <x-heroicon-o-chat-bubble-left-right class="w-5 h-5 text-emerald-500" />
                                <span>{{ __('barber-shops.WhatsApp Integration', ['name' => $name]) }}</span>
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('barber-shops.WhatsApp Subtitle') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="checkWhatsAppStatus" class="px-3.5 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl shadow-2xs hover:bg-gray-50">
                                {{ __('barber-shops.Refresh Status') }}
                            </button>
                            <button type="button" wire:click="generateWhatsAppQr" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl shadow-sm flex items-center gap-1.5">
                                <x-heroicon-o-qr-code class="w-4 h-4" />
                                <span>{{ __('barber-shops.Generate QR Code') }}</span>
                            </button>
                        </div>
                    </div>

                    @if($wa_connected)
                        <div class="p-4 bg-emerald-100 dark:bg-emerald-900/40 border border-emerald-300 dark:border-emerald-700 rounded-2xl flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-3.5 h-3.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                <div>
                                    <span class="text-xs font-black text-emerald-900 dark:text-emerald-200 uppercase tracking-wider block">{{ __('barber-shops.WhatsApp Connected') }}</span>
                                    @if($wa_phone)
                                        <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400 font-mono">{{ __('barber-shops.Connected Phone', ['phone' => $wa_phone]) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @elseif($wa_qr)
                        <div wire:poll.3s="checkWhatsAppStatus" class="p-6 bg-white dark:bg-gray-900 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-center space-y-4 shadow-sm">
                            <p class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-wider">{{ __('barber-shops.Scan QR Prompt') }}</p>
                            <div class="flex justify-center py-2">
                                @if(str_starts_with($wa_qr, 'data:image'))
                                    <img src="{{ $wa_qr }}" alt="WhatsApp QR Code" class="p-3 bg-white border-2 border-emerald-500/30 rounded-2xl shadow-md w-60 h-60" />
                                @else
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&data={{ urlencode($wa_qr) }}" alt="WhatsApp QR Code" class="p-3 bg-white border-2 border-emerald-500/30 rounded-2xl shadow-md w-60 h-60" />
                                @endif
                            </div>
                            <p class="text-[11px] font-semibold text-gray-400">{{ __('barber-shops.Scan Notice') }}</p>
                        </div>
                    @else
                        <div class="p-4 bg-white/80 dark:bg-gray-900/80 border border-gray-200 dark:border-gray-700 rounded-2xl text-xs font-bold text-gray-500 dark:text-gray-400">
                            {{ __('barber-shops.WhatsApp Disconnected') }}
                        </div>
                    @endif
                </div>

                <!-- Konfigurimi i Njoftimeve Automatike Card -->
                <div class="col-span-1 md:col-span-2 bg-slate-50 dark:bg-gray-900/60 p-6 rounded-3xl border border-gray-200 dark:border-gray-800 space-y-4">
                    <div>
                        <h3 class="font-black text-gray-900 dark:text-white text-sm uppercase tracking-wider">{{ __('barber-shops.Notification Channels') }}</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ __('barber-shops.Notification Channels Subtitle') }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-black text-gray-900 dark:text-white block">{{ __('barber-shops.Enable WhatsApp Notifications') }}</span>
                                <span class="text-[11px] text-gray-400">{{ __('barber-shops.Enable WhatsApp Subtitle') }}</span>
                            </div>
                            <x-form.checkbox name="whatsapp_enabled" wire:model="whatsapp_enabled" label="" />
                        </div>

                        <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-black text-gray-900 dark:text-white block">{{ __('barber-shops.Enable SMS Gateway') }}</span>
                                <span class="text-[11px] text-gray-400">{{ __('barber-shops.Enable SMS Subtitle') }}</span>
                            </div>
                            <x-form.checkbox name="sms_enabled" wire:model="sms_enabled" label="" />
                        </div>
                    </div>

                    <div class="pt-2">
                        @php
                            $reminderOptions = [
                                '0.5' => __('barber-shops.30 Minutes Before'),
                                '1' => __('barber-shops.1 Hour Before'),
                                '2' => __('barber-shops.2 Hours Before'),
                                '4' => __('barber-shops.4 Hours Before'),
                                '24' => __('barber-shops.24 Hours Before'),
                            ];
                        @endphp
                        <x-form.dropdown-search
                            name="reminder_hours_before"
                            wire:model.live="reminder_hours_before"
                            :label="__('barber-shops.Reminder Time Label')"
                            :data="$reminderOptions"
                        />
                    </div>
                </div>

                <!-- 10 Color Presets Card -->
                <div class="col-span-1 md:col-span-2 bg-slate-50 dark:bg-gray-900/60 p-6 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-4">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ __('barber-shops.Color Presets') }}</h3>
                        <p class="text-xs text-gray-500">{{ __('barber-shops.Color Presets Subtitle') }}</p>
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
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('barber-shops.Primary Hex') }}</label>
                            <div class="flex items-center gap-3">
                                <input type="color" wire:model.live="primary_color" class="w-12 h-12 p-1 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-2xl cursor-pointer shadow-sm shrink-0" />
                                <div class="flex-1">
                                    <x-form.input name="primary_color" type="text" wire:model.live="primary_color" label="" placeholder="#2563EB" class="dark:bg-gray-900 font-mono font-bold" />
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('barber-shops.Secondary Hex') }}</label>
                            <div class="flex items-center gap-3">
                                <input type="color" wire:model.live="secondary_color" class="w-12 h-12 p-1 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-2xl cursor-pointer shadow-sm shrink-0" />
                                <div class="flex-1">
                                    <x-form.input name="secondary_color" type="text" wire:model.live="secondary_color" label="" placeholder="#F59E0B" class="dark:bg-gray-900 font-mono font-bold" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-1 md:col-span-2 bg-gray-50 dark:bg-gray-900/50 p-6 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-4">
                    <h3 class="font-bold text-gray-800 dark:text-gray-200">{{ __('barber-shops.Custom Labels') }}</h3>
                    <p class="text-xs text-gray-500">{{ __('barber-shops.Custom Labels Subtitle') }}</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><x-form.input name="staff_label" type="text" wire:model.live="staff_label" :label="__('barber-shops.Staff Label Singular')" class="dark:bg-gray-900" /></div>
                        <div><x-form.input name="staff_label_plural" type="text" wire:model.live="staff_label_plural" :label="__('barber-shops.Staff Label Plural')" class="dark:bg-gray-900" /></div>
                        <div><x-form.input name="shop_label" type="text" wire:model.live="shop_label" :label="__('barber-shops.Shop Label')" class="dark:bg-gray-900" /></div>
                        <div><x-form.input name="service_label" type="text" wire:model.live="service_label" :label="__('barber-shops.Service Label')" class="dark:bg-gray-900" /></div>
                        <div><x-form.input name="currency" type="text" wire:model.live="currency" :label="__('barber-shops.Currency Official')" placeholder="Lekë" class="dark:bg-gray-900 font-bold" /></div>
                    </div>
                </div>

                @if(auth()->check() && auth()->user()->is_global_admin)
                    <div><x-form.input name="trial_ends_at" type="datetime-local" wire:model.live="trial_ends_at" :label="__('barber-shops.Trial Ends At')" class="dark:bg-gray-900" /></div>
                    <div><x-form.input name="expires_at" type="datetime-local" wire:model.live="expires_at" :label="__('barber-shops.Expires At')" class="dark:bg-gray-900" /></div>
                    <div><x-form.checkbox name="active" wire:model="active" :label="__('barber-shops.Active')" /></div>
                @else
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5">{{ __('barber-shops.Trial Ends At') }}</label>
                        <div class="px-4 py-3 bg-gray-100 dark:bg-gray-900 rounded-2xl text-xs font-bold font-mono text-gray-700 dark:text-gray-300">
                            {{ $trial_ends_at ? \Carbon\Carbon::parse($trial_ends_at)->format('d/m/Y H:i') : '-' }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5">{{ __('barber-shops.Expires At') }}</label>
                        <div class="px-4 py-3 bg-gray-100 dark:bg-gray-900 rounded-2xl text-xs font-bold font-mono text-gray-700 dark:text-gray-300">
                            {{ $expires_at ? \Carbon\Carbon::parse($expires_at)->format('d/m/Y H:i') : '-' }}
                        </div>
                    </div>
                @endif

                <div><x-form.input name="timezone" type="text" wire:model.live="timezone" :label="__('barber-shops.Timezone')" class="dark:bg-gray-900" /></div>
                <div><x-form.input name="max_no_show_before_block" type="number" step="1" wire:model.live="max_no_show_before_block" :label="__('barber-shops.Max No Show Before Block')" class="dark:bg-gray-900" /></div>
                <div><x-form.input name="min_service_time" type="number" step="1" wire:model.live="min_service_time" :label="__('barber-shops.Min Service Time')" :placeholder="__('barber-shops.Min Service Time Placeholder')" class="dark:bg-gray-900" /></div>
            </div>
            <div class="mt-10 flex justify-end">
                <x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl">{{ __('barber-shops.Update') }}</x-button>
            </div>
        </form>
    </div>
</div>
