<tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-none border-b border-gray-50 dark:border-gray-700/50 last:border-none">
    <td class="px-6 py-5 font-bold text-gray-900 dark:text-white">{{ $item->barber?->name ?? '-' }}</td>
<td class="px-6 py-5 font-bold text-gray-900 dark:text-white">{{ $item->service?->name ?? '-' }}</td>
<td class="px-6 py-5 font-bold text-gray-900 dark:text-white">{{ $item->customer?->name ?? '-' }}</td>
<td class="px-6 py-5 text-gray-600 dark:text-gray-300 font-medium">
    @php
        $duration = $item->service?->duration_minutes ?? 30;
        $notes = $item->notes ?? '';
        if (str_starts_with($notes, 'Shërbimet: ')) {
            $names = array_map('trim', explode('+', trim(substr($notes, strlen('Shërbimet: ')))));
            $sum = \App\Models\Service::where('barber_shop_id', $item->barber_shop_id)
                ->whereIn('name', $names)
                ->sum('duration_minutes');
            if ($sum > 0) {
                $duration = (int) $sum;
            }
        }
        $endTime = $item->appointment_at?->copy()->addMinutes($duration);
    @endphp
    @if($item->appointment_at)
        <div>{{ $item->appointment_at->format('d/m/Y H:i') }} @if($endTime) - {{ $endTime->format('H:i') }} @endif</div>
        <div class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold">{{ $duration }} minuta</div>
    @else
        -
    @endif
</td>
<td class="px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->status }}</td>
<td class="px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->total_price }}</td>
<td class="px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->notes }}</td>
<td class="px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->source }}</td>
    <td class="px-6 py-5 text-right !transition-none">
        <div class="flex justify-end gap-3 !transition-none">
            @can('edit_bookings')
                <x-a href="{{ route('admin.bookings.edit', $item) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-4 !py-1.5 !text-[10px] !font-black !uppercase !border-none">Edit</x-a>
            @endcan
            @can('delete_bookings')
                <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                    <x-modal>
                        <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">Delete</button></x-slot>
                        <x-slot name="modalTitle"><div class="text-left dark:text-white">Delete {{ $item->id }}?</div></x-slot>
                        <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p><input x-model="confirmation" placeholder="Type {{ $item->id }} to confirm" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                        <x-slot name="footer"><x-button variant="gray" @click="on = false">Cancel</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->id }}'" wire:click="$parent.deleteBooking('{{ $item->id }}')" @click="on = false">Delete</x-button></x-slot>
                    </x-modal>
                </div>
            @endcan
        </div>
    </td>
</tr>
