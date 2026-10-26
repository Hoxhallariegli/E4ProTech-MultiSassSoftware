<div x-data="{ openFilter: @entangle('openFilter') }" class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div><x-h1>{{ __('payments.Payments') }}</x-h1><x-short-description class="dark:text-gray-400">{{ __('payments.description') }}</x-short-description></div>
        <div class="flex items-center gap-3">
            @if($search || $barber_shop_id || $booking_id || $barber_id || $payment_date || $openFilter)
                <button wire:click="resetFilters" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-2xl transition-none shadow-none"><span>{{ __('payments.Reset') }}</span></button>
            @endif
            <button @click="openFilter = !openFilter" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-extrabold text-gray-700 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xs transition-none"><span>{{ __('payments.Filters') }}</span></button>
            @can('add_payments')
                <x-btn :href="route('admin.payments.create')" icon="plus">{{ __('payments.Add Payment') }}</x-btn>
            @endcan
        </div>
    </div>

    <div x-show="openFilter" x-cloak class="p-6 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">{{ __('payments.Search') }}</label>
                <input name="search" wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('payments.search_placeholder') }}" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
            </div>

            <div>
                <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">{{ __('payments.Filter Date') }}</label>
                <input name="payment_date" wire:model.live="payment_date" type="date" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
            </div>

            <div>
                <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">{{ __('payments.Filter Staff') }}</label>
                <x-form.dropdown-search name="barber_id" wire:model.live="barber_id" label="none" :data="$barbers" placeholder="{{ __('payments.filter_barber_placeholder') }}" />
            </div>

            @if(auth()->user()->is_global_admin)
                <div><label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">{{ __('payments.Barber Shop Id') }}</label><x-form.dropdown-search name="barber_shop_id" wire:model.live="barber_shop_id" label="none" :data="$barberShops" placeholder="{{ __('payments.filter_shop_placeholder') }}" /></div>
            @endif
        </div>
    </div>

    @include('errors.messages')

    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 border border-gray-100 dark:border-gray-700 shadow-sm space-y-6">
        <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700/80">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-100/70 dark:bg-gray-700/70 hidden lg:table-header-group border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <x-table.th name="id" :label="__('payments.ID')" :$sortField :$sortAsc :sortable="true" />
                        @if(auth()->user()->is_global_admin)
                            <x-table.th name="barber_shop_id" :label="__('payments.Barber Shop Id')" :$sortField :$sortAsc :sortable="in_array('barber_shop_id', $sortableFields)" />
                        @endif
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('payments.Customer & Service') }}</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('payments.Staff / Barber') }}</th>
                        <x-table.th name="amount" :label="__('payments.Amount')" :$sortField :$sortAsc :sortable="in_array('amount', $sortableFields)" />
                        <x-table.th name="method" :label="__('payments.Method')" :$sortField :$sortAsc :sortable="in_array('method', $sortableFields)" />
                        <x-table.th name="created_at" :label="__('payments.Paid At')" :$sortField :$sortAsc :sortable="in_array('created_at', $sortableFields)" />
                        <x-table.th name="status" :label="__('payments.Status')" :$sortField :$sortAsc :sortable="in_array('status', $sortableFields)" />
                        <th class="px-6 py-4 text-right text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('payments.Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700/80">
                    @forelse($items as $item)
                        <livewire:admin.payments.row :$item :key="$item->id" />
                    @empty
                        <tr><td colspan="100" class="px-6 py-10 text-center text-sm text-gray-400">{{ __('payments.No records found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Table Summary Footer --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 bg-gray-50 dark:bg-gray-900/60 rounded-2xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300">
            <div class="flex items-center gap-4 flex-wrap">
                <span>{{ __('payments.Page Items') }}: <strong class="text-blue-600 dark:text-blue-400 font-mono">{{ $items->count() }} / {{ $totalCount }}</strong></span>
                <span class="text-gray-300 dark:text-gray-600">•</span>
                <span>{{ __('payments.Page Sum') }}: <strong class="text-emerald-600 dark:text-emerald-400 font-mono text-sm">{{ format_currency($pageSum, auth()->user()?->barberShop) }}</strong></span>
            </div>
            <div>
                <span>{{ __('payments.Total Collected') }}: <strong class="text-emerald-600 dark:text-emerald-400 font-mono text-sm underline font-black">{{ format_currency($totalSum, auth()->user()?->barberShop) }}</strong></span>
            </div>
        </div>

        <div class="mt-4">{{ $items->links() }}</div>
    </div>
</div>
