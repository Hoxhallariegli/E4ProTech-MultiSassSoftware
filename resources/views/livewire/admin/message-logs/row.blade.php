<tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-none border-b border-gray-50 dark:border-gray-700/50 last:border-none">
    <td class="px-6 py-5 font-bold text-blue-600 dark:text-blue-400">{{ $item->id }}</td>
    @if(auth()->user()->is_global_admin)
        <td class="px-6 py-5 font-bold text-gray-900 dark:text-white">{{ $item->barberShop?->name ?? '-' }}</td>
    @endif
    <td class="px-6 py-5">
        <div class="font-bold text-gray-900 dark:text-white text-sm">{{ $item->customer?->name ?? '-' }}</div>
        @if($item->customer?->phone)
            <div class="text-xs text-gray-500 font-mono">{{ $item->customer->phone }}</div>
        @endif
    </td>
    <td class="px-6 py-5">
        @if($item->resolved_template_type === 'reminder')
            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-amber-800 bg-amber-100 dark:bg-amber-900/40 dark:text-amber-300 rounded-lg border border-amber-300">
                ⏰ RIKUJTESË
            </span>
        @elseif($item->resolved_template_type === 'welcome')
            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-emerald-800 bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300 rounded-lg border border-emerald-300">
                👋 MIRËSEARDHJE
            </span>
        @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-blue-800 bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300 rounded-lg border border-blue-300">
                📌 KONFIRMIM
            </span>
        @endif
    </td>
    <td class="px-6 py-5">
        <div class="text-xs font-medium text-gray-800 dark:text-gray-200 max-w-md leading-relaxed bg-gray-50 dark:bg-gray-900/50 p-2.5 rounded-xl border border-gray-100 dark:border-gray-700">
            {{ $item->message }}
        </div>
    </td>
    <td class="px-6 py-5 text-xs text-gray-700 dark:text-gray-300 font-medium whitespace-nowrap">
        {{ $item->created_at?->format('d/m/Y H:i') ?? '-' }}
    </td>
    <td class="px-6 py-5 text-xs text-emerald-700 dark:text-emerald-400 font-bold whitespace-nowrap">
        {{ $item->sent_at?->format('d/m/Y H:i') ?? '-' }}
    </td>
    <td class="px-6 py-5 whitespace-nowrap">
        @if($item->status === 'sent')
            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-emerald-800 bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300 rounded-lg">
                ✅ SENT
            </span>
        @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-red-800 bg-red-100 dark:bg-red-900/40 dark:text-red-300 rounded-lg">
                ❌ FAILED
            </span>
        @endif
    </td>
    <td class="px-6 py-5 text-right !transition-none">
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
