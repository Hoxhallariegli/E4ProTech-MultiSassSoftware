<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1"><div><x-h1>{{ __('shop-front-page-settings.Add ShopFrontPageSetting') }}</x-h1><x-short-description class="dark:text-gray-400">{{ __('shop-front-page-settings.New record') }}</x-short-description></div><x-back-btn route="admin.shop-front-page-settings.index" /></div>
    @include('errors.errors')
    
    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700"><form wire:submit.prevent="store" class="space-y-8 "><div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8"><div class="md:col-span-2"><x-form.textarea name="hero_title" wire:model="hero_title" :label="__('shop-front-page-settings.Hero Title')" class="dark:bg-gray-900" /></div>
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
</div></div><div class="mt-10 flex justify-end"><x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl">{{ __('shop-front-page-settings.Save') }}</x-button></div></form></div>
</div>