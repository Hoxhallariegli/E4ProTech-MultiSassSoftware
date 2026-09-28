<tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-none border-b border-gray-50 dark:border-gray-700/50 last:border-none">
    <td class="px-6 py-5 font-bold text-blue-600 dark:text-blue-400">{{ $item->id }}</td>
    @if(auth()->user()->is_global_admin)
<td class="px-6 py-5 font-bold text-gray-900 dark:text-white">{{ $item->barberShop?->name ?? '-' }}</td>
@endif
<td class="px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->name }}</td>
<td class="px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->phone }}</td>
<td class="px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->email }}</td>
<td class="px-6 py-3">@if($item->photo) <a href="{{ asset(ltrim($item->photo, '/')) }}" target="_blank" rel="noopener" class="inline-block group" title="View image"><img src="{{ asset(ltrim($item->photo, '/')) }}" alt="{{ e(ucwords(str_replace('_', ' ', 'photo'))) }}" class="w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-gray-700 shadow-sm group-hover:scale-105 transition-transform duration-150" loading="lazy" /></a> @else <span class="text-gray-400">-</span> @endif</td>
<td class="px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->total_bookings }}</td>
<td class="px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->no_show_count }}</td>
<td class="px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->blocked_at?->format('d/m/Y H:i') ?? '-' }}</td>
    <td class="px-6 py-5 text-right !transition-none">
        <div class="flex justify-end gap-3 !transition-none">
            @can('edit_customers')
                <x-a href="{{ route('admin.customers.edit', $item) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-4 !py-1.5 !text-[10px] !font-black !uppercase !border-none">Edit</x-a>
            @endcan
            @can('delete_customers')
                <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                    <x-modal>
                        <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">Delete</button></x-slot>
                        <x-slot name="modalTitle"><div class="text-left dark:text-white">Delete {{ $item->name }}?</div></x-slot>
                        <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p><input x-model="confirmation" placeholder="Type {{ $item->name }} to confirm" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                        <x-slot name="footer"><x-button variant="gray" @click="on = false">Cancel</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->name }}'" wire:click="$parent.deleteCustomer('{{ $item->id }}')" @click="on = false">Delete</x-button></x-slot>
                    </x-modal>
                </div>
            @endcan
        </div>
    </td>
</tr>