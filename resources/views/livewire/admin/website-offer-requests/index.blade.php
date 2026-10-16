<div x-data="{ openFilter: @entangle('openFilter') }">
    <div class="space-y-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <x-h1>Kërkesat për Uebsajt</x-h1>
                <x-short-description class="dark:text-gray-400">Lista e të gjitha kërkesave për uebsajte me domain të personalizuar (.al / .com)</x-short-description>
            </div>
            <div class="flex items-center gap-3">
                <x-a href="{{ route('admin.website-offer-requests.create') }}" class="!rounded-2xl !bg-[#1A1D20] hover:!bg-black !text-white !px-6 !py-3 !font-black !text-xs">
                    + Shto Kërkesë të Re
                </x-a>
            </div>
        </div>

        <div class="card !p-0 overflow-hidden border-gray-200 dark:border-gray-700 dark:bg-gray-800 shadow-sm">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="w-full sm:w-96">
                        <input name="search" wire:model.live.debounce.300ms="search" type="text" placeholder="Kërko me domain, sallon apo telefon..." class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                    </div>
                    <div class="flex items-center gap-3">
                        @if($search || $status || $openFilter)
                            <button wire:click="resetFilters" class="px-3 py-2 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-2xl transition">Pastro</button>
                        @endif
                        <button @click="openFilter = !openFilter" class="px-4 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-sm">Filtro</button>
                    </div>
                </div>

                <div x-show="openFilter" x-cloak class="mt-6 p-6 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 rounded-2xl">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-900 dark:text-gray-100">Statusi</label>
                            <select wire:model.live="status" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl dark:text-white">
                                <option value="">Të gjitha</option>
                                <option value="pending">Në Pritje (Pending)</option>
                                <option value="contacted">U Kontaktua (Contacted)</option>
                                <option value="completed">E Përfunduar (Completed)</option>
                            </select>
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
                            <x-table.th name="barber_shop_id" label="Salloni / Përdoruesi" :$sortField :$sortAsc :sortable="false" />
                            <x-table.th name="desired_domain" label="Domain-i i Dëshiruar" :$sortField :$sortAsc :sortable="true" />
                            <x-table.th name="phone" label="Telefon" :$sortField :$sortAsc :sortable="true" />
                            <x-table.th name="notes" label="Shënime" :$sortField :$sortAsc :sortable="false" />
                            <x-table.th name="status" label="Statusi" :$sortField :$sortAsc :sortable="true" />
                            <x-table.th name="created_at" label="Koha" :$sortField :$sortAsc :sortable="true" />
                            <th class="px-6 py-4 text-right text-[10px] font-black uppercase text-gray-400 tracking-widest">Veprimet</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @forelse($items as $item)
                            @include('livewire.admin.website-offer-requests.row', ['item' => $item])
                        @empty
                            <tr><td colspan="100" class="px-6 py-10 text-center text-sm text-gray-400">Nuk u gjet asnjë kërkesë për uebsajt.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-50 dark:border-gray-700/50">{{ $items->links() }}</div>
        </div>
    </div>
</div>
