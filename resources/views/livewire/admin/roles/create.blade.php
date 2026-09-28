<x-modal>
    <x-slot name="trigger">
        <x-button @click="on = true">{{ __('Add Role') }}</x-button>
    </x-slot>

    <x-slot name="modalTitle">{{ __('Add Role') }}</x-slot>

    <x-slot name="content">

        @include('errors.success')

        <div class="space-y-4">
            <x-form.input autofocus wire:model="label" :label="__('Role Name')" name="label" required />

            @if(auth()->user()->is_global_admin)
                <x-form.select wire:model="barber_shop_id" :label="__('Assign to Team')" name="barber_shop_id">
                    <option value="0">🌍 {{ __('GLOBAL (Template Role)') }}</option>
                    @foreach($allShops as $shop)
                        <option value="{{ $shop->id }}">🏪 {{ $shop->name }}</option>
                    @endforeach
                </x-form.select>
                <p class="text-[10px] text-gray-500 italic">* {{ __('Global roles are visible and usable by all barber shops.') }}</p>
            @endif
        </div>

    </x-slot>

    <x-slot name="footer">
        <x-button variant="gray" @click="on = false">{{ __('Close') }}</x-button>
        <x-button wire:click="store">{{ __('Create Role') }}</x-button>
    </x-slot>

</x-modal>
