<div x-data="{ openFilter: @entangle('openFilter') }">
    <div class="card !p-0 overflow-hidden shadow-none border-gray-200 dark:border-gray-700 dark:bg-gray-800">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div><x-h1>{{ __('shop-front-page-settings.ShopFrontPageSettings') }}</x-h1><x-short-description class="dark:text-gray-400">{{ __('shop-front-page-settings.List of') }} shopfrontpagesettings</x-short-description></div>
                <div class="flex items-center gap-3">
                    @if($search || $openFilter)
                        <button wire:click="resetFilters" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-2xl transition-none shadow-none"><span>{{ __('shop-front-page-settings.Reset') }}</span></button>
                    @endif
                    <button @click="openFilter = !openFilter" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-600 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-sm transition-none"><span>{{ __('shop-front-page-settings.Filters') }}</span></button>
                    @can('add_shop_front_page_settings')
                        <x-btn :href="route('admin.shop-front-page-settings.create')" icon="plus">{{ __('shop-front-page-settings.Add ShopFrontPageSetting') }}</x-btn>
                    @endcan
                </div>
            </div>

            <div x-show="openFilter" x-cloak class="mt-6 p-6 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 rounded-2xl">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">{{ __('shop-front-page-settings.Search') }}</label>
                        <input name="search" wire:model.live.debounce.300ms="search" type="text" placeholder="Search by ID, Hero_Title, Hero_Subtitle, Hero_Button_Text, Services_Badge_Text, Services_Title, Staff_Badge_Text, Staff_Title, Contact_Phone, Contact_Email, Contact_Address, Google_Maps_Url, Footer_Text" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                    </div>
                    @if(auth()->user()->is_global_admin)
<div><label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">Barber Shop Id</label><x-form.dropdown-search name="barber_shop_id" wire:model.live="barber_shop_id" label="none" :data="$barberShops" placeholder="Filter Barber Shop Id" /></div>
@endif
                </div>
            </div>
        </div>

        @include('errors.messages')

        <div class="overflow-x-auto border-t border-gray-100 dark:border-gray-700">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-100/50 dark:bg-gray-700/50"><tr><x-table.th name="id" :label="__('shop-front-page-settings.ID')" :$sortField :$sortAsc :sortable="true" /><x-table.th name="hero_title" :label="__('shop-front-page-settings.Hero Title')" :$sortField :$sortAsc :sortable="in_array('hero_title', $sortableFields)" />
<x-table.th name="hero_subtitle" :label="__('shop-front-page-settings.Hero Subtitle')" :$sortField :$sortAsc :sortable="in_array('hero_subtitle', $sortableFields)" />
<x-table.th name="hero_button_text" :label="__('shop-front-page-settings.Hero Button Text')" :$sortField :$sortAsc :sortable="in_array('hero_button_text', $sortableFields)" />
<x-table.th name="services_badge_text" :label="__('shop-front-page-settings.Services Badge Text')" :$sortField :$sortAsc :sortable="in_array('services_badge_text', $sortableFields)" />
<x-table.th name="services_title" :label="__('shop-front-page-settings.Services Title')" :$sortField :$sortAsc :sortable="in_array('services_title', $sortableFields)" />
<x-table.th name="staff_badge_text" :label="__('shop-front-page-settings.Staff Badge Text')" :$sortField :$sortAsc :sortable="in_array('staff_badge_text', $sortableFields)" />
<x-table.th name="staff_title" :label="__('shop-front-page-settings.Staff Title')" :$sortField :$sortAsc :sortable="in_array('staff_title', $sortableFields)" />
<x-table.th name="contact_phone" :label="__('shop-front-page-settings.Contact Phone')" :$sortField :$sortAsc :sortable="in_array('contact_phone', $sortableFields)" />
<x-table.th name="contact_email" :label="__('shop-front-page-settings.Contact Email')" :$sortField :$sortAsc :sortable="in_array('contact_email', $sortableFields)" />
<x-table.th name="contact_address" :label="__('shop-front-page-settings.Contact Address')" :$sortField :$sortAsc :sortable="in_array('contact_address', $sortableFields)" />
<x-table.th name="google_maps_url" :label="__('shop-front-page-settings.Google Maps Url')" :$sortField :$sortAsc :sortable="in_array('google_maps_url', $sortableFields)" />
<x-table.th name="footer_text" :label="__('shop-front-page-settings.Footer Text')" :$sortField :$sortAsc :sortable="in_array('footer_text', $sortableFields)" />
@if(auth()->user()->is_global_admin)
<x-table.th name="barber_shop_id" :label="__('shop-front-page-settings.Barber Shop Id')" :$sortField :$sortAsc :sortable="in_array('barber_shop_id', $sortableFields)" />
@endif<th class="px-6 py-4 text-right text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('shop-front-page-settings.Action') }}</th></tr></thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">@forelse($items as $item) <livewire:admin.shop-front-page-settings.row :$item :key="$item->id" /> @empty <tr><td colspan="100" class="px-6 py-10 text-center text-sm text-gray-400">{{ __('shop-front-page-settings.No records found.') }}</td></tr> @endforelse</tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-50 dark:border-gray-700/50">{{ $items->links() }}</div>
    </div>
</div>