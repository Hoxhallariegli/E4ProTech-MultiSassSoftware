<div x-data="{ openFilter: @entangle('openFilter') }" class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <x-h1>{{ __('reports.Reports') }}</x-h1>
            <x-short-description class="dark:text-gray-400">
                {{ __('reports.Automated calculation') }} ({{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }})
            </x-short-description>
        </div>
        <div class="flex items-center gap-3">
            @if($search || $barber_id || $period_type !== 'daily' || $report_date !== date('Y-m-d') || $openFilter)
                <button wire:click="resetFilters" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-2xl transition-none shadow-none">
                    <span>{{ __('reports.Reset') }}</span>
                </button>
            @endif
            <button @click="openFilter = !openFilter" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-extrabold text-gray-700 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xs transition-none">
                <span>{{ __('reports.Filters') }}</span>
            </button>
        </div>
    </div>

    <!-- Filters Panel -->
    <div x-show="openFilter" x-cloak class="p-6 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">{{ __('reports.Period Type') }}</label>
                <select wire:model.live="period_type" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                    <option value="daily">{{ __('reports.Daily') }}</option>
                    <option value="weekly">{{ __('reports.Weekly') }}</option>
                    <option value="monthly">{{ __('reports.Monthly') }}</option>
                </select>
            </div>
            <div>
                <x-form.input name="report_date" type="date" wire:model.live="report_date" :label="__('reports.Report Date')" class="dark:bg-gray-800" />
            </div>
            <div>
                <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">{{ __('reports.Staff Member') }}</label>
                <x-form.dropdown-search name="barber_id" wire:model.live="barber_id" label="none" :data="$barbers" :placeholder="__('reports.All Staff')" />
            </div>
            @if(auth()->user()->is_global_admin)
                <div>
                    <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">{{ __('reports.Salon / Shop') }}</label>
                    <x-form.dropdown-search name="barber_shop_id" wire:model.live="barber_shop_id" label="none" :data="$barberShops" :placeholder="__('reports.All Shops')" />
                </div>
            @endif
        </div>
    </div>

    @include('errors.messages')

    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 border border-gray-100 dark:border-gray-700 shadow-sm space-y-6">
        <!-- 4 KPI Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Revenue Card -->
            <div class="p-5 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <x-heroicon-o-banknotes class="w-6 h-6" />
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase text-gray-400 tracking-widest">{{ __('reports.Total Revenue') }}</span>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-0.5">
                        {{ number_format((float)$totalRevenue, 2) }} €
                    </h3>
                </div>
            </div>

            <!-- Total Bookings Card -->
            <div class="p-5 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <x-heroicon-o-calendar class="w-6 h-6" />
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase text-gray-400 tracking-widest">{{ __('reports.Total Bookings') }}</span>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-0.5">
                        {{ $totalBookings }}
                    </h3>
                </div>
            </div>

            <!-- Completed Card -->
            <div class="p-5 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <x-heroicon-o-check-circle class="w-6 h-6" />
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase text-gray-400 tracking-widest">{{ __('reports.Total Completed') }}</span>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-0.5">
                        {{ $totalCompleted }}
                    </h3>
                </div>
            </div>

            <!-- Cancelled Card -->
            <div class="p-5 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-500 flex items-center justify-center shrink-0">
                    <x-heroicon-o-x-circle class="w-6 h-6" />
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase text-gray-400 tracking-widest">{{ __('reports.Total Cancelled') }}</span>
                    <h3 class="text-2xl font-black text-red-500 mt-0.5">
                        {{ $totalCancelled }}
                    </h3>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-4">
                <span class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">{{ __('reports.Period Bookings Details') }}</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('reports.Search by customer, service...') }}" class="w-64 p-2.5 text-xs font-bold bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl dark:text-white">
            </div>

            <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700/80">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-100/70 dark:bg-gray-700/70 hidden lg:table-header-group border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('reports.ID') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('reports.Time / Date') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('reports.Customer') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('reports.Service') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('reports.Staff') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('reports.Price') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('reports.Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700/80">
                        @forelse($bookings as $b)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-900/60 transition-colors border-b border-gray-200 dark:border-gray-700/80 last:border-none">
                                <td class="px-6 py-5 font-bold text-blue-600 dark:text-blue-400">#{{ $b->id }}</td>
                                <td class="px-6 py-5 font-bold text-gray-900 dark:text-white whitespace-nowrap font-mono text-xs">
                                    {{ $b->appointment_at?->format('d/m/Y H:i') ?? '-' }}
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <span class="font-extrabold text-xs text-gray-900 dark:text-white block">{{ $b->customer?->name ?? __('reports.Walk-in Customer') }}</span>
                                    <span class="text-[11px] text-gray-400 font-mono">{{ $b->customer?->phone }}</span>
                                </td>
                                <td class="px-6 py-5 font-extrabold text-xs text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                    {{ $b->service?->name ?? __('reports.Service') }}
                                </td>
                                <td class="px-6 py-5 font-extrabold text-xs text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                    {{ $b->barber?->name ?? __('reports.All Staff') }}
                                </td>
                                <td class="px-6 py-5 font-extrabold text-emerald-600 dark:text-emerald-400 font-mono whitespace-nowrap">
                                    {{ number_format($b->total_price, 2) }} €
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    @if($b->status === 'completed')
                                        <span class="px-2.5 py-0.5 text-[10px] font-black uppercase text-emerald-800 bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300 rounded-full">
                                            {{ __('reports.Completed') }}
                                        </span>
                                    @elseif($b->status === 'cancelled')
                                        <span class="px-2.5 py-0.5 text-[10px] font-black uppercase text-red-800 bg-red-100 dark:bg-red-900/40 dark:text-red-300 rounded-full">
                                            {{ __('reports.Cancelled') }}
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 text-[10px] font-black uppercase text-amber-800 bg-amber-100 dark:bg-amber-900/40 dark:text-amber-300 rounded-full">
                                            {{ ucfirst($b->status) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100" class="px-6 py-12 text-center text-sm text-gray-400 font-medium">
                                    {{ __('reports.No bookings found for selected period.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $bookings->links() }}</div>
        </div>
    </div>
</div>
