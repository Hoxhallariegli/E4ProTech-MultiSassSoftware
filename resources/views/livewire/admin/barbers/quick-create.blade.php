<div class="p-6">
    @if($created)
        <div class="flex flex-col items-center text-center py-10">
            <div class="w-12 h-12 rounded-full bg-green-50 dark:bg-green-900/30 flex items-center justify-center mb-4">
                <x-heroicon-o-check class="w-6 h-6 text-green-500" />
            </div>
            <p class="font-bold text-gray-900 dark:text-white">{{ __('barbers.created') }}</p>
            @if($createdLabel)<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $createdLabel }}</p>@endif
            <button type="button" wire:click="addAnother" class="mt-6 text-xs font-black uppercase tracking-widest text-blue-600 dark:text-blue-400">{{ __('barbers.Add Barber') }}</button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8"><div>
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
<div><x-file-upload name="photo" wire:model="photo" :label="__('barbers.Photo')" id="photo" :isEditing="false" /></div>
<div class="md:col-span-2"><x-form.textarea name="bio" wire:model="bio" :label="__('barbers.Bio')" class="dark:bg-gray-900" /></div>
<div><x-form.checkbox name="active" wire:model="active" :label="__('barbers.Active')" /></div></div>
        <div class="mt-8 flex justify-end"><x-button wire:click="store" variant="blue">{{ __('barbers.Save') }}</x-button></div>
    @endif
</div>