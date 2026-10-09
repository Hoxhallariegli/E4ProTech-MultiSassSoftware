<tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-none border-b border-gray-50 dark:border-gray-700/50 last:border-none">
    <td class="px-4 py-4 font-bold text-blue-600 dark:text-blue-400 w-16">{{ $item->id }}</td>
    @if(auth()->user()->is_global_admin)
        <td class="px-4 py-4 font-bold text-gray-900 dark:text-white whitespace-nowrap min-w-[140px]">{{ $item->barberShop?->name ?? '-' }}</td>
    @endif
    <td class="px-4 py-4 min-w-[160px] whitespace-nowrap">
        <div class="font-bold text-gray-900 dark:text-white text-sm">{{ $item->booking?->customer?->name ?? $item->phone_number }}</div>
        <div class="text-xs text-gray-500 font-mono">{{ $item->phone_number }}</div>
        @if($item->booking?->appointment_at)
            <div class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 mt-0.5">📅 Takimi: {{ $item->booking->appointment_at->format('d/m/Y H:i') }}</div>
        @endif
    </td>
    <td class="px-4 py-4 min-w-[130px] whitespace-nowrap">
        @if($item->resolved_template_type === 'reminder')
            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-amber-800 bg-amber-100 dark:bg-amber-900/40 dark:text-amber-300 rounded-lg border border-amber-300">
                📌 RIKUJTESË
            </span>
        @elseif($item->resolved_template_type === 'welcome')
            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-emerald-800 bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300 rounded-lg border border-emerald-300">
                📌 MIRËSEARDHJE
            </span>
        @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-blue-800 bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300 rounded-lg border border-blue-300">
                📌 KONFIRMIM
            </span>
        @endif
    </td>
    <td class="px-4 py-4 min-w-[280px] max-w-[340px]">
        <div class="text-xs font-medium text-gray-800 dark:text-gray-200 leading-relaxed bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-700 break-words whitespace-normal">
            {{ $item->message_content }}
        </div>
    </td>
    <td class="px-4 py-4 text-xs text-gray-700 dark:text-gray-300 font-bold whitespace-nowrap min-w-[140px]">
        {{ $item->scheduled_at?->format('d/m/Y H:i') ?? '-' }}
    </td>
    <td class="px-4 py-4 whitespace-nowrap min-w-[120px]">
        @if($item->status === 'sent')
            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-emerald-800 bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300 rounded-lg">
                ✅ Dërguar më {{ $item->updated_at?->format('d/m/Y H:i') }}
            </span>
        @elseif($item->status === 'failed')
            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-red-800 bg-red-100 dark:bg-red-900/40 dark:text-red-300 rounded-lg">
                ❌ Dështoi
            </span>
        @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-amber-800 bg-amber-100 dark:bg-amber-900/40 dark:text-amber-300 rounded-lg">
                ⏳ Në Pritje
            </span>
        @endif
    </td>
    <td class="px-4 py-4 text-right w-20 !transition-none">
        <div class="flex justify-end gap-3 !transition-none">
            @can('delete_message_queues')
                <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                    <x-modal>
                        <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">Delete</button></x-slot>
                        <x-slot name="modalTitle"><div class="text-left dark:text-white">Fshi {{ $item->id }} nga radha?</div></x-slot>
                        <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">Ky veprim nuk mund të kthehet prapa.</p><input x-model="confirmation" placeholder="Shtyp {{ $item->id }} për konfirmim" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                        <x-slot name="footer"><x-button variant="gray" @click="on = false">Anulo</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->id }}'" wire:click="$parent.deleteMessageQueue('{{ $item->id }}')" @click="on = false">Fshi</x-button></x-slot>
                    </x-modal>
                </div>
            @endcan
        </div>
    </td>
</tr>
