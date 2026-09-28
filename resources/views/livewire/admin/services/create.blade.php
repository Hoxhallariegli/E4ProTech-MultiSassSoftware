<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1"><div><x-h1>{{ __('services.Add Service') }}</x-h1><x-short-description class="dark:text-gray-400">{{ __('services.New record') }}</x-short-description></div><x-back-btn route="admin.services.index" /></div>
    @include('errors.errors')
        @if($limitReached ?? false)
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-6 py-4 rounded-3xl flex items-center gap-4 mb-6">
            <x-heroicon-o-exclamation-triangle class="w-6 h-6 shrink-0" />
            <div>
                <p class="font-bold">{{ __('Limit i arritur!') }}</p>
                <p class="text-sm opacity-90">{{ __('Keni arritur numrin maksimal p\u00ebr k\u00ebt\u00eb plan.') }}</p>
            </div>
            <a href="{{ route('admin.subscriptions.index') }}" class="ml-auto bg-red-600 text-white px-4 py-2 rounded-xl text-xs font-bold uppercase hover:bg-red-700 transition-colors">
                {{ __('P\u00ebrmir\u00ebso Planin') }}
            </a>
        </div>
    @endif
    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700"><form wire:submit.prevent="store" class="space-y-8 {{ ($limitReached ?? false) ? "opacity-50 pointer-events-none" : "" }}"><div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8"><div>
    @if(auth()->user()->barber_shop_id && !auth()->user()->is_global_admin)
        <x-form.input name="barber_shop_name" :label="__('services.Barber Shop Id')" value="{{ auth()->user()->barberShop->name }}" readonly disabled class="bg-gray-50" />
        <input type="hidden" wire:model="barber_shop_id">
    @else
        <div class="flex items-end gap-2">
            <div class="flex-1"><x-form.dropdown-search name="barber_shop_id" wire:model.live="barber_shop_id" :label="__('services.Barber Shop Id')" :data="$barberShops" /></div>
            <x-modal>
                <x-slot name="trigger"><button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button></x-slot>
                <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Add New BarberShop</div></x-slot>
                <x-slot name="content"><livewire:admin.barber-shops.quick-create /></x-slot>
            </x-modal>
        </div>
    @endif
</div>
<div><x-form.input name="name" type="text" wire:model.live="name" :label="__('services.Name')" class="dark:bg-gray-900" /></div>
<div class="md:col-span-2"><x-form.textarea name="description" wire:model="description" :label="__('services.Description')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="price" type="number" step="0.01" min="0" inputmode="decimal" oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*)\./g,'$1').replace(/(\.\d{2}).*/g,'$1')" wire:model.live="price" :label="__('services.Price')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="duration_minutes" type="number" step="1" wire:model.live="duration_minutes" :label="__('services.Duration Minutes')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="category" type="text" wire:model.live="category" :label="__('services.Category')" class="dark:bg-gray-900" /></div>
<div><x-form.checkbox name="active" wire:model="active" :label="__('services.Active')" /></div></div><div class="mt-10 flex justify-end"><x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl">{{ __('services.Save') }}</x-button></div></form></div>
</div>