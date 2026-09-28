<div class="p-6">
    @if($created)
        <div class="flex flex-col items-center text-center py-10">
            <div class="w-12 h-12 rounded-full bg-green-50 dark:bg-green-900/30 flex items-center justify-center mb-4">
                <x-heroicon-o-check class="w-6 h-6 text-green-500" />
            </div>
            <p class="font-bold text-gray-900 dark:text-white">{{ __('barber-shops.created') }}</p>
            @if($createdLabel)<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $createdLabel }}</p>@endif
            <button type="button" wire:click="addAnother" class="mt-6 text-xs font-black uppercase tracking-widest text-blue-600 dark:text-blue-400">{{ __('barber-shops.Add BarberShop') }}</button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8"><div>
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
<div><x-form.input name="app_name" type="text" wire:model.live="app_name" :label="__('barber-shops.App Name')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="slug" type="text" wire:model.live="slug" :label="__('barber-shops.Slug')" class="dark:bg-gray-900" /></div>
<div><x-file-upload name="logo" wire:model="logo" :label="__('barber-shops.Logo')" id="logo" :isEditing="false" /></div>
<div><x-file-upload name="banner" wire:model="banner" :label="__('barber-shops.Banner')" id="banner" :isEditing="false" /></div>
<div><x-form.input name="primary_color" type="text" wire:model.live="primary_color" :label="__('barber-shops.Primary Color')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="secondary_color" type="text" wire:model.live="secondary_color" :label="__('barber-shops.Secondary Color')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="trial_ends_at" type="datetime-local" wire:model.live="trial_ends_at" :label="__('barber-shops.Trial Ends At')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="expires_at" type="datetime-local" wire:model.live="expires_at" :label="__('barber-shops.Expires At')" class="dark:bg-gray-900" /></div>
<div><x-form.checkbox name="active" wire:model="active" :label="__('barber-shops.Active')" /></div>
<div><x-form.checkbox name="sms_enabled" wire:model="sms_enabled" :label="__('barber-shops.Sms Enabled')" /></div>
<div><x-form.input name="timezone" type="text" wire:model.live="timezone" :label="__('barber-shops.Timezone')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="max_no_show_before_block" type="number" step="1" wire:model.live="max_no_show_before_block" :label="__('barber-shops.Max No Show Before Block')" class="dark:bg-gray-900" /></div></div>
        <div class="mt-8 flex justify-end"><x-button wire:click="store" variant="blue">{{ __('barber-shops.Save') }}</x-button></div>
    @endif
</div>