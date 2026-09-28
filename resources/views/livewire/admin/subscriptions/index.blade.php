<div x-data="{ openFilter: @entangle('openFilter') }">
    <div class="card !p-0 overflow-hidden shadow-none border-gray-200 dark:border-gray-700 dark:bg-gray-800">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div><x-h1>{{ __('subscriptions.Subscriptions') }}</x-h1><x-short-description class="dark:text-gray-400">{{ __('subscriptions.List of') }} subscriptions</x-short-description></div>
                <div class="flex items-center gap-3">
                    @if($search || $openFilter)
                        <button wire:click="resetFilters" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-2xl transition-none shadow-none"><span>{{ __('subscriptions.Reset') }}</span></button>
                    @endif
                    <button @click="openFilter = !openFilter" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-600 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-sm transition-none"><span>{{ __('subscriptions.Filters') }}</span></button>
                    @can('add_subscriptions')
                        <x-btn :href="route('admin.subscriptions.create')" icon="plus">{{ __('subscriptions.Add Subscription') }}</x-btn>
                    @endcan
                </div>
            </div>

            <div x-show="openFilter" x-cloak class="mt-6 p-6 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 rounded-2xl">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">{{ __('subscriptions.Search') }}</label>
                        <input name="search" wire:model.live.debounce.300ms="search" type="text" placeholder="Search by ID" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                    </div>
                    @if(auth()->user()->is_global_admin)
<div><label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">Barber Shop Id</label><x-form.dropdown-search name="barber_shop_id" wire:model.live="barber_shop_id" label="none" :data="$barberShops" placeholder="Filter Barber Shop Id" /></div>
@endif
<div><label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">Plan Id</label><x-form.dropdown-search name="plan_id" wire:model.live="plan_id" label="none" :data="$plans" placeholder="Filter Plan Id" /></div>
                </div>
            </div>
        </div>

        @include('errors.messages')

        <div class="overflow-x-auto border-t border-gray-100 dark:border-gray-700">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-100/50 dark:bg-gray-700/50"><tr><x-table.th name="id" :label="__('subscriptions.ID')" :$sortField :$sortAsc :sortable="true" />@if(auth()->user()->is_global_admin)
<x-table.th name="barber_shop_id" :label="__('subscriptions.Barber Shop Id')" :$sortField :$sortAsc :sortable="in_array('barber_shop_id', $sortableFields)" />
@endif
<x-table.th name="plan_id" :label="__('subscriptions.Plan Id')" :$sortField :$sortAsc :sortable="in_array('plan_id', $sortableFields)" />
<x-table.th name="starts_at" :label="__('subscriptions.Starts At')" :$sortField :$sortAsc :sortable="in_array('starts_at', $sortableFields)" />
<x-table.th name="ends_at" :label="__('subscriptions.Ends At')" :$sortField :$sortAsc :sortable="in_array('ends_at', $sortableFields)" />
<x-table.th name="status" :label="__('subscriptions.Status')" :$sortField :$sortAsc :sortable="in_array('status', $sortableFields)" />
<x-table.th name="auto_renew" :label="__('subscriptions.Auto Renew')" :$sortField :$sortAsc :sortable="in_array('auto_renew', $sortableFields)" /><th class="px-6 py-4 text-right text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('subscriptions.Action') }}</th></tr></thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">@forelse($items as $item) <livewire:admin.subscriptions.row :$item :key="$item->id" /> @empty <tr><td colspan="100" class="px-6 py-10 text-center text-sm text-gray-400">{{ __('subscriptions.No records found.') }}</td></tr> @endforelse</tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-50 dark:border-gray-700/50">{{ $items->links() }}</div>
    </div>
</div>