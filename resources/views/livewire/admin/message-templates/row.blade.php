<tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-none border-b border-gray-50 dark:border-gray-700/50 last:border-none">
    <td class="px-6 py-5 font-bold text-blue-600 dark:text-blue-400">{{ $item->id }}</td>
    @if(auth()->user()->is_global_admin)
<td class="px-6 py-5 font-bold text-gray-900 dark:text-white">{{ $item->barberShop?->name ?? '-' }}</td>
@endif
<td class="px-6 py-5 text-gray-600 dark:text-gray-300 font-bold uppercase text-xs">{{ $item->channel }}</td>
<td class="px-6 py-5 text-gray-600 dark:text-gray-300 font-bold uppercase text-xs">{{ $item->type }}</td>
<td class="px-6 py-5 text-gray-600 dark:text-gray-300">
    @php
        $raw = $item->content;
        $sq = ''; $en = '';
        if (is_array($raw)) {
            $sq = $raw['sq'] ?? '';
            $en = $raw['en'] ?? '';
        } elseif (is_string($raw)) {
            $dec = json_decode($raw, true);
            if (is_array($dec)) {
                $sq = $dec['sq'] ?? '';
                $en = $dec['en'] ?? '';
            } else {
                $sq = $raw;
            }
        }
    @endphp
    @if($sq)
        <div class="text-xs font-semibold text-gray-900 dark:text-white">🇦🇱 {{ $sq }}</div>
    @endif
    @if($en)
        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">🇬🇧 {{ $en }}</div>
    @endif
</td>
    <td class="px-6 py-5 text-right !transition-none">
        <div class="flex justify-end gap-3 !transition-none">
            @can('edit_message_templates')
                <x-a href="{{ route('admin.message-templates.edit', $item) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-4 !py-1.5 !text-[10px] !font-black !uppercase !border-none">Edit</x-a>
            @endcan
            @can('delete_message_templates')
                <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                    <x-modal>
                        <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">Delete</button></x-slot>
                        <x-slot name="modalTitle"><div class="text-left dark:text-white">Delete {{ $item->id }}?</div></x-slot>
                        <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p><input x-model="confirmation" placeholder="Type {{ $item->id }} to confirm" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                        <x-slot name="footer"><x-button variant="gray" @click="on = false">Cancel</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->id }}'" wire:click="$parent.deleteMessageTemplate('{{ $item->id }}')" @click="on = false">Delete</x-button></x-slot>
                    </x-modal>
                </div>
            @endcan
        </div>
    </td>
</tr>
