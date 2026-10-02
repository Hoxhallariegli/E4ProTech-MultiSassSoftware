<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1">
        <div>
            <x-h1>{{ __('message-templates.Add MessageTemplate') }}</x-h1>
            <x-short-description class="dark:text-gray-400">{{ __('message-templates.New record') }}</x-short-description>
        </div>
        <x-back-btn route="admin.message-templates.index" />
    </div>

    @include('errors.errors')

    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700">
        <form wire:submit.prevent="store" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                <div>
                    @if(auth()->user()->barber_shop_id && !auth()->user()->is_global_admin)
                        <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-500">Salloni / Barber Shop</label>
                        <div class="p-3 text-sm font-bold bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl text-gray-800 dark:text-gray-200">
                            {{ auth()->user()->barberShop?->name ?? 'Salloni Juaj' }}
                        </div>
                    @else
                        <div class="flex items-end gap-2">
                            <div class="flex-1">
                                <x-form.dropdown-search name="barber_shop_id" wire:model.live="barber_shop_id" :label="__('message-templates.Barber Shop Id')" :data="$barberShops" />
                            </div>
                            <x-modal>
                                <x-slot name="trigger">
                                    <button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform">
                                        <x-heroicon-o-plus class="w-5 h-5" />
                                    </button>
                                </x-slot>
                                <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Add New BarberShop</div></x-slot>
                                <x-slot name="content"><livewire:admin.barber-shops.quick-create /></x-slot>
                            </x-modal>
                        </div>
                    @endif
                </div>

                <div>
                    <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-500">{{ __('message-templates.Channel') }}</label>
                    <select name="channel" wire:model="channel" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl">
                        <option value="sms">SMS</option>
                        <option value="whatsapp">WhatsApp</option>
                    </select>
                </div>

                <div>
                    <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-500">{{ __('message-templates.Type') }}</label>
                    <select name="type" wire:model="type" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl">
                        <option value="confirmation">Konfirmim Rezervimi (Confirmation)</option>
                        <option value="reminder">Kujtesë Takimi (Reminder)</option>
                        <option value="welcome">Mirëseardhje (Welcome)</option>
                    </select>
                </div>

                <div class="md:col-span-2 space-y-6">
                    <div>
                        <x-form.textarea name="content_sq" wire:model="content_sq" label="🇦🇱 Përmbajtja në Shqip (SQ)" placeholder="Përdorni variablat: {customer_name}, {service_name}, {staff_name}, {shop_name}, {time}, {date}" class="dark:bg-gray-900 font-mono text-sm" />
                    </div>

                    <div>
                        <x-form.textarea name="content_en" wire:model="content_en" label="🇬🇧 Content in English (EN)" placeholder="Use variables: {customer_name}, {service_name}, {staff_name}, {shop_name}, {time}, {date}" class="dark:bg-gray-900 font-mono text-sm" />
                    </div>

                    <p class="text-xs text-gray-500 mt-2">Variablat e lejuara: <code class="bg-gray-100 dark:bg-gray-900 px-1.5 py-0.5 rounded">{customer_name}</code>, <code class="bg-gray-100 dark:bg-gray-900 px-1.5 py-0.5 rounded">{service_name}</code>, <code class="bg-gray-100 dark:bg-gray-900 px-1.5 py-0.5 rounded">{staff_name}</code>, <code class="bg-gray-100 dark:bg-gray-900 px-1.5 py-0.5 rounded">{shop_name}</code>, <code class="bg-gray-100 dark:bg-gray-900 px-1.5 py-0.5 rounded">{time}</code>, <code class="bg-gray-100 dark:bg-gray-900 px-1.5 py-0.5 rounded">{date}</code></p>
                </div>
            </div>

            <div class="mt-10 flex justify-end">
                <x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl">{{ __('message-templates.Save') }}</x-button>
            </div>
        </form>
    </div>
</div>
