<tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-none border-b border-gray-50 dark:border-gray-700/50 last:border-none">
    <td class="px-6 py-5 font-bold text-blue-600 dark:text-blue-400">{{ $item->id }}</td>
    @if(auth()->user()->is_global_admin)
        <td class="px-6 py-5 font-bold text-gray-900 dark:text-white">{{ $item->barberShop?->name ?? '-' }}</td>
    @endif
    <td class="px-6 py-5">
        <div class="font-bold text-sm text-gray-900 dark:text-white">{{ $item->device_name ?? 'Pajisje Mobile' }}</div>
        <div class="text-xs text-gray-500 dark:text-gray-400 font-normal">Përdoruesi: {{ $item->user?->name ?? ('ID #' . $item->user_id) }}</div>
    </td>
    <td class="px-6 py-5">
        @if($item->is_sms_gateway)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-black text-emerald-700 bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300 rounded-xl border border-emerald-300 dark:border-emerald-700">
                📱 SMS GATEWAY (Aktiv)
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-gray-500 bg-gray-100 dark:bg-gray-700 dark:text-gray-300 rounded-xl">
                Njoftime Push
            </span>
        @endif
    </td>
    <td class="px-6 py-5 text-gray-600 dark:text-gray-300 font-bold uppercase text-xs">{{ $item->platform }}</td>
    <td class="px-6 py-5 text-gray-600 dark:text-gray-300 text-xs">{{ $item->last_used_at?->format('d/m/Y H:i') ?? '-' }}</td>
    <td class="px-6 py-5 text-right !transition-none">
        <div class="flex justify-end gap-2 !transition-none">
            @can('edit_device_tokens')
                <button wire:click="$parent.toggleGateway({{ $item->id }})" class="!rounded-xl {{ $item->is_sms_gateway ? '!bg-emerald-100 !text-emerald-800 dark:!bg-emerald-900/50 dark:!text-emerald-200' : '!bg-gray-100 dark:!bg-gray-700 !text-gray-700 dark:!text-gray-200' }} !px-3 !py-1.5 !text-[10px] !font-black !uppercase !border-none transition-transform active:scale-95">
                    {{ $item->is_sms_gateway ? '✅ Gateway' : '📱 Bëj Gateway' }}
                </button>
            @endcan
            @can('edit_device_tokens')
                <x-a href="{{ route('admin.device-tokens.edit', $item) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-3 !py-1.5 !text-[10px] !font-black !uppercase !border-none">Edit</x-a>
            @endcan
            @can('delete_device_tokens')
                <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                    <x-modal>
                        <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">Delete</button></x-slot>
                        <x-slot name="modalTitle"><div class="text-left dark:text-white">Delete {{ $item->id }}?</div></x-slot>
                        <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p><input x-model="confirmation" placeholder="Type {{ $item->id }} to confirm" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                        <x-slot name="footer"><x-button variant="gray" @click="on = false">Cancel</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->id }}'" wire:click="$parent.deleteDeviceToken('{{ $item->id }}')" @click="on = false">Delete</x-button></x-slot>
                    </x-modal>
                </div>
            @endcan
        </div>
    </td>
</tr>
