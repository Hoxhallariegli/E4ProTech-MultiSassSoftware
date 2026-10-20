<div x-data="{ openFilter: @entangle('openFilter') }" class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <x-h1>{{ __('admin.Website Offer Requests') }}</x-h1>
            <x-short-description class="dark:text-gray-400">{{ __('admin.Custom domain website requests') }}</x-short-description>
        </div>
        <div class="flex items-center gap-3">
            <x-a href="{{ route('admin.website-offer-requests.create') }}" class="!rounded-2xl !bg-[#1A1D20] hover:!bg-black !text-white !px-6 !py-3 !font-black !text-xs">
                + {{ __('admin.Add Request') }}
            </x-a>
        </div>
    </div>

    @include('errors.messages')

    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 border border-gray-100 dark:border-gray-700 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="w-full sm:w-96">
                <input name="search" wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('admin.Search Requests') }}" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
            </div>
            <div class="flex items-center gap-3">
                @if($search || $status || $openFilter)
                    <button wire:click="resetFilters" class="px-3 py-2 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-2xl transition">{{ __('admin.Reset') }}</button>
                @endif
                <button @click="openFilter = !openFilter" class="px-4 py-2.5 text-xs font-extrabold text-gray-700 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xs transition-none">{{ __('admin.Filters') }}</button>
            </div>
        </div>

        <div x-show="openFilter" x-cloak class="p-6 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-900 dark:text-gray-100">{{ __('admin.Status') }}</label>
                    <select wire:model.live="status" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl dark:text-white">
                        <option value="">{{ __('admin.All') }}</option>
                        <option value="pending">{{ __('admin.Pending') }}</option>
                        <option value="contacted">{{ __('admin.Contacted') }}</option>
                        <option value="completed">{{ __('admin.Completed') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700/80">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-100/70 dark:bg-gray-700/70 hidden lg:table-header-group border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <x-table.th name="id" label="ID" :$sortField :$sortAsc :sortable="true" />
                        <x-table.th name="barber_shop_id" :label="__('admin.Salon / User')" :$sortField :$sortAsc :sortable="false" />
                        <x-table.th name="desired_domain" :label="__('admin.Desired Domain')" :$sortField :$sortAsc :sortable="true" />
                        <x-table.th name="phone" :label="__('admin.Phone')" :$sortField :$sortAsc :sortable="true" />
                        <x-table.th name="notes" :label="__('admin.Notes')" :$sortField :$sortAsc :sortable="false" />
                        <x-table.th name="status" :label="__('admin.Status')" :$sortField :$sortAsc :sortable="true" />
                        <x-table.th name="created_at" :label="__('admin.Time')" :$sortField :$sortAsc :sortable="true" />
                        <th class="px-6 py-4 text-right text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('admin.Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700/80">
                    @forelse($items as $item)
                        @include('livewire.admin.website-offer-requests.row', ['item' => $item])
                    @empty
                        <tr><td colspan="100" class="px-6 py-10 text-center text-sm text-gray-400">{{ __('admin.No records found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $items->links() }}</div>
    </div>
</div>
