<div x-data="{ openFilter: @entangle('openFilter') }">
    <div class="card !p-0 overflow-hidden shadow-none border-gray-200 dark:border-gray-700 dark:bg-gray-800">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <x-h1>{{ __('barber-shops.BarberShops') }}</x-h1>
                    <x-short-description class="dark:text-gray-400">Lista e {{ mb_strtolower(__('barber-shops.BarberShops')) }} të regjistruara</x-short-description>
                </div>
                <div class="flex items-center gap-3">
                    @if($search || $openFilter)
                        <button wire:click="resetFilters" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-2xl transition-none shadow-none"><span>{{ __('barber-shops.Reset') }}</span></button>
                    @endif
                    <button @click="openFilter = !openFilter" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-600 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-sm transition-none"><span>{{ __('barber-shops.Filters') }}</span></button>
                    @can('add_barber_shops')
                        <x-btn :href="route('admin.barber-shops.create')" icon="plus">{{ __('barber-shops.Add BarberShop') }}</x-btn>
                    @endcan
                </div>
            </div>

            <div x-show="openFilter" x-cloak class="mt-6 p-6 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 rounded-2xl">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">{{ __('barber-shops.Search') }}</label>
                        <input name="search" wire:model.live.debounce.300ms="search" type="text" placeholder="Kërko me ID, Emër, Slug, Brez Orare" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                    </div>
                    <div><label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">{{ __('barber-shops.Owner Id') }}</label><x-form.dropdown-search name="owner_id" wire:model.live="owner_id" label="none" :data="$owners" placeholder="Filtro sipas pronarit" /></div>
                </div>
            </div>
        </div>

        @include('errors.messages')

        <div class="overflow-x-auto border-t border-gray-100 dark:border-gray-700">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-100/50 dark:bg-gray-700/50">
                    <tr>
                        <x-table.th name="id" :label="__('barber-shops.ID')" :$sortField :$sortAsc :sortable="true" />
                        <x-table.th name="owner_id" :label="__('barber-shops.Owner Id')" :$sortField :$sortAsc :sortable="in_array('owner_id', $sortableFields)" />
                        <x-table.th name="name" :label="__('barber-shops.Name')" :$sortField :$sortAsc :sortable="in_array('name', $sortableFields)" />
                        <x-table.th name="app_name" :label="__('barber-shops.App Name')" :$sortField :$sortAsc :sortable="in_array('app_name', $sortableFields)" />
                        <x-table.th name="slug" :label="__('barber-shops.Slug')" :$sortField :$sortAsc :sortable="in_array('slug', $sortableFields)" />
                        <x-table.th name="logo" :label="__('barber-shops.Logo')" :$sortField :$sortAsc :sortable="in_array('logo', $sortableFields)" />
                        <x-table.th name="banner" :label="__('barber-shops.Banner')" :$sortField :$sortAsc :sortable="in_array('banner', $sortableFields)" />
                        <x-table.th name="primary_color" :label="__('barber-shops.Primary Color')" :$sortField :$sortAsc :sortable="in_array('primary_color', $sortableFields)" />
                        <x-table.th name="secondary_color" :label="__('barber-shops.Secondary Color')" :$sortField :$sortAsc :sortable="in_array('secondary_color', $sortableFields)" />
                        <x-table.th name="trial_ends_at" :label="__('barber-shops.Trial Ends At')" :$sortField :$sortAsc :sortable="in_array('trial_ends_at', $sortableFields)" />
                        <x-table.th name="expires_at" :label="__('barber-shops.Expires At')" :$sortField :$sortAsc :sortable="in_array('expires_at', $sortableFields)" />
                        <x-table.th name="active" :label="__('barber-shops.Active')" :$sortField :$sortAsc :sortable="in_array('active', $sortableFields)" />
                        <x-table.th name="sms_enabled" :label="__('barber-shops.Sms Enabled')" :$sortField :$sortAsc :sortable="in_array('sms_enabled', $sortableFields)" />
                        <x-table.th name="timezone" :label="__('barber-shops.Timezone')" :$sortField :$sortAsc :sortable="in_array('timezone', $sortableFields)" />
                        <x-table.th name="max_no_show_before_block" :label="__('barber-shops.Max No Show Before Block')" :$sortField :$sortAsc :sortable="in_array('max_no_show_before_block', $sortableFields)" />
                        <th class="px-6 py-4 text-right text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('barber-shops.Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    @forelse($items as $item)
                        <livewire:admin.barber-shops.row :$item :key="$item->id" />
                    @empty
                        <tr><td colspan="100" class="px-6 py-10 text-center text-sm text-gray-400">{{ __('barber-shops.No records found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-50 dark:border-gray-700/50">{{ $items->links() }}</div>
    </div>
</div>
