<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1"><div><x-h1>{{ __('barbers.Edit Barber') }}</x-h1><x-short-description class="dark:text-gray-400">{{ __('barbers.Update info') }}</x-short-description></div><x-back-btn route="admin.barbers.index" /></div>
    @include('errors.errors')
    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700">
        <form wire:submit.prevent="update" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                <div>
                    @if(auth()->user()->barber_shop_id && !auth()->user()->is_global_admin)
                        <x-form.input name="barber_shop_name" :label="__('barbers.Barber Shop Id')" value="{{ auth()->user()->barberShop->name }}" readonly disabled class="bg-gray-50" />
                        <input type="hidden" wire:model="barber_shop_id">
                    @else
                        <div class="flex items-end gap-2">
                            <div class="flex-1"><x-form.dropdown-search name="barber_shop_id" wire:model.live="barber_shop_id" :label="__('barbers.Barber Shop Id')" :data="$barberShops" /></div>
                            <x-modal>
                                <x-slot name="trigger"><button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button></x-slot>
                                <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Add New BarberShop</div></x-slot>
                                <x-slot name="content"><livewire:admin.barber-shops.quick-create /></x-slot>
                            </x-modal>
                        </div>
                    @endif
                </div>

                <div>
                    <div class="flex items-end gap-2">
                        <div class="flex-1"><x-form.dropdown-search name="user_id" wire:model.live="user_id" :label="__('barbers.User Id')" :data="$users" /></div>
                        <x-modal>
                            <x-slot name="trigger"><button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button></x-slot>
                            <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Add New User</div></x-slot>
                            <x-slot name="content"><livewire:admin.users.quick-create /></x-slot>
                        </x-modal>
                    </div>
                </div>

                <div><x-form.input name="name" type="text" wire:model.live="name" :label="__('barbers.Name')" class="dark:bg-gray-900" /></div>
                <div><x-form.input name="phone" type="text" wire:model.live="phone" :label="__('barbers.Phone')" class="dark:bg-gray-900" /></div>
                <div><x-file-upload name="photo" wire:model="photo" :label="__('barbers.Photo')" id="photo" :isEditing="true" /></div>
                <div class="md:col-span-2"><x-form.textarea name="bio" wire:model="bio" :label="__('barbers.Bio')" class="dark:bg-gray-900" /></div>

                <!-- WhatsApp Gateway Integration Card per Barber -->
                <div class="col-span-1 md:col-span-2 bg-emerald-50/60 dark:bg-emerald-950/20 p-6 rounded-3xl border border-emerald-200 dark:border-emerald-800 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="font-black text-gray-900 dark:text-white text-base flex items-center gap-2">
                                <x-heroicon-o-chat-bubble-left-right class="w-5 h-5 text-emerald-500" />
                                <span>Lidhja e WhatsApp-it Personal për Berberin: {{ $name }}</span>
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Skanoni QR Code-in me WhatsApp-in personal të këtij berberi në mënyrë që njoftimet t'u dërgohen klientëve direkt nga numri i tij!</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="checkWhatsAppStatus" class="px-3.5 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl shadow-2xs hover:bg-gray-50">
                                🔄 Rifresko Statusin
                            </button>
                            <button type="button" wire:click="generateWhatsAppQr" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl shadow-sm flex items-center gap-1.5">
                                <x-heroicon-o-qr-code class="w-4 h-4" />
                                <span>📱 Gjenero QR Code</span>
                            </button>
                        </div>
                    </div>

                    @if($wa_connected)
                        <div class="p-4 bg-emerald-100 dark:bg-emerald-900/40 border border-emerald-300 dark:border-emerald-700 rounded-2xl flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-3.5 h-3.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                <div>
                                    <span class="text-xs font-black text-emerald-900 dark:text-emerald-200 uppercase tracking-wider block">WhatsApp i këtij Berberi është 100% i Lidhur e Aktiv!</span>
                                    @if($wa_phone)
                                        <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400 font-mono">Numri i Lidhur: +{{ $wa_phone }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @elseif($wa_qr)
                        <div wire:poll.3s="checkWhatsAppStatus" class="p-6 bg-white dark:bg-gray-900 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-center space-y-4 shadow-sm">
                            <p class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-wider">Hapni WhatsApp te telefoni i {{ $name }} ➔ Linked Devices ➔ Link a Device dhe Skanoni këtë QR Code:</p>
                            <div class="flex justify-center py-2">
                                @if(str_starts_with($wa_qr, 'data:image'))
                                    <img src="{{ $wa_qr }}" alt="WhatsApp QR Code" class="p-3 bg-white border-2 border-emerald-500/30 rounded-2xl shadow-md w-60 h-60" />
                                @else
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&data={{ urlencode($wa_qr) }}" alt="WhatsApp QR Code" class="p-3 bg-white border-2 border-emerald-500/30 rounded-2xl shadow-md w-60 h-60" />
                                @endif
                            </div>
                            <p class="text-[11px] font-semibold text-gray-400">Skanoni foton me WhatsApp. Sapo ta skanoni, faqja e detekton automatikisht lidhjen!</p>
                        </div>
                    @else
                        <div class="p-4 bg-white/80 dark:bg-gray-900/80 border border-gray-200 dark:border-gray-700 rounded-2xl text-xs font-bold text-gray-500 dark:text-gray-400">
                            Statusi: Pa lidhur. Klikoni butonin <strong class="text-emerald-600 dark:text-emerald-400">"📱 Gjenero QR Code"</strong> më sipër për të lidhur WhatsApp-in e këtij berberi!
                        </div>
                    @endif
                </div>

                <div><x-form.checkbox name="active" wire:model="active" :label="__('barbers.Active')" /></div>
                <div><x-form.checkbox name="whatsapp_enabled" wire:model="whatsapp_enabled" label="Dërgo Njoftime te Klientët në WhatsApp" /></div>
            </div>

            <div class="mt-10 flex justify-end">
                <x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl">{{ __('barbers.Update') }}</x-button>
            </div>
        </form>
    </div>
</div>
