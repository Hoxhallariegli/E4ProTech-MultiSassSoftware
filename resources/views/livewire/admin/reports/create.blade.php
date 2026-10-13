<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1">
        <div>
            <x-h1>Gjenero Raport të Ri</x-h1>
            <x-short-description class="dark:text-gray-400">Kalkuloni automatikisht xhiron dhe statistikat nga takimet e regjistruara</x-short-description>
        </div>
        <x-back-btn route="admin.reports.index" />
    </div>

    @include('errors.errors')

    <!-- AUTOMATED CALCULATION BANNER -->
    <div class="p-6 rounded-[2.5rem] bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h3 class="font-black text-gray-900 dark:text-white text-base flex items-center gap-2">
                <span>📊</span> Kalkulimi Automatik nga Baza e të Dhënave
            </h3>
            <p class="text-xs font-medium text-gray-600 dark:text-gray-300 mt-1">
                Zgjidhni punonjësin, datën dhe periudhën, pastaj klikoni butonin për të llogaritur automatikisht të gjitha xhirot dhe takimet!
            </p>
        </div>
        <button type="button" wire:click="calculateReportMetrics"
                class="px-6 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs shadow-md transition shrink-0 flex items-center gap-2">
            <span>Kalkulo nga Relacionet</span>
            <span>⚡</span>
        </button>
    </div>

    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700">
        <form wire:submit.prevent="store" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">

                <div>
                    <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300">Periudha e Raportit (Period Type)</label>
                    <select name="period_type" wire:model.live="period_type" class="w-full p-3.5 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                        <option value="daily">📅 Ditore (Daily Report)</option>
                        <option value="weekly">🗓️ Javore (Weekly Report)</option>
                        <option value="monthly">📊 Mujore (Monthly Report)</option>
                        <option value="custom">⚙️ E Personalizuar (Custom)</option>
                    </select>
                </div>

                <div>
                    <x-form.input name="report_date" type="date" wire:model.live="report_date" label="Data e Raportit" class="dark:bg-gray-900" />
                </div>

                <div>
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <x-form.dropdown-search name="barber_id" wire:model.live="barber_id" label="Punonjësi / Berber" :data="$barbers" placeholder="Zgjidh Stafin (Ose Gjithë Stafin)" />
                        </div>
                    </div>
                </div>

                <div>
                    @if(auth()->user()->barber_shop_id && !auth()->user()->is_global_admin)
                        <x-form.input name="barber_shop_name" label="Dyqani / Salloni" value="{{ auth()->user()->barberShop->name }}" readonly disabled class="bg-gray-50" />
                        <input type="hidden" wire:model="barber_shop_id">
                    @else
                        <div class="flex items-end gap-2">
                            <div class="flex-1">
                                <x-form.dropdown-search name="barber_shop_id" wire:model.live="barber_shop_id" label="Dyqani / Salloni" :data="$barberShops" />
                            </div>
                        </div>
                    @endif
                </div>

                <!-- CALCULATED METRICS CARDS -->
                <div class="md:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-4 p-6 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800">
                    <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total Takime</span>
                        <span class="text-2xl font-black text-gray-900 dark:text-white mt-1 block">{{ $total_bookings }}</span>
                    </div>

                    <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total Xhiro (€ / Lekë)</span>
                        <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 block">{{ number_format((float)$total_revenue, 2) }}</span>
                    </div>

                    <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Përfunduara</span>
                        <span class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-1 block">{{ $total_completed }}</span>
                    </div>

                    <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Anuluara</span>
                        <span class="text-2xl font-black text-red-500 mt-1 block">{{ $total_cancelled }}</span>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <x-form.textarea name="notes" wire:model="notes" label="Shënime & Analizë e Raportit" class="dark:bg-gray-900" />
                </div>
            </div>

            <div class="mt-10 flex justify-end">
                <x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl font-black">
                    Ruaj Raportin te Baza e të Dhënave 🚀
                </x-button>
            </div>
        </form>
    </div>
</div>
