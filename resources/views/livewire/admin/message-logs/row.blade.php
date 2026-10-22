<tr class="hover:bg-gray-50/80 dark:hover:bg-gray-900/60 transition-colors border-b border-gray-200 dark:border-gray-700/80 last:border-none">
    <!-- Mobile & Tablet Compact Cell (< lg) -->
    <td colspan="100" class="p-4 space-y-3 lg:hidden">
        <div class="flex items-center justify-between gap-2 text-xs">
            <span class="font-extrabold text-gray-900 dark:text-white truncate">
                {{ $item->customer?->name ?? 'Klient' }}
            </span>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase shrink-0 {{ $item->status === 'sent' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                {{ $item->status === 'sent' ? 'Dërguar' : 'Dështoi' }}
            </span>
        </div>

        <div class="text-xs text-gray-700 dark:text-gray-200 font-medium bg-gray-50 dark:bg-gray-900/60 p-3 rounded-2xl border border-gray-100 dark:border-gray-800 break-words whitespace-pre-line leading-relaxed">
            {{ $item->message }}
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800 text-[10px]">
            <span class="text-gray-400 font-mono">{{ $item->sent_at?->format('d/m/Y H:i') ?? $item->created_at?->format('d/m/Y H:i') }}</span>
            <div class="flex items-center gap-2">
                @can('delete_message_logs')
                    <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                        <x-modal>
                            <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">Delete</button></x-slot>
                            <x-slot name="modalTitle"><div class="text-left dark:text-white">Fshi {{ $item->id }}?</div></x-slot>
                            <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">Ky veprim nuk mund të kthehet prapa.</p><input x-model="confirmation" placeholder="Shtyp {{ $item->id }} për konfirmim" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                            <x-slot name="footer"><x-button variant="gray" @click="on = false">Anulo</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->id }}'" wire:click="$parent.deleteMessageLog('{{ $item->id }}')" @click="on = false">Fshi</x-button></x-slot>
                        </x-modal>
                    </div>
                @endcan
            </div>
        </div>
    </td>

    <!-- Desktop Table Cells (>= lg) -->
    <td class="hidden lg:table-cell px-6 py-5 font-bold text-blue-600 dark:text-blue-400 w-16">#{{ $item->id }}</td>
    @if(auth()->user()->is_global_admin)
        <td class="hidden lg:table-cell px-6 py-5 font-bold text-gray-900 dark:text-white whitespace-nowrap min-w-[140px]">{{ $item->barberShop?->name ?? '-' }}</td>
    @endif
    <td class="hidden lg:table-cell px-6 py-5 min-w-[160px] whitespace-nowrap">
        <div class="font-bold text-gray-900 dark:text-white text-xs">{{ $item->customer?->name ?? '-' }}</div>
        @if($item->customer?->phone)
            <div class="text-[11px] text-gray-500 font-mono">{{ $item->customer->phone }}</div>
        @endif
    </td>
    <td class="hidden lg:table-cell px-6 py-5 min-w-[130px] whitespace-nowrap">
        @if($item->resolved_template_type === 'cancellation' || $item->resolved_template_type === 'cancelled')
            <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-black uppercase text-red-800 bg-red-100 dark:bg-red-900/40 dark:text-red-300 rounded-full">
                ANULIM
            </span>
        @elseif($item->resolved_template_type === 'reschedule' || $item->resolved_template_type === 'update')
            <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-black uppercase text-purple-800 bg-purple-100 dark:bg-purple-900/40 dark:text-purple-300 rounded-full">
                NDRYSHIM
            </span>
        @elseif($item->resolved_template_type === 'reminder')
            <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-black uppercase text-amber-800 bg-amber-100 dark:bg-amber-900/40 dark:text-amber-300 rounded-full">
                RIKUJTESË
            </span>
        @elseif($item->resolved_template_type === 'welcome')
            <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-black uppercase text-emerald-800 bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300 rounded-full">
                MIRËSEARDHJE
            </span>
        @else
            <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-black uppercase text-blue-800 bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300 rounded-full">
                KONFIRMIM
            </span>
        @endif
    </td>
    <td class="hidden lg:table-cell px-6 py-5 min-w-[280px] max-w-[340px]">
        <div class="text-xs font-medium text-gray-800 dark:text-gray-200 leading-relaxed bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-700 break-words whitespace-normal">
            {{ $item->message }}
        </div>
    </td>
    <td class="hidden lg:table-cell px-6 py-5 text-xs text-gray-700 dark:text-gray-300 font-mono font-medium whitespace-nowrap min-w-[140px]">
        {{ $item->created_at?->format('d/m/Y H:i') ?? '-' }}
    </td>
    <td class="hidden lg:table-cell px-6 py-5 text-xs text-emerald-700 dark:text-emerald-400 font-mono font-bold whitespace-nowrap min-w-[140px]">
        {{ $item->sent_at?->format('d/m/Y H:i') ?? '-' }}
    </td>
    <td class="hidden lg:table-cell px-6 py-5 whitespace-nowrap min-w-[120px]">
        @if($item->status === 'sent')
            <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-black uppercase text-emerald-800 bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300 rounded-full">
                Dërguar
            </span>
        @else
            <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-black uppercase text-red-800 bg-red-100 dark:bg-red-900/40 dark:text-red-300 rounded-full">
                Dështoi
            </span>
        @endif
    </td>
    <td class="hidden lg:table-cell px-6 py-5 text-right w-20 !transition-none">
        <div class="flex justify-end gap-3 !transition-none">
            @can('delete_message_logs')
                <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                    <x-modal>
                        <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">Delete</button></x-slot>
                        <x-slot name="modalTitle"><div class="text-left dark:text-white">Fshi {{ $item->id }} nga logjet?</div></x-slot>
                        <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">Ky veprim nuk mund të kthehet prapa.</p><input x-model="confirmation" placeholder="Shtyp {{ $item->id }} për konfirmim" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                        <x-slot name="footer"><x-button variant="gray" @click="on = false">Anulo</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->id }}'" wire:click="$parent.deleteMessageLog('{{ $item->id }}')" @click="on = false">Fshi</x-button></x-slot>
                    </x-modal>
                </div>
            @endcan
        </div>
    </td>
</tr>
