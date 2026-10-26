<div x-data="{ openFilter: @entangle('openFilter') }" class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <x-h1>{{ __('bookings.Bookings') }}</x-h1>
            <x-short-description class="dark:text-gray-400">{{ __('bookings.description') }}</x-short-description>
        </div>
        <div class="flex items-center gap-3">
            @if($search || $barber_shop_id || $barber_id || $service_id || $customer_id || $openFilter)
                <button wire:click="resetFilters" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-2xl transition-none shadow-none"><span>{{ __('bookings.Reset') }}</span></button>
            @endif
            <button @click="openFilter = !openFilter" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-extrabold text-gray-700 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xs transition-none"><span>{{ __('bookings.Filters') }}</span></button>
            @can('add_bookings')
                <x-btn :href="route('admin.bookings.create')" icon="plus">{{ __('bookings.Add Booking') }}</x-btn>
            @endcan
        </div>
    </div>

    <!-- Today's Live Stats Banner (Brand Oriented & Minimalist) -->
    @can('view_bookings')
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <!-- 1. Today Total Bookings -->
            <div class="p-4 sm:p-5 bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 rounded-[2rem] shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">{{ __('bookings.Today Bookings') }}</p>
                    <p class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white font-mono mt-1">
                        {{ $todayBookingsCount }}
                    </p>
                </div>
                <div class="p-3 bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-200 rounded-2xl shrink-0">
                    <x-heroicon-o-calendar-days class="w-5 h-5 sm:w-6 sm:h-6" />
                </div>
            </div>

            <!-- 2. Today Completed & Paid -->
            <div class="p-4 sm:p-5 bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 rounded-[2rem] shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">{{ __('bookings.Completed & Paid') }}</p>
                    <p class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white font-mono mt-1">
                        {{ $todayCompletedCount }}
                    </p>
                </div>
                <div class="p-3 bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-200 rounded-2xl shrink-0">
                    <x-heroicon-o-check-circle class="w-5 h-5 sm:w-6 sm:h-6" />
                </div>
            </div>

            <!-- 3. Today Pending / Upcoming -->
            <div class="p-4 sm:p-5 bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 rounded-[2rem] shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">{{ __('bookings.Pending & Confirmed') }}</p>
                    <p class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white font-mono mt-1">
                        {{ $todayPendingCount }}
                    </p>
                </div>
                <div class="p-3 bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-200 rounded-2xl shrink-0">
                    <x-heroicon-o-clock class="w-5 h-5 sm:w-6 sm:h-6" />
                </div>
            </div>

            <!-- 4. Today Revenue -->
            <div class="p-4 sm:p-5 bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 rounded-[2rem] shadow-2xs flex items-center justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">{{ __('bookings.Today Revenue') }}</p>
                    <p class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono mt-1">
                        {{ format_currency($todayRevenue, auth()->user()?->barberShop) }}
                    </p>
                </div>
                <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-2xl shrink-0">
                    <x-heroicon-o-banknotes class="w-5 h-5 sm:w-6 sm:h-6" />
                </div>
            </div>
        </div>
    @endcan

    <div x-show="openFilter" x-cloak class="p-6 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">{{ __('bookings.Search') }}</label>
                <input name="search" wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('bookings.search_placeholder') }}" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
            </div>
            @if(auth()->user()->is_global_admin)
                <div><label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">{{ __('bookings.Barber Shop Id') }}</label><x-form.dropdown-search name="barber_shop_id" wire:model.live="barber_shop_id" label="none" :data="$barberShops" placeholder="{{ __('bookings.filter_shop_placeholder') }}" /></div>
            @endif
            <div><label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">{{ __('bookings.Barber Id') }}</label><x-form.dropdown-search name="barber_id" wire:model.live="barber_id" label="none" :data="$barbers" placeholder="{{ __('bookings.filter_barber_placeholder') }}" /></div>
            <div><label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">{{ __('bookings.Service Id') }}</label><x-form.dropdown-search name="service_id" wire:model.live="service_id" label="none" :data="$services" placeholder="{{ __('bookings.filter_service_placeholder') }}" /></div>
            <div><label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">{{ __('bookings.Customer Id') }}</label><x-form.dropdown-search name="customer_id" wire:model.live="customer_id" label="none" :data="$customers" placeholder="{{ __('bookings.filter_customer_placeholder') }}" /></div>
        </div>
    </div>

    @include('errors.messages')

    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 border border-gray-100 dark:border-gray-700 shadow-sm space-y-6">
        <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700/80">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-100/70 dark:bg-gray-700/70 hidden lg:table-header-group border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <x-table.th name="barber_id" :label="__('bookings.Barber Id')" :$sortField :$sortAsc :sortable="in_array('barber_id', $sortableFields)" />
                        <x-table.th name="service_id" :label="__('bookings.Service Id')" :$sortField :$sortAsc :sortable="in_array('service_id', $sortableFields)" />
                        <x-table.th name="customer_id" :label="__('bookings.Customer Id')" :$sortField :$sortAsc :sortable="in_array('customer_id', $sortableFields)" />
                        <x-table.th name="appointment_at" :label="__('bookings.Appointment At')" :$sortField :$sortAsc :sortable="in_array('appointment_at', $sortableFields)" />
                        <x-table.th name="status" :label="__('bookings.Status')" :$sortField :$sortAsc :sortable="in_array('status', $sortableFields)" />
                        <x-table.th name="total_price" :label="__('bookings.Total Price')" :$sortField :$sortAsc :sortable="in_array('total_price', $sortableFields)" />
                        <x-table.th name="notes" :label="__('bookings.Notes')" :$sortField :$sortAsc :sortable="in_array('notes', $sortableFields)" />
                        <x-table.th name="source" :label="__('bookings.Source')" :$sortField :$sortAsc :sortable="in_array('source', $sortableFields)" />
                        <th class="px-6 py-4 text-right text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('bookings.Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700/80">
                    @forelse($items as $item)
                        <livewire:admin.bookings.row :$item :key="$item->id" />
                    @empty
                        <tr><td colspan="100" class="px-6 py-10 text-center text-sm text-gray-400">{{ __('bookings.No records found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $items->links() }}</div>
    </div>
</div>
