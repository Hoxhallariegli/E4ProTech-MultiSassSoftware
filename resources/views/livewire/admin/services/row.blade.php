<tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-none border-b border-gray-50 dark:border-gray-700/50 last:border-none">
    <!-- Mobile & Tablet Compact Cell (< lg) -->
    <td colspan="100" class="p-4 space-y-2 lg:hidden">
        <div class="flex items-center justify-between gap-2 text-xs">
            <span class="font-extrabold text-gray-900 dark:text-white truncate">
                {{ $item->name }}
            </span>
            <span class="font-extrabold text-emerald-600 dark:text-emerald-400 font-mono shrink-0">
                {{ format_currency($item->price, $item->barberShop) }}
            </span>
        </div>

        @if($item->description)
            <p class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-1 font-medium">{{ $item->description }}</p>
        @endif

        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800 text-[10px]">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 font-bold">
                    {{ $item->duration_minutes }} min
                </span>
                @if($item->category)
                    <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 font-medium">
                        {{ $item->category }}
                    </span>
                @endif
            </div>

            <div class="flex items-center gap-2">
                @can('edit_services')
                    <x-a href="{{ route('admin.services.edit', $item) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-3 !py-1 !text-[10px] !font-black !uppercase !border-none">Edit</x-a>
                @endcan
                @can('delete_services')
                    <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                        <x-modal>
                            <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">Delete</button></x-slot>
                            <x-slot name="modalTitle"><div class="text-left dark:text-white">Delete {{ $item->name }}?</div></x-slot>
                            <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p><input x-model="confirmation" placeholder="Type {{ $item->name }} to confirm" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                            <x-slot name="footer"><x-button variant="gray" @click="on = false">Cancel</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->name }}'" wire:click="$parent.deleteService('{{ $item->id }}')" @click="on = false">Delete</x-button></x-slot>
                        </x-modal>
                    </div>
                @endcan
            </div>
        </div>
    </td>

    <!-- Desktop Table Cells (>= lg) -->
    <td class="hidden lg:table-cell px-6 py-5 font-bold text-blue-600 dark:text-blue-400">{{ $item->id }}</td>
    @if(auth()->user()->is_global_admin)
        <td class="hidden lg:table-cell px-6 py-5 font-bold text-gray-900 dark:text-white">{{ $item->barberShop?->name ?? '-' }}</td>
    @endif
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->name }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->description }}</td>
    <td class="hidden lg:table-cell px-6 py-5 font-extrabold text-gray-900 dark:text-white">{{ format_currency($item->price, $item->barberShop) }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->duration_minutes }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->category }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->active }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-right !transition-none">
        <div class="flex justify-end gap-3 !transition-none">
            @can('edit_services')
                <x-a href="{{ route('admin.services.edit', $item) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-4 !py-1.5 !text-[10px] !font-black !uppercase !border-none">Edit</x-a>
            @endcan
            @can('delete_services')
                <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                    <x-modal>
                        <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">Delete</button></x-slot>
                        <x-slot name="modalTitle"><div class="text-left dark:text-white">Delete {{ $item->name }}?</div></x-slot>
                        <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p><input x-model="confirmation" placeholder="Type {{ $item->name }} to confirm" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                        <x-slot name="footer"><x-button variant="gray" @click="on = false">Cancel</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->name }}'" wire:click="$parent.deleteService('{{ $item->id }}')" @click="on = false">Delete</x-button></x-slot>
                    </x-modal>
                </div>
            @endcan
        </div>
    </td>
</tr>
