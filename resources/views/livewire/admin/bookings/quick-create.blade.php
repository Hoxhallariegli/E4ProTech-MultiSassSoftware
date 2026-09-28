<div class="p-6">
    @if($created)
        <div class="flex flex-col items-center text-center py-10">
            <div class="w-12 h-12 rounded-full bg-green-50 dark:bg-green-900/30 flex items-center justify-center mb-4">
                <x-heroicon-o-check class="w-6 h-6 text-green-500" />
            </div>
            <p class="font-bold text-gray-900 dark:text-white">{{ __('bookings.created') }}</p>
            @if($createdLabel)<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $createdLabel }}</p>@endif
            <button type="button" wire:click="addAnother" class="mt-6 text-xs font-black uppercase tracking-widest text-blue-600 dark:text-blue-400">{{ __('bookings.Add Booking') }}</button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8"><div>
    @if(auth()->user()->barber_shop_id && !auth()->user()->is_global_admin)
        <x-form.input name="barber_shop_name" :label="__('bookings.Barber Shop Id')" value="{{ auth()->user()->barberShop->name }}" readonly disabled class="bg-gray-50" />
        <input type="hidden" wire:model="barber_shop_id">
    @else
        <div class="flex items-end gap-2">
            <div class="flex-1"><x-form.dropdown-search name="barber_shop_id" wire:model.live="barber_shop_id" :label="__('bookings.Barber Shop Id')" :data="$barberShops" /></div>
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
        <div class="flex-1"><x-form.dropdown-search name="barber_id" wire:model.live="barber_id" :label="__('bookings.Barber Id')" :data="$barbers" /></div>
        <x-modal>
            <x-slot name="trigger"><button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button></x-slot>
            <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Add New Barber</div></x-slot>
            <x-slot name="content"><livewire:admin.barbers.quick-create /></x-slot>
        </x-modal>
    </div>
</div>
<div>
    <div class="flex items-end gap-2">
        <div class="flex-1"><x-form.dropdown-search name="service_id" wire:model.live="service_id" :label="__('bookings.Service Id')" :data="$services" /></div>
        <x-modal>
            <x-slot name="trigger"><button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button></x-slot>
            <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Add New Service</div></x-slot>
            <x-slot name="content"><livewire:admin.services.quick-create /></x-slot>
        </x-modal>
    </div>
</div>
<div>
    <div class="flex items-end gap-2">
        <div class="flex-1"><x-form.dropdown-search name="customer_id" wire:model.live="customer_id" :label="__('bookings.Customer Id')" :data="$customers" /></div>
        <x-modal>
            <x-slot name="trigger"><button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button></x-slot>
            <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Add New Customer</div></x-slot>
            <x-slot name="content"><livewire:admin.customers.quick-create /></x-slot>
        </x-modal>
    </div>
</div>
<div><x-form.input name="appointment_at" type="datetime-local" wire:model.live="appointment_at" :label="__('bookings.Appointment At')" class="dark:bg-gray-900" /></div>
<div><label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest">{{ __('bookings.Status') }}</label><select name="status" wire:model="status" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl"><option value="">--</option><option value="pending">pending</option><option value="confirmed">confirmed</option><option value="completed">completed</option><option value="cancelled">cancelled</option><option value="no-show">no-show</option></select></div>
<div><x-form.input name="total_price" type="number" step="0.01" min="0" inputmode="decimal" oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*)\./g,'$1').replace(/(\.\d{2}).*/g,'$1')" wire:model.live="total_price" :label="__('bookings.Total Price')" class="dark:bg-gray-900" /></div>
<div class="md:col-span-2"><x-form.textarea name="notes" wire:model="notes" :label="__('bookings.Notes')" class="dark:bg-gray-900" /></div>
<div><label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest">{{ __('bookings.Source') }}</label><select name="source" wire:model="source" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl"><option value="">--</option><option value="online">online</option><option value="walk-in">walk-in</option><option value="phone">phone</option></select></div></div>
        <div class="mt-8 flex justify-end"><x-button wire:click="store" variant="blue">{{ __('bookings.Save') }}</x-button></div>
    @endif
</div>