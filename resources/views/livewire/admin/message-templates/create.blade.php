<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1"><div><x-h1>{{ __('message-templates.Add MessageTemplate') }}</x-h1><x-short-description class="dark:text-gray-400">{{ __('message-templates.New record') }}</x-short-description></div><x-back-btn route="admin.message-templates.index" /></div>
    @include('errors.errors')
    
    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700"><form wire:submit.prevent="store" class="space-y-8 "><div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8"><div>
    @if(auth()->user()->barber_shop_id && !auth()->user()->is_global_admin)
        <x-form.input name="barber_shop_name" :label="__('message-templates.Barber Shop Id')" value="{{ auth()->user()->barberShop->name }}" readonly disabled class="bg-gray-50" />
        <input type="hidden" wire:model="barber_shop_id">
    @else
        <div class="flex items-end gap-2">
            <div class="flex-1"><x-form.dropdown-search name="barber_shop_id" wire:model.live="barber_shop_id" :label="__('message-templates.Barber Shop Id')" :data="$barberShops" /></div>
            <x-modal>
                <x-slot name="trigger"><button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button></x-slot>
                <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Add New BarberShop</div></x-slot>
                <x-slot name="content"><livewire:admin.barber-shops.quick-create /></x-slot>
            </x-modal>
        </div>
    @endif
</div>
<div><label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest">{{ __('message-templates.Channel') }}</label><select name="channel" wire:model="channel" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl"><option value="">--</option><option value="sms">sms</option><option value="whatsapp">whatsapp</option></select></div>
<div><label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest">{{ __('message-templates.Type') }}</label><select name="type" wire:model="type" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl"><option value="">--</option><option value="reminder">reminder</option><option value="confirmation">confirmation</option><option value="welcome">welcome</option></select></div>
<div class="md:col-span-2"><x-form.textarea name="content" wire:model="content" :label="__('message-templates.Content')" class="dark:bg-gray-900" /></div></div><div class="mt-10 flex justify-end"><x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl">{{ __('message-templates.Save') }}</x-button></div></form></div>
</div>