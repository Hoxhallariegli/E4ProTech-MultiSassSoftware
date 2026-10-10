<div x-data="{ openFilter: @entangle('openFilter') }">
    <div class="space-y-8">

        <!-- 🌟 PRO PLAN SELECTION HEADER & PRICING CARDS -->
        <div class="bg-white dark:bg-gray-800 p-8 sm:p-10 rounded-[2.5rem] border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">Renovoni Abonimin e Dyqanit Tuaj</h1>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-1">Zgjidhni planin më të mirë dhe periudhën e abonimit për të vazhduar operimin pa ndërprerje</p>
                </div>

                <!-- ⏱️ DURATION TOGGLE SWITCHER (MIN 6 MONTHS) -->
                <div class="inline-flex p-1.5 bg-gray-100 dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700">
                    <button wire:click="setBillingDuration(6)" class="px-5 py-2.5 rounded-xl text-xs font-black transition-all {{ $billingDuration === 6 ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        6 Muaj
                    </button>
                    <button wire:click="setBillingDuration(12)" class="px-5 py-2.5 rounded-xl text-xs font-black transition-all flex items-center gap-2 {{ $billingDuration === 12 ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        <span>1 Vit (12 Muaj)</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">5% OFF</span>
                    </button>
                    <button wire:click="setBillingDuration(24)" class="px-5 py-2.5 rounded-xl text-xs font-black transition-all flex items-center gap-2 {{ $billingDuration === 24 ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                        <span>2 Vite (24 Muaj)</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">10% OFF</span>
                    </button>
                </div>
            </div>

            <!-- 💎 PLAN CARDS GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
                @foreach($availablePlans as $index => $plan)
                    @php
                        $basePrice = (float) ($plan->price ?? 0);
                        $months = $billingDuration;
                        $discountPct = $months === 12 ? 0.05 : ($months === 24 ? 0.10 : 0.0);
                        $totalPrice = ($basePrice * $months) * (1.0 - $discountPct);
                        $monthlyAvg = $totalPrice / $months;
                        $isPopular = (strtolower($plan->name) === 'pro' || strtolower($plan->name) === 'pro plan' || ($availablePlans->count() > 1 && $index === 1 && !str_contains(strtolower($plan->name), 'unlimited')));
                    @endphp

                    <div class="flex flex-col justify-between p-7 rounded-[2rem] border transition-all duration-200 {{ $isPopular ? 'border-2 border-indigo-600 dark:border-indigo-500 bg-indigo-50/10 dark:bg-indigo-950/20 shadow-sm' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800' }}">
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="text-xl font-black text-gray-900 dark:text-white">{{ $plan->name }}</h3>
                                @if($isPopular)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">
                                        🌟 Popullor
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-[10px] font-black uppercase rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                        {{ $months }} Muaj
                                    </span>
                                @endif
                            </div>

                            <div class="mt-4 flex items-baseline gap-2">
                                <span class="text-3xl font-black text-gray-900 dark:text-white">{{ number_format($totalPrice, 2) }} €</span>
                                <span class="text-xs font-bold text-gray-400">/ {{ $months }} muaj</span>
                            </div>
                            <div class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 mt-1">
                                ~ {{ number_format($monthlyAvg, 2) }} € / muaj
                                @if($discountPct > 0)
                                    ({{ ($discountPct * 100) }}% Ulje e Aplikuar)
                                @endif
                            </div>

                            <div class="my-6 border-t border-gray-100 dark:border-gray-700/60"></div>

                            <!-- FEATURES CHECKLIST -->
                            <ul class="space-y-3 text-xs font-bold text-gray-600 dark:text-gray-300">
                                <li class="flex items-center gap-2">
                                    <span class="text-emerald-500 text-sm">✓</span>
                                    <span>Staf/Punonjës: {{ $plan->max_barbers > 900 ? 'PA LIMIT' : 'Deri në ' . $plan->max_barbers }}</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-emerald-500 text-sm">✓</span>
                                    <span>Shërbime: {{ $plan->max_services > 900 ? 'PA LIMIT' : 'Deri në ' . $plan->max_services }}</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-emerald-500 text-sm">✓</span>
                                    <span>Dyqane/Sallone: {{ $plan->max_shops > 900 ? 'PA LIMIT' : 'Deri në ' . $plan->max_shops }}</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-emerald-500 text-sm">✓</span>
                                    <span>SMS Gateway & Push Notifications</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-emerald-500 text-sm">✓</span>
                                    <span>Mbështetje Teknike 24/7</span>
                                </li>
                            </ul>
                        </div>

                        <div class="mt-8">
                            <x-btn :href="route('admin.subscription-renewals.create', ['plan_id' => $plan->id, 'amount' => $totalPrice, 'duration' => $months])" class="w-full !justify-center !py-3.5 !rounded-2xl !font-black !text-sm {{ $isPopular ? '!bg-indigo-600 !text-white hover:!bg-indigo-700' : '' }}">
                                Zgjidh Planin & Renovo 🚀
                            </x-btn>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 📜 RENEWAL REQUESTS HISTORY TABLE -->
        <div class="card !p-0 overflow-hidden shadow-none border-gray-200 dark:border-gray-700 dark:bg-gray-800">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-black text-gray-900 dark:text-white">Historiku i Kërkesave të Renovimit</h2>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">Shikoni statusin e faturave të mandat-pagesave dhe miratimet nga administratori</p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($search || $openFilter)
                            <button wire:click="resetFilters" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-2xl transition-none shadow-none"><span>Reset</span></button>
                        @endif
                        <button @click="openFilter = !openFilter" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-600 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-sm transition-none"><span>Filtra</span></button>
                    </div>
                </div>

                <div x-show="openFilter" x-cloak class="mt-6 p-6 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 rounded-2xl">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">Kërko Kërkesën</label>
                            <input name="search" wire:model.live.debounce.300ms="search" type="text" placeholder="Kërko me ID, shënime ose mënyrë pagese..." class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                        </div>
                        <div>
                            <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">Filtro me Plan</label>
                            <x-form.dropdown-search name="plan_id" wire:model.live="plan_id" label="none" :data="$plans" placeholder="Zgjidh Planin" />
                        </div>
                    </div>
                </div>
            </div>

            @include('errors.messages')

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-100/50 dark:bg-gray-700/50">
                        <tr>
                            <x-table.th name="id" label="ID" :$sortField :$sortAsc :sortable="true" />
                            <x-table.th name="plan_id" label="Salloni / Plani" :$sortField :$sortAsc :sortable="true" />
                            <x-table.th name="payment_method" label="Mënyra e Pagesës" :$sortField :$sortAsc :sortable="true" />
                            <x-table.th name="transfer_document" label="Fatura / Mandat-Pagesa" :$sortField :$sortAsc :sortable="false" />
                            <x-table.th name="amount" label="Shuma (€)" :$sortField :$sortAsc :sortable="true" />
                            <x-table.th name="notes" label="Shënime" :$sortField :$sortAsc :sortable="false" />
                            <x-table.th name="status" label="Statusi i Miratimit" :$sortField :$sortAsc :sortable="true" />
                            <th class="px-6 py-4 text-right text-[10px] font-black uppercase text-gray-400 tracking-widest">Veprimet</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @forelse($items as $item)
                            <livewire:admin.subscription-renewals.row :$item :key="$item->id" />
                        @empty
                            <tr><td colspan="100" class="px-6 py-10 text-center text-sm text-gray-400">Nuk u gjet asnjë kërkesë renovimi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-50 dark:border-gray-700/50">{{ $items->links() }}</div>
        </div>

    </div>
</div>
