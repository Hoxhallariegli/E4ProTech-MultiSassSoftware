<tr class="hover:bg-gray-50/80 dark:hover:bg-gray-900/60 transition-colors border-b border-gray-200 dark:border-gray-700/80 last:border-none">
    <!-- Mobile & Tablet Compact Cell (< lg) -->
    <td colspan="100" class="p-4 space-y-2 lg:hidden">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                @if($item->photo)
                    <img src="{{ asset(ltrim($item->photo, '/')) }}" alt="{{ $item->name }}" class="w-10 h-10 rounded-xl object-cover border border-gray-200 dark:border-gray-700 shrink-0" />
                @else
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-black text-sm flex items-center justify-center shrink-0">
                        {{ mb_substr($item->name, 0, 1) }}
                    </div>
                @endif
                <div>
                    <h4 class="font-extrabold text-xs text-gray-900 dark:text-white">{{ $item->name }}</h4>
                    <p class="text-[11px] text-gray-500 font-mono">{{ $item->phone ?: 'Pa telefon' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $wa_connected ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                    WA: {{ $wa_connected ? 'Lidhur' : 'Pa lidhur' }}
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $item->active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                    {{ $item->active ? 'Aktiv' : 'Inaktiv' }}
                </span>
            </div>
        </div>

        @if($item->bio)
            <p class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-1 font-medium">{{ $item->bio }}</p>
        @endif

        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800 text-[10px]">
            <span class="text-gray-400 font-mono">User: {{ $item->user?->name ?? 'Pa llogari' }}</span>
            <div class="flex items-center gap-2">
                @can('edit_barbers')
                    <x-a href="{{ route('admin.barbers.edit', $item) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-3 !py-1 !text-[10px] !font-black !uppercase !border-none">{{ __('admin.Edit') }}</x-a>
                @endcan
                @can('delete_barbers')
                    <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                        <x-modal>
                            <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">{{ __('admin.Delete') }}</button></x-slot>
                            <x-slot name="modalTitle"><div class="text-left dark:text-white">{{ __('admin.Delete') }} {{ $item->name }}?</div></x-slot>
                            <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p><input x-model="confirmation" placeholder="Type {{ $item->name }} to confirm" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                            <x-slot name="footer"><x-button variant="gray" @click="on = false">{{ __('admin.Cancel') }}</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->name }}'" wire:click="$parent.deleteBarber('{{ $item->id }}')" @click="on = false">{{ __('admin.Delete') }}</x-button></x-slot>
                        </x-modal>
                    </div>
                @endcan
            </div>
        </div>
    </td>

    <!-- Desktop Table Cells (>= lg) -->
    <td class="hidden lg:table-cell px-6 py-5 font-bold text-blue-600 dark:text-blue-400">#{{ $item->id }}</td>
    @if(auth()->user()->is_global_admin)
        <td class="hidden lg:table-cell px-6 py-5 font-bold text-gray-900 dark:text-white">{{ $item->barberShop?->name ?? '-' }}</td>
    @endif
    <td class="hidden lg:table-cell px-6 py-5 font-bold text-gray-900 dark:text-white">{{ $item->user?->name ?? '-' }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300 font-extrabold">{{ $item->name }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300 font-mono">{{ $item->phone ?: '-' }}</td>
    <td class="hidden lg:table-cell px-6 py-3">@if($item->photo) <a href="{{ asset(ltrim($item->photo, '/')) }}" target="_blank" rel="noopener" class="inline-block group" title="View image"><img src="{{ asset(ltrim($item->photo, '/')) }}" alt="{{ e(ucwords(str_replace('_', ' ', 'photo'))) }}" class="w-11 h-11 rounded-xl object-cover border border-gray-200 dark:border-gray-700 shadow-sm group-hover:scale-105 transition-transform duration-150" loading="lazy" /></a> @else <span class="text-gray-400">-</span> @endif</td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300 truncate max-w-xs">{{ $item->bio ?: '-' }}</td>
    <td class="hidden lg:table-cell px-6 py-5">
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $item->active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
            {{ $item->active ? 'Aktiv' : 'Inaktiv' }}
        </span>
    </td>
    <td class="hidden lg:table-cell px-6 py-5 whitespace-nowrap">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase {{ $wa_connected ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
            <span class="w-2 h-2 rounded-full {{ $wa_connected ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}"></span>
            <span>{{ $wa_connected ? 'Lidhur' : 'Pa lidhur' }}</span>
        </span>
    </td>
    <td class="hidden lg:table-cell px-6 py-5 text-right !transition-none">
        <div class="flex justify-end gap-3 !transition-none">
            @can('edit_barbers')
                <x-a href="{{ route('admin.barbers.edit', $item) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-4 !py-1.5 !text-[10px] !font-black !uppercase !border-none">{{ __('admin.Edit') }}</x-a>
            @endcan
            @can('delete_barbers')
                <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                    <x-modal>
                        <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">{{ __('admin.Delete') }}</button></x-slot>
                        <x-slot name="modalTitle"><div class="text-left dark:text-white">{{ __('admin.Delete') }} {{ $item->name }}?</div></x-slot>
                        <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p><input x-model="confirmation" placeholder="Type {{ $item->name }} to confirm" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                        <x-slot name="footer"><x-button variant="gray" @click="on = false">{{ __('admin.Cancel') }}</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->name }}'" wire:click="$parent.deleteBarber('{{ $item->id }}')" @click="on = false">{{ __('admin.Delete') }}</x-button></x-slot>
                    </x-modal>
                </div>
            @endcan

            @if(auth()->user()?->is_global_admin)
                <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                    <x-modal>
                        <x-slot name="trigger">
                            <button @click="on = true" class="text-[10px] font-black uppercase text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-200 bg-rose-50 dark:bg-rose-900/30 px-3 py-1.5 rounded-xl border border-rose-200/60 dark:border-rose-800/60">
                                ⚡ Force Delete
                            </button>
                        </x-slot>
                        <x-slot name="modalTitle">
                            <div class="text-left dark:text-white text-rose-600 font-black">⚡ Fshij Tërësisht (Force Delete) - {{ $item->name }}</div>
                        </x-slot>
                        <x-slot name="content">
                            <div class="text-left space-y-3">
                                <p class="text-xs text-red-600 dark:text-red-400 font-bold leading-relaxed">
                                    ⚠️ VETËM PËR SUPER ADMIN: Kjo komandë do të fshijë TËRËSISHT këtë anëtar stafit së bashku me të gjitha rezervimet, oraret e punës dhe mungesat lidhëse!
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Shkruaj <strong class="text-black dark:text-white">{{ $item->name }}</strong> për ta konfirmuar:
                                </p>
                                <input x-model="confirmation" placeholder="Shkruaj {{ $item->name }} për konfirmim" class="w-full px-3 py-2 border rounded-xl dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-rose-500 outline-none text-xs font-bold">
                            </div>
                        </x-slot>
                        <x-slot name="footer">
                            <x-button variant="gray" @click="on = false">Anulo</x-button>
                            <x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->name }}'" wire:click="$parent.forceDeleteBarber('{{ $item->id }}')" @click="on = false">⚡ Fshij Tërësisht</x-button>
                        </x-slot>
                    </x-modal>
                </div>
            @endif
        </div>
    </td>
</tr>
