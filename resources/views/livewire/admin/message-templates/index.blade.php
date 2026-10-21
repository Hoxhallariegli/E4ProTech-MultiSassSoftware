<div x-data="{ openFilter: @entangle('openFilter') }" class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div><x-h1>{{ __('message-templates.MessageTemplates') }}</x-h1><x-short-description class="dark:text-gray-400">{{ __('message-templates.description') }}</x-short-description></div>
        <div class="flex items-center gap-3">
            @if($search || $barber_shop_id || $openFilter)
                <button wire:click="resetFilters" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-2xl transition-none shadow-none"><span>{{ __('message-templates.Reset') }}</span></button>
            @endif
            <button @click="openFilter = !openFilter" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-extrabold text-gray-700 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xs transition-none"><span>{{ __('message-templates.Filters') }}</span></button>
            @can('add_message_templates')
                <x-btn :href="route('admin.message-templates.create')" icon="plus">{{ __('message-templates.Add MessageTemplate') }}</x-btn>
            @endcan
        </div>
    </div>

    <div x-show="openFilter" x-cloak class="p-6 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">{{ __('message-templates.Search') }}</label>
                <input name="search" wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('message-templates.search_placeholder') }}" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
            </div>
            @if(auth()->user()->is_global_admin)
                <div><label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">{{ __('message-templates.Barber Shop Id') }}</label><x-form.dropdown-search name="barber_shop_id" wire:model.live="barber_shop_id" label="none" :data="$barberShops" placeholder="{{ __('message-templates.filter_shop_placeholder') }}" /></div>
            @endif
        </div>
    </div>

    @include('errors.messages')

    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 border border-gray-100 dark:border-gray-700 shadow-sm space-y-6">
        <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700/80">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-100/70 dark:bg-gray-700/70 hidden lg:table-header-group border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <x-table.th name="id" :label="__('message-templates.ID')" :$sortField :$sortAsc :sortable="true" />
                        @if(auth()->user()->is_global_admin)
                            <x-table.th name="barber_shop_id" :label="__('message-templates.Barber Shop Id')" :$sortField :$sortAsc :sortable="in_array('barber_shop_id', $sortableFields)" />
                        @endif
                        <x-table.th name="channel" :label="__('message-templates.Channel')" :$sortField :$sortAsc :sortable="in_array('channel', $sortableFields)" />
                        <x-table.th name="type" :label="__('message-templates.Type')" :$sortField :$sortAsc :sortable="in_array('type', $sortableFields)" />
                        <x-table.th name="content" :label="__('message-templates.Content')" :$sortField :$sortAsc :sortable="in_array('content', $sortableFields)" />
                        <th class="px-6 py-4 text-right text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('message-templates.Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700/80">
                    @forelse($items as $item)
                        <livewire:admin.message-templates.row :$item :key="$item->id" />
                    @empty
                        <tr><td colspan="100" class="px-6 py-10 text-center text-sm text-gray-400">{{ __('message-templates.No records found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $items->links() }}</div>
    </div>
</div>
