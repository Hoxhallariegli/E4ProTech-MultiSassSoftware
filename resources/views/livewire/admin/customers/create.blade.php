<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1">
        <div>
            <x-h1>{{ __('customers.Add Customer') }}</x-h1>
            <x-short-description class="dark:text-gray-400">{{ __('customers.New record') }}</x-short-description>
        </div>
        <x-back-btn route="admin.customers.index" />
    </div>
    @include('errors.errors')

    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700">
        <form wire:submit.prevent="store" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                <div>
                    @if(auth()->user()->barber_shop_id && !auth()->user()->is_global_admin)
                        <x-form.input name="barber_shop_name" :label="__('customers.Barber Shop Id')" value="{{ auth()->user()->barberShop->name }}" readonly disabled class="bg-gray-50" />
                        <input type="hidden" wire:model="barber_shop_id">
                    @else
                        <div class="flex items-end gap-2">
                            <div class="flex-1"><x-form.dropdown-search name="barber_shop_id" wire:model.live="barber_shop_id" :label="__('customers.Barber Shop Id')" :data="$barberShops" /></div>
                            <x-modal>
                                <x-slot name="trigger"><button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button></x-slot>
                                <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Add New BarberShop</div></x-slot>
                                <x-slot name="content"><livewire:admin.barber-shops.quick-create /></x-slot>
                            </x-modal>
                        </div>
                    @endif
                </div>
                <div><x-form.input name="name" type="text" wire:model.live="name" :label="__('customers.Name')" class="dark:bg-gray-900" /></div>
                <div><x-form.input name="phone" type="text" wire:model.live="phone" :label="__('customers.Phone')" class="dark:bg-gray-900" /></div>
                <div><x-form.input name="email" type="text" wire:model.live="email" :label="__('customers.Email')" class="dark:bg-gray-900" /></div>
                <div><x-file-upload name="photo" wire:model="photo" :label="__('customers.Photo')" id="photo" :isEditing="false" /></div>

                <div>
                    <div class="space-y-1 mb-6">
                        <label class="block text-[10px] font-black text-gray-900 dark:text-gray-100 uppercase tracking-widest mb-1.5 ml-1">
                            {{ __('customers.block_customer') }}
                        </label>
                        <div class="flex items-center justify-between p-3.5 bg-gray-50/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ __('customers.blocked_from_online_bookings') }}</span>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" wire:model.live="is_blocked" class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:after:border-gray-600 peer-checked:bg-rose-600"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex justify-end">
                <x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl">{{ __('customers.Save') }}</x-button>
            </div>
        </form>
    </div>
</div>
