<div class="p-6">
    @if($created)
        <div class="flex flex-col items-center text-center py-10">
            <div class="w-12 h-12 rounded-full bg-green-50 dark:bg-green-900/30 flex items-center justify-center mb-4">
                <x-heroicon-o-check class="w-6 h-6 text-green-500" />
            </div>
            <p class="font-bold text-gray-900 dark:text-white">{{ __('shop-front-page-settings.created') }}</p>
            @if($createdLabel)<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $createdLabel }}</p>@endif
            <button type="button" wire:click="addAnother" class="mt-6 text-xs font-black uppercase tracking-widest text-blue-600 dark:text-blue-400">{{ __('shop-front-page-settings.Add ShopFrontPageSetting') }}</button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8"><div class="md:col-span-2"><x-form.textarea name="hero_title" wire:model="hero_title" :label="__('shop-front-page-settings.Hero Title')" class="dark:bg-gray-900" /></div>
<div class="md:col-span-2"><x-form.textarea name="hero_subtitle" wire:model="hero_subtitle" :label="__('shop-front-page-settings.Hero Subtitle')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="hero_button_text" type="text" wire:model.live="hero_button_text" :label="__('shop-front-page-settings.Hero Button Text')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="services_badge_text" type="text" wire:model.live="services_badge_text" :label="__('shop-front-page-settings.Services Badge Text')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="services_title" type="text" wire:model.live="services_title" :label="__('shop-front-page-settings.Services Title')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="staff_badge_text" type="text" wire:model.live="staff_badge_text" :label="__('shop-front-page-settings.Staff Badge Text')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="staff_title" type="text" wire:model.live="staff_title" :label="__('shop-front-page-settings.Staff Title')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="contact_phone" type="text" wire:model.live="contact_phone" :label="__('shop-front-page-settings.Contact Phone')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="contact_email" type="text" wire:model.live="contact_email" :label="__('shop-front-page-settings.Contact Email')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="contact_address" type="text" wire:model.live="contact_address" :label="__('shop-front-page-settings.Contact Address')" class="dark:bg-gray-900" /></div>
<div class="md:col-span-2"><x-form.textarea name="google_maps_url" wire:model="google_maps_url" :label="__('shop-front-page-settings.Google Maps Url')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="footer_text" type="text" wire:model.live="footer_text" :label="__('shop-front-page-settings.Footer Text')" class="dark:bg-gray-900" /></div>
<div>
    @if(auth()->user()->barber_shop_id && !auth()->user()->is_global_admin)
        <x-form.input name="barber_shop_name" :label="__('shop-front-page-settings.Barber Shop Id')" value="{{ auth()->user()->barberShop->name }}" readonly disabled class="bg-gray-50" />
        <input type="hidden" wire:model="barber_shop_id">
    @else
        <div class="flex items-end gap-2">
            <div class="flex-1"><x-form.dropdown-search name="barber_shop_id" wire:model.live="barber_shop_id" :label="__('shop-front-page-settings.Barber Shop Id')" :data="$barberShops" /></div>
            <x-modal>
                <x-slot name="trigger"><button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button></x-slot>
                <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Add New BarberShop</div></x-slot>
                <x-slot name="content"><livewire:admin.barber-shops.quick-create /></x-slot>
            </x-modal>
        </div>
    @endif
</div></div>
        <div class="mt-8 flex justify-end"><x-button wire:click="store" variant="blue">{{ __('shop-front-page-settings.Save') }}</x-button></div>
    @endif
</div>