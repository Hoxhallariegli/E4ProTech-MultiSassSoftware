<tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition border-b border-gray-50 dark:border-gray-700/50 last:border-none">
    <!-- Mobile & Tablet Compact Cell (< lg) -->
    <td colspan="100" class="p-4 space-y-2 lg:hidden">
        <div class="flex items-center justify-between gap-2 text-xs">
            <span class="font-extrabold text-gray-900 dark:text-white truncate">
                {{ $item->barberShop?->name ?? 'Sallon' }}
            </span>
            <span class="px-2 py-0.5 text-[10px] font-black uppercase rounded-full shrink-0 {{ $item->status === 'completed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : ($item->status === 'contacted' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300') }}">
                {{ $item->status === 'completed' ? 'E Përfunduar' : ($item->status === 'contacted' ? 'U Kontaktua' : 'Në Pritje') }}
            </span>
        </div>

        <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 font-medium">
            <span>Domain: <strong class="font-mono text-indigo-600 dark:text-indigo-400">{{ $item->desired_domain ?: '-' }}</strong></span>
            <span>Tel: {{ $item->phone ?: '-' }}</span>
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800 text-[10px]">
            <span class="text-gray-400 font-mono">{{ $item->created_at?->format('d/m/Y H:i') }}</span>
            <div class="flex items-center gap-2">
                <x-a href="{{ route('admin.website-offer-requests.edit', $item) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-3 !py-1 !text-[10px] !font-black !uppercase !border-none">Edit</x-a>

                <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                    <x-modal>
                        <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-500 hover:text-red-700">Delete</button></x-slot>
                        <x-slot name="modalTitle"><div class="text-left dark:text-white">Delete Kërkesën #{{ $item->id }}?</div></x-slot>
                        <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">Ky veprim nuk mund të kthehet mbrapa.</p><input x-model="confirmation" placeholder="Shkruaj {{ $item->id }} për konfirmim" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                        <x-slot name="footer"><x-button variant="gray" @click="on = false">Anulo</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->id }}'" wire:click="$parent.deleteRequest('{{ $item->id }}')" @click="on = false">Fshi</x-button></x-slot>
                    </x-modal>
                </div>
            </div>
        </div>
    </td>

    <!-- Desktop Table Cells (>= lg) -->
    <td class="hidden lg:table-cell px-6 py-5 font-bold text-blue-600 dark:text-blue-400">#{{ $item->id }}</td>
    <td class="hidden lg:table-cell px-6 py-5 font-extrabold text-gray-900 dark:text-white">
        <div>{{ $item->barberShop?->name ?? 'Sallon' }}</div>
        <div class="text-[11px] font-medium text-gray-400 dark:text-gray-500">{{ $item->user?->name ?? 'Përdorues' }} ({{ $item->user?->email }})</div>
    </td>
    <td class="hidden lg:table-cell px-6 py-5 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $item->desired_domain ?: '-' }}</td>
    <td class="hidden lg:table-cell px-6 py-5 font-bold text-gray-800 dark:text-gray-200">{{ $item->phone ?: '-' }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-xs text-gray-600 dark:text-gray-300 max-w-xs truncate">{{ $item->notes ?: '-' }}</td>
    <td class="hidden lg:table-cell px-6 py-5">
        <span class="px-3 py-1 text-[10px] font-black uppercase rounded-full {{ $item->status === 'completed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : ($item->status === 'contacted' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300') }}">
            {{ $item->status === 'completed' ? 'E Përfunduar' : ($item->status === 'contacted' ? 'U Kontaktua' : 'Në Pritje') }}
        </span>
    </td>
    <td class="hidden lg:table-cell px-6 py-5 text-xs font-mono text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $item->created_at?->format('d/m/Y H:i') }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-right">
        <div class="flex justify-end gap-2">
            <x-a href="{{ route('admin.website-offer-requests.edit', $item) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-3.5 !py-1.5 !text-[10px] !font-black !uppercase !border-none">Edit</x-a>

            <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                <x-modal>
                    <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-500 hover:text-red-700">Delete</button></x-slot>
                    <x-slot name="modalTitle"><div class="text-left dark:text-white">Delete Kërkesën #{{ $item->id }}?</div></x-slot>
                    <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">Ky veprim nuk mund të kthehet mbrapa.</p><input x-model="confirmation" placeholder="Shkruaj {{ $item->id }} për konfirmim" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                    <x-slot name="footer"><x-button variant="gray" @click="on = false">Anulo</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->id }}'" wire:click="$parent.deleteRequest('{{ $item->id }}')" @click="on = false">Fshi</x-button></x-slot>
                </x-modal>
            </div>
        </div>
    </td>
</tr>
