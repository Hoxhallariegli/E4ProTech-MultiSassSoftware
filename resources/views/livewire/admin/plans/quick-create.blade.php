<div class="p-6">
    @if($created)
        <div class="flex flex-col items-center text-center py-10">
            <div class="w-12 h-12 rounded-full bg-green-50 dark:bg-green-900/30 flex items-center justify-center mb-4">
                <x-heroicon-o-check class="w-6 h-6 text-green-500" />
            </div>
            <p class="font-bold text-gray-900 dark:text-white">{{ __('plans.created') }}</p>
            @if($createdLabel)<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $createdLabel }}</p>@endif
            <button type="button" wire:click="addAnother" class="mt-6 text-xs font-black uppercase tracking-widest text-blue-600 dark:text-blue-400">{{ __('plans.Add Plan') }}</button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8"><div><x-form.input name="name" type="text" wire:model.live="name" :label="__('plans.Name')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="price" type="number" step="0.01" min="0" inputmode="decimal" oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*)\./g,'$1').replace(/(\.\d{2}).*/g,'$1')" wire:model.live="price" :label="__('plans.Price')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="duration_months" type="number" step="1" wire:model.live="duration_months" :label="__('plans.Duration Months')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="max_barbers" type="number" step="1" wire:model.live="max_barbers" :label="__('plans.Max Barbers')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="max_services" type="number" step="1" wire:model.live="max_services" :label="__('plans.Max Services')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="max_shops" type="number" step="1" wire:model.live="max_shops" :label="__('plans.Max Shops')" class="dark:bg-gray-900" /></div>
<div><x-form.checkbox name="active" wire:model="active" :label="__('plans.Active')" /></div></div>
        <div class="mt-8 flex justify-end"><x-button wire:click="store" variant="blue">{{ __('plans.Save') }}</x-button></div>
    @endif
</div>