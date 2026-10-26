<tr class="hover:bg-gray-50/80 dark:hover:bg-gray-900/60 transition-colors border-b border-gray-200 dark:border-gray-700/80 last:border-none">
    <!-- Mobile & Tablet Compact Cell (< lg) -->
    <td colspan="100" class="p-4 space-y-2 lg:hidden">
        <div class="flex items-center justify-between gap-2 text-xs">
            <span class="font-extrabold text-gray-900 dark:text-white truncate">
                {{ $item->customer?->name ?? 'Klient Publik' }}
            </span>
            <span class="font-extrabold text-emerald-600 dark:text-emerald-400 font-mono shrink-0">
                {{ format_currency($item->total_price, $item->barberShop) }}
            </span>
        </div>

        <div class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-2 flex-wrap">
            <span class="text-blue-600 dark:text-blue-400">{{ $item->service?->name ?? '-' }}</span>
            <span class="text-gray-400">•</span>
            <span class="text-gray-600 dark:text-gray-400">{{ $item->barber?->name ?? '-' }}</span>
        </div>

        <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 font-medium pt-1">
            <span class="font-mono">
                @if($item->appointment_at) {{ $item->appointment_at->format('d/m/Y H:i') }} @else - @endif
            </span>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $item->status === 'completed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : ($item->status === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300') }}">
                {{ $item->status }}
            </span>
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800 text-[10px]">
            <span class="text-gray-400 font-mono">Burimi: {{ $item->source ?? 'Online' }}</span>
            <div class="flex items-center gap-2">
                @if($item->status !== 'completed' && $item->status !== 'cancelled')
                    @can('edit_bookings')
                        <div x-data="{ payMethod: 'cash', payAmount: '{{ $item->total_price }}' }" x-cloak class="inline-block">
                            <x-modal>
                                <x-slot name="trigger">
                                    <button @click="on = true" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-[10px] font-black uppercase shadow-xs transition-all hover:scale-105">
                                        <x-heroicon-o-credit-card class="w-3.5 h-3.5 shrink-0" />
                                        <span>{{ __('bookings.Pay Now') }}</span>
                                    </button>
                                </x-slot>
                                <x-slot name="modalTitle">
                                    <div class="text-left font-black text-gray-900 dark:text-white flex items-center gap-2">
                                        <x-heroicon-o-banknotes class="w-5 h-5 text-emerald-500" />
                                        <span>{{ __('bookings.Pay Now') }} - #{{ $item->id }}</span>
                                    </div>
                                </x-slot>
                                <x-slot name="content">
                                    <div class="text-left space-y-4">
                                        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-2xl">
                                            <div class="text-xs font-bold text-emerald-900 dark:text-emerald-300">
                                                {{ $item->customer?->name ?? 'Klient' }} • {{ $item->service?->name ?? '-' }}
                                            </div>
                                            <div class="text-lg font-black text-emerald-600 dark:text-emerald-400 mt-1">
                                                {{ format_currency($item->total_price, $item->barberShop) }}
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('bookings.Amount Received') }}</label>
                                            <input type="number" step="0.01" min="0" x-model="payAmount" class="w-full p-3.5 text-sm font-black bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl dark:text-white focus:ring-2 focus:ring-emerald-500 font-mono">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">{{ __('bookings.Payment Method') }}</label>
                                            <div class="grid grid-cols-2 gap-3">
                                                <button type="button" @click="payMethod = 'cash'" :class="payMethod === 'cash' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-black' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 font-bold'" class="p-3 rounded-2xl border text-xs flex items-center justify-center gap-2 transition-all">
                                                    <x-heroicon-o-banknotes class="w-4 h-4" />
                                                    <span>{{ __('bookings.Cash') }}</span>
                                                </button>
                                                <button type="button" @click="payMethod = 'card'" :class="payMethod === 'card' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-black' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 font-bold'" class="p-3 rounded-2xl border text-xs flex items-center justify-center gap-2 transition-all">
                                                    <x-heroicon-o-credit-card class="w-4 h-4" />
                                                    <span>{{ __('bookings.Card') }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </x-slot>
                                <x-slot name="footer">
                                    <x-button variant="gray" @click="on = false">{{ __('admin.Cancel') }}</x-button>
                                    <x-button variant="green" wire:click="payNow(payAmount, payMethod)" @click="on = false">
                                        {{ __('bookings.Confirm Payment') }}
                                    </x-button>
                                </x-slot>
                            </x-modal>
                        </div>
                    @endcan
                @endif
                @can('edit_bookings')
                    <x-a href="{{ route('admin.bookings.edit', $item) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-3 !py-1 !text-[10px] !font-black !uppercase !border-none">{{ __('admin.Edit') }}</x-a>
                @endcan
                @can('delete_bookings')
                    <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                        <x-modal>
                            <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">{{ __('admin.Delete') }}</button></x-slot>
                            <x-slot name="modalTitle"><div class="text-left dark:text-white">{{ __('admin.Delete') }} #{{ $item->id }}?</div></x-slot>
                            <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p><input x-model="confirmation" placeholder="Type {{ $item->id }} to confirm" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                            <x-slot name="footer"><x-button variant="gray" @click="on = false">{{ __('admin.Cancel') }}</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->id }}'" wire:click="$parent.deleteBooking('{{ $item->id }}')" @click="on = false">{{ __('admin.Delete') }}</x-button></x-slot>
                        </x-modal>
                    </div>
                @endcan
            </div>
        </div>
    </td>

    <!-- Desktop Table Cells (>= lg) -->
    <td class="hidden lg:table-cell px-6 py-5 font-bold text-gray-900 dark:text-white">{{ $item->barber?->name ?? '-' }}</td>
    <td class="hidden lg:table-cell px-6 py-5 font-bold text-gray-900 dark:text-white">{{ $item->service?->name ?? '-' }}</td>
    <td class="hidden lg:table-cell px-6 py-5 font-bold text-gray-900 dark:text-white">{{ $item->customer?->name ?? '-' }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300 font-medium">
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
    <td class="hidden lg:table-cell px-6 py-5">
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $item->status === 'completed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : ($item->status === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300') }}">
            {{ $item->status }}
        </span>
    </td>
    <td class="hidden lg:table-cell px-6 py-5 font-extrabold text-gray-900 dark:text-white">{{ format_currency($item->total_price, $item->barberShop) }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->notes ?: '-' }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->source ?: '-' }}</td>
    <td class="hidden lg:table-cell px-6 py-5 text-right !transition-none">
        <div class="flex justify-end gap-2.5 items-center !transition-none">
            @if($item->status !== 'completed' && $item->status !== 'cancelled')
                @can('edit_bookings')
                    <div x-data="{ payMethod: 'cash', payAmount: '{{ $item->total_price }}' }" x-cloak class="inline-block">
                        <x-modal>
                            <x-slot name="trigger">
                                <button @click="on = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-[10px] font-black uppercase shadow-xs transition-all hover:scale-105">
                                    <x-heroicon-o-credit-card class="w-3.5 h-3.5 shrink-0" />
                                    <span>{{ __('bookings.Pay Now') }}</span>
                                </button>
                            </x-slot>
                            <x-slot name="modalTitle">
                                <div class="text-left font-black text-gray-900 dark:text-white flex items-center gap-2">
                                    <x-heroicon-o-banknotes class="w-5 h-5 text-emerald-500" />
                                    <span>{{ __('bookings.Pay Now') }} - #{{ $item->id }}</span>
                                </div>
                            </x-slot>
                            <x-slot name="content">
                                <div class="text-left space-y-4">
                                    <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-2xl">
                                        <div class="text-xs font-bold text-emerald-900 dark:text-emerald-300">
                                            {{ $item->customer?->name ?? 'Klient' }} • {{ $item->service?->name ?? '-' }}
                                        </div>
                                        <div class="text-lg font-black text-emerald-600 dark:text-emerald-400 mt-1">
                                            {{ format_currency($item->total_price, $item->barberShop) }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('bookings.Amount Received') }}</label>
                                        <input type="number" step="0.01" min="0" x-model="payAmount" class="w-full p-3.5 text-sm font-black bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl dark:text-white focus:ring-2 focus:ring-emerald-500 font-mono">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">{{ __('bookings.Payment Method') }}</label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <button type="button" @click="payMethod = 'cash'" :class="payMethod === 'cash' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-black' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 font-bold'" class="p-3 rounded-2xl border text-xs flex items-center justify-center gap-2 transition-all">
                                                <x-heroicon-o-banknotes class="w-4 h-4" />
                                                <span>{{ __('bookings.Cash') }}</span>
                                            </button>
                                            <button type="button" @click="payMethod = 'card'" :class="payMethod === 'card' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-black' : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 font-bold'" class="p-3 rounded-2xl border text-xs flex items-center justify-center gap-2 transition-all">
                                                <x-heroicon-o-credit-card class="w-4 h-4" />
                                                <span>{{ __('bookings.Card') }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </x-slot>
                            <x-slot name="footer">
                                <x-button variant="gray" @click="on = false">{{ __('admin.Cancel') }}</x-button>
                                <x-button variant="green" wire:click="payNow(payAmount, payMethod)" @click="on = false">
                                    {{ __('bookings.Confirm Payment') }}
                                </x-button>
                            </x-slot>
                        </x-modal>
                    </div>
                @endcan
            @endif
            @can('edit_bookings')
                <x-a href="{{ route('admin.bookings.edit', $item) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-4 !py-1.5 !text-[10px] !font-black !uppercase !border-none">{{ __('admin.Edit') }}</x-a>
            @endcan
            @can('delete_bookings')
                <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                    <x-modal>
                        <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">{{ __('admin.Delete') }}</button></x-slot>
                        <x-slot name="modalTitle"><div class="text-left dark:text-white">{{ __('admin.Delete') }} #{{ $item->id }}?</div></x-slot>
                        <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p><input x-model="confirmation" placeholder="Type {{ $item->id }} to confirm" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                        <x-slot name="footer"><x-button variant="gray" @click="on = false">{{ __('admin.Cancel') }}</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->id }}'" wire:click="$parent.deleteBooking('{{ $item->id }}')" @click="on = false">{{ __('admin.Delete') }}</x-button></x-slot>
                    </x-modal>
                </div>
            @endcan
        </div>
    </td>
</tr>
