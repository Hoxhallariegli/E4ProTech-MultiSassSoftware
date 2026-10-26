<div x-data="{ openFilter: @entangle('openFilter') }" class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <x-h1>{{ __('reports.Reports & Analytics') }}</x-h1>
            <x-short-description class="dark:text-gray-400">
                {{ __('reports.Automated calculation') }} ({{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }})
            </x-short-description>
        </div>
        <div class="flex items-center gap-3">
            <!-- Quick Period Tabs -->
            <div class="inline-flex p-1 bg-gray-100 dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 text-xs font-extrabold">
                <button wire:click="setPeriod('daily')" class="px-3.5 py-1.5 rounded-xl transition-all {{ $period_type === 'daily' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs font-black' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}">
                    {{ __('reports.Today') }}
                </button>
                <button wire:click="setPeriod('weekly')" class="px-3.5 py-1.5 rounded-xl transition-all {{ $period_type === 'weekly' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs font-black' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}">
                    {{ __('reports.This Week') }}
                </button>
                <button wire:click="setPeriod('monthly')" class="px-3.5 py-1.5 rounded-xl transition-all {{ $period_type === 'monthly' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs font-black' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}">
                    {{ __('reports.This Month') }}
                </button>
            </div>

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

    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 border border-gray-100 dark:border-gray-700 shadow-sm space-y-8">
        <!-- 4 KPI Stat Cards (Minimalist & Brand Oriented) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Revenue Card -->
            <div class="p-5 rounded-3xl bg-white dark:bg-gray-800/80 border border-gray-200/80 dark:border-gray-700/80 flex items-center justify-between shadow-2xs">
                <div>
                    <span class="text-[10px] font-bold uppercase text-gray-500 dark:text-gray-400 tracking-wider">{{ __('reports.Total Revenue') }}</span>
                    <h3 class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono mt-1">
                        {{ format_currency($totalRevenue, auth()->user()?->barberShop) }}
                    </h3>
                </div>
                <div class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 shrink-0">
                    <x-heroicon-o-banknotes class="w-6 h-6" />
                </div>
            </div>

            <!-- Total Bookings Card -->
            <div class="p-5 rounded-3xl bg-white dark:bg-gray-800/80 border border-gray-200/80 dark:border-gray-700/80 flex items-center justify-between shadow-2xs">
                <div>
                    <span class="text-[10px] font-bold uppercase text-gray-500 dark:text-gray-400 tracking-wider">{{ __('reports.Total Bookings') }}</span>
                    <h3 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white font-mono mt-1">
                        {{ $totalBookings }}
                    </h3>
                </div>
                <div class="p-3.5 rounded-2xl bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-200 shrink-0">
                    <x-heroicon-o-calendar-days class="w-6 h-6" />
                </div>
            </div>

            <!-- Completed Card -->
            <div class="p-5 rounded-3xl bg-white dark:bg-gray-800/80 border border-gray-200/80 dark:border-gray-700/80 flex items-center justify-between shadow-2xs">
                <div>
                    <span class="text-[10px] font-bold uppercase text-gray-500 dark:text-gray-400 tracking-wider">{{ __('reports.Total Completed') }}</span>
                    <h3 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white font-mono mt-1">
                        {{ $totalCompleted }}
                    </h3>
                </div>
                <div class="p-3.5 rounded-2xl bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-200 shrink-0">
                    <x-heroicon-o-check-circle class="w-6 h-6" />
                </div>
            </div>

            <!-- Cancelled Card -->
            <div class="p-5 rounded-3xl bg-white dark:bg-gray-800/80 border border-gray-200/80 dark:border-gray-700/80 flex items-center justify-between shadow-2xs">
                <div>
                    <span class="text-[10px] font-bold uppercase text-gray-500 dark:text-gray-400 tracking-wider">{{ __('reports.Total Cancelled') }}</span>
                    <h3 class="text-2xl sm:text-3xl font-black text-red-500 font-mono mt-1">
                        {{ $totalCancelled }}
                    </h3>
                </div>
                <div class="p-3.5 rounded-2xl bg-red-50 dark:bg-red-900/20 text-red-500 shrink-0">
                    <x-heroicon-o-x-circle class="w-6 h-6" />
                </div>
            </div>
        </div>

        <!-- Staff Performance Breakdown Table (Pro Analytics) -->
        @if($barberBreakdown->isNotEmpty())
            <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <x-heroicon-o-user-group class="w-4 h-4 text-blue-500" />
                        <span>{{ __('reports.Staff Performance Breakdown') }}</span>
                    </h2>
                    <span class="text-[11px] font-bold text-gray-400 font-mono">({{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }})</span>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700/80">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="bg-gray-100/70 dark:bg-gray-700/70 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('reports.Staff') }}</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('reports.Total Bookings') }}</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('reports.Total Completed') }}</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('reports.Total Revenue') }}</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('reports.Revenue Share') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700/80">
                            @foreach($barberBreakdown as $b)
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-900/60 transition-colors">
                                    <td class="px-6 py-4 font-black text-xs text-gray-900 dark:text-white flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 font-black text-[11px] flex items-center justify-center">
                                            {{ mb_substr($b['barber_name'], 0, 1) }}
                                        </div>
                                        <span>{{ $b['barber_name'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 font-extrabold text-xs text-gray-700 dark:text-gray-300 font-mono">
                                        {{ $b['total_bookings'] }}
                                    </td>
                                    <td class="px-6 py-4 font-extrabold text-xs text-gray-700 dark:text-gray-300 font-mono">
                                        {{ $b['completed_bookings'] }}
                                    </td>
                                    <td class="px-6 py-4 font-black text-emerald-600 dark:text-emerald-400 font-mono text-sm">
                                        {{ format_currency($b['revenue'], auth()->user()?->barberShop) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-24 bg-gray-100 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                                <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ min(100, $b['share']) }}%"></div>
                                            </div>
                                            <span class="text-xs font-black font-mono text-gray-700 dark:text-gray-300">{{ $b['share'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Bookings Details Table Section -->
        <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <span class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">{{ __('reports.Period Bookings Details') }}</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('reports.Search by customer, service...') }}" class="w-full sm:w-64 p-2.5 text-xs font-bold bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl dark:text-white">
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
                                    {{ format_currency($b->total_price, auth()->user()?->barberShop) }}
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
