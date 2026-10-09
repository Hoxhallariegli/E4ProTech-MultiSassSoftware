<div x-data="{ openFilter: @entangle('openFilter') }">
    <div class="card !p-0 overflow-hidden shadow-none border-gray-200 dark:border-gray-700 dark:bg-gray-800">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div><x-h1>{{ __('test-modules.TestModules') }}</x-h1><x-short-description class="dark:text-gray-400">{{ __('test-modules.List of') }} testmodules</x-short-description></div>
                <div class="flex items-center gap-3">
                    @if($search || $openFilter)
                        <button wire:click="resetFilters" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-2xl transition-none shadow-none"><span>{{ __('test-modules.Reset') }}</span></button>
                    @endif
                    <button @click="openFilter = !openFilter" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-600 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-sm transition-none"><span>{{ __('test-modules.Filters') }}</span></button>
                    @can('add_test_modules')
                        <x-btn :href="route('admin.test-modules.create')" icon="plus">{{ __('test-modules.Add TestModule') }}</x-btn>
                    @endcan
                </div>
            </div>

            <div x-show="openFilter" x-cloak class="mt-6 p-6 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 rounded-2xl">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">{{ __('test-modules.Search') }}</label>
                        <input name="search" wire:model.live.debounce.300ms="search" type="text" placeholder="Search by ID, Name, Description" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                    </div>
                    <div><label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">User Id</label><x-form.dropdown-search name="user_id" wire:model.live="user_id" label="none" :data="$users" placeholder="Filter User Id" /></div>
                </div>
            </div>
        </div>

        @include('errors.messages')

        <div class="overflow-x-auto border-t border-gray-100 dark:border-gray-700">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-100/50 dark:bg-gray-700/50"><tr><x-table.th name="id" :label="__('test-modules.ID')" :$sortField :$sortAsc :sortable="true" /><x-table.th name="name" :label="__('test-modules.Name')" :$sortField :$sortAsc :sortable="in_array('name', $sortableFields)" />
<x-table.th name="description" :label="__('test-modules.Description')" :$sortField :$sortAsc :sortable="in_array('description', $sortableFields)" />
<x-table.th name="qty" :label="__('test-modules.Qty')" :$sortField :$sortAsc :sortable="in_array('qty', $sortableFields)" />
<x-table.th name="price" :label="__('test-modules.Price')" :$sortField :$sortAsc :sortable="in_array('price', $sortableFields)" />
<x-table.th name="is_active" :label="__('test-modules.Is Active')" :$sortField :$sortAsc :sortable="in_array('is_active', $sortableFields)" />
<x-table.th name="due_date" :label="__('test-modules.Due Date')" :$sortField :$sortAsc :sortable="in_array('due_date', $sortableFields)" />
<x-table.th name="event_at" :label="__('test-modules.Event At')" :$sortField :$sortAsc :sortable="in_array('event_at', $sortableFields)" />
<x-table.th name="user_id" :label="__('test-modules.User Id')" :$sortField :$sortAsc :sortable="in_array('user_id', $sortableFields)" />
<x-table.th name="priority" :label="__('test-modules.Priority')" :$sortField :$sortAsc :sortable="in_array('priority', $sortableFields)" />
<x-table.th name="image" :label="__('test-modules.Image')" :$sortField :$sortAsc :sortable="in_array('image', $sortableFields)" />
<x-table.th name="cover_photo" :label="__('test-modules.Cover Photo')" :$sortField :$sortAsc :sortable="in_array('cover_photo', $sortableFields)" /><th class="px-6 py-4 text-right text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('test-modules.Action') }}</th></tr></thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">@forelse($items as $item) <livewire:admin.test-modules.row :$item :key="$item->id" /> @empty <tr><td colspan="100" class="px-6 py-10 text-center text-sm text-gray-400">{{ __('test-modules.No records found.') }}</td></tr> @endforelse</tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-50 dark:border-gray-700/50">{{ $items->links() }}</div>
    </div>
</div>