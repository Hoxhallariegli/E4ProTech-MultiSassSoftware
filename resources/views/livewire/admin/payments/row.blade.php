<tr class="hover:bg-gray-50/80 dark:hover:bg-gray-900/60 transition-colors border-b border-gray-200 dark:border-gray-700/80 last:border-none">
    <!-- Mobile & Tablet Compact Cell (< lg) -->
    <td colspan="100" class="p-4 space-y-2 lg:hidden">
        <div class="flex items-center justify-between gap-2 text-xs">
            <span class="font-extrabold text-gray-900 dark:text-white truncate">
                {{ $item->booking?->customer?->name ?? 'Klient Publik' }}
            </span>
            <span class="font-extrabold text-emerald-600 dark:text-emerald-400 font-mono shrink-0">
                {{ format_currency($item->amount, $item->barberShop) }}
            </span>
        </div>

        <div class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center justify-between gap-2">
            <span class="text-blue-600 dark:text-blue-400 truncate">{{ $item->booking?->service?->name ?? ('Takimi #' . ($item->booking_id ?? '-')) }}</span>
            <span class="text-xs font-black text-gray-900 dark:text-white shrink-0">Stafi: {{ $item->booking?->barber?->name ?? '-' }}</span>
        </div>

        <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 font-medium pt-1">
            <span class="font-extrabold uppercase text-[10px] px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                {{ $item->method === 'card' ? __('payments.Card') : __('payments.Cash') }}
            </span>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $item->status === 'paid' || $item->status === 'completed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' }}">
                {{ $item->status === 'paid' ? __('payments.Paid') : $item->status }}
            </span>
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800 text-[10px]">
            <span class="text-gray-400 font-mono">ID: #{{ $item->id }}</span>
            <div class="flex items-center gap-2">
                @can('edit_payments')
                    <x-a href="{{ route('admin.payments.edit', $item) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-3 !py-1 !text-[10px] !font-black !uppercase !border-none">{{ __('admin.Edit') }}</x-a>
                @endcan
                @can('delete_payments')
                    <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                        <x-modal>
                            <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">{{ __('admin.Delete') }}</button></x-slot>
                            <x-slot name="modalTitle"><div class="text-left dark:text-white">{{ __('admin.Delete') }} #{{ $item->id }}?</div></x-slot>
                            <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p><input x-model="confirmation" placeholder="Type {{ $item->id }} to confirm" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                            <x-slot name="footer"><x-button variant="gray" @click="on = false">{{ __('admin.Cancel') }}</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->id }}'" wire:click="$parent.deletePayment('{{ $item->id }}')" @click="on = false">{{ __('admin.Delete') }}</x-button></x-slot>
                        </x-modal>
                    </div>
                @endcan
            </div>
        </div>
    </td>

    <!-- Desktop Table Cells (>= lg) -->
    <td class="hidden lg:table-cell px-6 py-5 font-bold text-blue-600 dark:text-blue-400 font-mono">#{{ $item->id }}</td>
    @if(auth()->user()->is_global_admin)
        <td class="hidden lg:table-cell px-6 py-5 font-bold text-gray-900 dark:text-white">{{ $item->barberShop?->name ?? '-' }}</td>
    @endif
    <td class="hidden lg:table-cell px-6 py-5">
        <div class="font-extrabold text-gray-900 dark:text-white text-xs">
            {{ $item->booking?->customer?->name ?? 'Klient' }}
        </div>
        <div class="text-[11px] text-gray-400 font-medium">
            {{ $item->booking?->service?->name ?? ('Takimi #' . ($item->booking_id ?? '-')) }}
            @if($item->booking?->customer?->phone)
                • <span class="font-mono text-gray-500">{{ $item->booking->customer->phone }}</span>
            @endif
        </div>
    </td>
    <td class="hidden lg:table-cell px-6 py-5 font-bold text-gray-900 dark:text-white text-xs">
        <div class="flex items-center gap-1.5">
            <x-heroicon-o-user class="w-4 h-4 text-blue-500 shrink-0" />
            <span class="font-extrabold">{{ $item->booking?->barber?->name ?? '-' }}</span>
        </div>
    </td>
    <td class="hidden lg:table-cell px-6 py-5 font-black text-emerald-600 dark:text-emerald-400 font-mono text-base">
        {{ format_currency($item->amount, $item->barberShop) }}
    </td>
    <td class="hidden lg:table-cell px-6 py-5">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[10px] font-black uppercase bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
            @if(strtolower((string)$item->method) === 'card')
                <x-heroicon-o-credit-card class="w-3.5 h-3.5 text-blue-500" />
                <span>{{ __('payments.Card') }}</span>
            @else
                <x-heroicon-o-banknotes class="w-3.5 h-3.5 text-emerald-500" />
                <span>{{ __('payments.Cash') }}</span>
            @endif
        </span>
    </td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300 font-semibold text-xs">
        <div class="flex items-center gap-1.5">
            <x-heroicon-o-clock class="w-4 h-4 text-gray-400 shrink-0" />
            <span class="font-mono text-xs">{{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-' }}</span>
        </div>
    </td>
    <td class="hidden lg:table-cell px-6 py-5">
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $item->status === 'paid' || $item->status === 'completed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' }}">
            {{ $item->status === 'paid' ? __('payments.Paid') : $item->status }}
        </span>
    </td>
    <td class="hidden lg:table-cell px-6 py-5 text-right !transition-none">
        <div class="flex justify-end gap-3 !transition-none">
            @can('edit_payments')
                <x-a href="{{ route('admin.payments.edit', $item) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-4 !py-1.5 !text-[10px] !font-black !uppercase !border-none">{{ __('admin.Edit') }}</x-a>
            @endcan
            @can('delete_payments')
                <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                    <x-modal>
                        <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">{{ __('admin.Delete') }}</button></x-slot>
                        <x-slot name="modalTitle"><div class="text-left dark:text-white">{{ __('admin.Delete') }} #{{ $item->id }}?</div></x-slot>
                        <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p><input x-model="confirmation" placeholder="Type {{ $item->id }} to confirm" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                        <x-slot name="footer"><x-button variant="gray" @click="on = false">{{ __('admin.Cancel') }}</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->id }}'" wire:click="$parent.deletePayment('{{ $item->id }}')" @click="on = false">{{ __('admin.Delete') }}</x-button></x-slot>
                    </x-modal>
                </div>
            @endcan
        </div>
    </td>
</tr>
