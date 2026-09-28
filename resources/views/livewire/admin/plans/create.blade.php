<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1"><div><x-h1>{{ __('plans.Add Plan') }}</x-h1><x-short-description class="dark:text-gray-400">{{ __('plans.New record') }}</x-short-description></div><x-back-btn route="admin.plans.index" /></div>
    @include('errors.errors')
    
    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700"><form wire:submit.prevent="store" class="space-y-8 "><div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8"><div><x-form.input name="name" type="text" wire:model.live="name" :label="__('plans.Name')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="price" type="number" step="0.01" min="0" inputmode="decimal" oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*)\./g,'$1').replace(/(\.\d{2}).*/g,'$1')" wire:model.live="price" :label="__('plans.Price')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="duration_months" type="number" step="1" wire:model.live="duration_months" :label="__('plans.Duration Months')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="max_barbers" type="number" step="1" wire:model.live="max_barbers" :label="__('plans.Max Barbers')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="max_services" type="number" step="1" wire:model.live="max_services" :label="__('plans.Max Services')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="max_shops" type="number" step="1" wire:model.live="max_shops" :label="__('plans.Max Shops')" class="dark:bg-gray-900" /></div>
<div><x-form.checkbox name="active" wire:model="active" :label="__('plans.Active')" /></div></div><div class="mt-10 flex justify-end"><x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl">{{ __('plans.Save') }}</x-button></div></form></div>
</div>