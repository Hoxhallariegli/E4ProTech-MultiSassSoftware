<tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-none border-b border-gray-50 dark:border-gray-700/50 last:border-none">
    <!-- Mobile & Tablet Compact Cell (< lg) -->
    <td colspan="100" class="p-4 space-y-2 lg:hidden">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                @if($item->logo)
                    <img src="{{ asset(ltrim($item->logo, '/')) }}" alt="{{ $item->name }}" class="w-10 h-10 rounded-xl object-cover border border-gray-200 dark:border-gray-700 shrink-0" />
                @else
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-black text-sm flex items-center justify-center shrink-0">
                        {{ mb_substr($item->name, 0, 1) }}
                    </div>
                @endif
                <div>
                    <h4 class="font-extrabold text-xs text-gray-900 dark:text-white">{{ $item->name }}</h4>
                    <p class="text-[11px] text-gray-500 font-mono">Pronar: {{ $item->owner?->name ?? '-' }}</p>
                </div>
            </div>

            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase shrink-0 {{ $item->is_expired ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' }}">
                {{ $item->active_plan_name }}
            </span>
        </div>

        <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 font-medium">
            <span>Slug: <strong class="font-mono text-blue-600 dark:text-blue-400">{{ $item->slug }}</strong></span>
            <span class="font-mono font-bold {{ $item->is_expired ? 'text-red-500' : 'text-emerald-600' }}">{{ $item->days_left }} Ditë Mbetura</span>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
            @can('edit_barber_shops')
                <x-a href="{{ route('admin.barber-shops.edit', $item) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-3 !py-1 !text-[10px] !font-black !uppercase !border-none">Edit</x-a>
            @endcan
            @can('delete_barber_shops')
                <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                    <x-modal>
                        <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">Delete</button></x-slot>
                        <x-slot name="modalTitle"><div class="text-left dark:text-white">Delete {{ $item->name }}?</div></x-slot>
                        <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p><input x-model="confirmation" placeholder="Type {{ $item->name }} to confirm" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                        <x-slot name="footer"><x-button variant="gray" @click="on = false">Cancel</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->name }}'" wire:click="$parent.deleteBarberShop('{{ $item->id }}')" @click="on = false">Delete</x-button></x-slot>
                    </x-modal>
                </div>
            @endcan
        </div>
    </td>

    <!-- Desktop Table Cells (>= lg) -->
    <td class="hidden lg:table-cell px-6 py-5 font-bold text-blue-600 dark:text-blue-400">{{ $item->id }}</td>
    <td class="hidden lg:table-cell px-6 py-5 font-bold text-gray-900 dark:text-white">{{ $item->owner?->name ?? '-' }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->name }}</td>
    <td class="hidden lg:table-cell px-6 py-5 whitespace-nowrap">
        <span class="px-3 py-1 rounded-full text-xs font-black {{ $item->is_expired ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' }}">
            {{ $item->active_plan_name }}
        </span>
    </td>
    <td class="hidden lg:table-cell px-6 py-5 whitespace-nowrap">
        <span class="px-3 py-1 rounded-full text-xs font-black font-mono {{ $item->is_expired ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' }}">
            {{ $item->days_left }} Ditë
        </span>
    </td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->app_name }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->slug }}</td>
    <td class="hidden lg:table-cell px-6 py-3">@if($item->logo) <a href="{{ asset(ltrim($item->logo, '/')) }}" target="_blank" rel="noopener" class="inline-block group" title="View image"><img src="{{ asset(ltrim($item->logo, '/')) }}" alt="{{ e(ucwords(str_replace('_', ' ', 'logo'))) }}" class="w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-gray-700 shadow-sm group-hover:scale-105 transition-transform duration-150" loading="lazy" /></a> @else <span class="text-gray-400">-</span> @endif</td>
    <td class="hidden lg:table-cell px-6 py-3">@if($item->banner) <a href="{{ asset(ltrim($item->banner, '/')) }}" target="_blank" rel="noopener" class="inline-block group" title="View image"><img src="{{ asset(ltrim($item->banner, '/')) }}" alt="{{ e(ucwords(str_replace('_', ' ', 'banner'))) }}" class="w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-gray-700 shadow-sm group-hover:scale-105 transition-transform duration-150" loading="lazy" /></a> @else <span class="text-gray-400">-</span> @endif</td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->primary_color }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->secondary_color }}</td>
    <td class="hidden lg:table-cell px-6 py-5 font-extrabold text-slate-800 dark:text-slate-200">{{ $item->resolved_currency }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->trial_ends_at?->format('d/m/Y H:i') ?? '-' }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->expires_at?->format('d/m/Y H:i') ?? '-' }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->active }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->sms_enabled }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->timezone }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->max_no_show_before_block }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-right !transition-none">
        <div class="flex justify-end gap-3 !transition-none">
            @can('edit_barber_shops')
                <x-a href="{{ route('admin.barber-shops.edit', $item) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-4 !py-1.5 !text-[10px] !font-black !uppercase !border-none">Edit</x-a>
            @endcan
            @can('delete_barber_shops')
                <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                    <x-modal>
                        <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">Delete</button></x-slot>
                        <x-slot name="modalTitle"><div class="text-left dark:text-white">Delete {{ $item->name }}?</div></x-slot>
                        <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p><input x-model="confirmation" placeholder="Type {{ $item->name }} to confirm" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                        <x-slot name="footer"><x-button variant="gray" @click="on = false">Cancel</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->name }}'" wire:click="$parent.deleteBarberShop('{{ $item->id }}')" @click="on = false">Delete</x-button></x-slot>
                    </x-modal>
                </div>
            @endcan
        </div>
    </td>
</tr>
