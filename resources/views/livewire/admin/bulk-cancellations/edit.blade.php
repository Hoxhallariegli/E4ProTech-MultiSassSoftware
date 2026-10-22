<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1"><div><x-h1>{{ __('bulk-cancellations.Edit BulkCancellation') }}</x-h1><x-short-description class="dark:text-gray-400">{{ __('bulk-cancellations.Update info') }}</x-short-description></div><x-back-btn route="admin.bulk-cancellations.index" /></div>
    @include('errors.errors')
    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700"><form wire:submit.prevent="update" class="space-y-8"><div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8"><div>
    @if(auth()->user()->barber_shop_id && !auth()->user()->is_global_admin)
        <x-form.input name="barber_shop_name" :label="__('bulk-cancellations.Barber Shop Id')" value="{{ auth()->user()->barberShop->name }}" readonly disabled class="bg-gray-50" />
        <input type="hidden" wire:model="barber_shop_id">
    @else
        <div class="flex items-end gap-2">
            <div class="flex-1"><x-form.dropdown-search name="barber_shop_id" wire:model.live="barber_shop_id" :label="__('bulk-cancellations.Barber Shop Id')" :data="$barberShops" /></div>
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
        <div class="flex-1"><x-form.dropdown-search name="barber_id" wire:model.live="barber_id" :label="__('bulk-cancellations.Barber Id')" :data="$barbers" /></div>
        <x-modal>
            <x-slot name="trigger"><button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button></x-slot>
            <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Add New Barber</div></x-slot>
            <x-slot name="content"><livewire:admin.barbers.quick-create /></x-slot>
        </x-modal>
    </div>
</div>
<div><x-form.input name="start_at" type="datetime-local" wire:model.live="start_at" :label="__('bulk-cancellations.Start At')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="end_at" type="datetime-local" wire:model.live="end_at" :label="__('bulk-cancellations.End At')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="reason" type="text" wire:model.live="reason" :label="__('bulk-cancellations.Reason')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="affected_count" type="number" step="1" wire:model.live="affected_count" :label="__('bulk-cancellations.Affected Count')" class="dark:bg-gray-900" /></div></div><div class="mt-10 flex justify-end"><x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl">{{ __('bulk-cancellations.Update') }}</x-button></div></form></div>
</div>