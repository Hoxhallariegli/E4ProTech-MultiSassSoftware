<tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-none border-b border-gray-50 dark:border-gray-700/50 last:border-none">
    <!-- Mobile & Tablet Compact Cell (< lg) -->
    <td colspan="100" class="p-4 space-y-2 lg:hidden">
        <div class="flex items-center justify-between gap-2 text-xs">
            <span class="font-extrabold text-gray-900 dark:text-white truncate">
                {{ $item->barberShop?->name ?? 'Salon #' . $item->barber_shop_id }}
            </span>
            <span class="font-extrabold text-emerald-600 dark:text-emerald-400 font-mono shrink-0">
                {{ number_format($item->amount ?? 0, 2) }} €
            </span>
        </div>

        <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 font-medium">
            <span>{{ __('subscription-renewals.Selected Plan') }}: <strong>{{ $item->plan?->name ?? 'Subscription' }}</strong></span>
            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase shrink-0 {{ $item->status === 'approved' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : ($item->status === 'rejected' ? 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300') }}">
                {{ $item->status === 'approved' ? __('subscription-renewals.Approved') : ($item->status === 'rejected' ? __('subscription-renewals.Rejected') : __('subscription-renewals.Pending')) }}
            </span>
        </div>

        <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 font-medium">
            <span>Krijuar: <strong>{{ $item->created_at?->format('d/m/Y H:i') }}</strong></span>
            @if($item->status === 'approved' && $item->barberShop?->expires_at)
                <span class="text-emerald-600 dark:text-emerald-400 font-bold font-mono">{{ __('subscription-renewals.Expires') }}: {{ $item->barberShop->expires_at->format('d/m/Y') }}</span>
            @endif
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800 text-[10px]">
            <span class="text-gray-400 font-mono">{{ __('subscription-renewals.Payment Method') }}: {{ $item->payment_method }}</span>
            <div class="flex items-center gap-2">
                @if(auth()->user()->is_global_admin || $item->status === 'pending')
                    @can('edit_subscription_renewals')
                        <x-a href="{{ route('admin.subscription-renewals.edit', $item) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-3 !py-1 !text-[10px] !font-black !uppercase !border-none">{{ __('admin.Edit') }}</x-a>
                    @endcan
                    @can('delete_subscription_renewals')
                        <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                            <x-modal>
                                <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">{{ __('admin.Delete') }}</button></x-slot>
                                <x-slot name="modalTitle"><div class="text-left dark:text-white">{{ __('admin.Delete') }} #{{ $item->id }}?</div></x-slot>
                                <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p><input x-model="confirmation" placeholder="Type {{ $item->id }} to confirm" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                                <x-slot name="footer"><x-button variant="gray" @click="on = false">{{ __('admin.Cancel') }}</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->id }}'" wire:click="$parent.deleteSubscriptionRenewal('{{ $item->id }}')" @click="on = false">{{ __('admin.Delete') }}</x-button></x-slot>
                            </x-modal>
                        </div>
                    @endcan
                @endif
            </div>
        </div>
    </td>

    <!-- Desktop Table Cells (>= lg) -->
    <td class="hidden lg:table-cell px-6 py-4 font-black text-xs text-blue-600 dark:text-blue-400">#{{ $item->id }}</td>
    <td class="hidden lg:table-cell px-6 py-4">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">
                S
            </div>
            <div>
                <div class="font-extrabold text-sm text-gray-900 dark:text-white">
                    {{ $item->barberShop?->name ?? 'Salon #' . $item->barber_shop_id }}
                </div>
                <div class="text-xs text-gray-400 font-medium">
                    {{ $item->plan?->name ?? 'Plan' }}
                </div>
            </div>
        </div>
    </td>
    <td class="hidden lg:table-cell px-6 py-4 font-bold text-xs text-gray-700 dark:text-gray-300">
        @switch($item->payment_method)
            @case('cash')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ __('subscription-renewals.Cash') }}</span>
                @break
            @case('card')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">{{ __('subscription-renewals.Credit/Debit Card') }}</span>
                @break
            @default
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">{{ __('subscription-renewals.Online Payment') }}</span>
        @endswitch
    </td>
    <td class="hidden lg:table-cell px-6 py-3">
        @if($item->transfer_document)
            <a href="{{ asset(ltrim($item->transfer_document, '/')) }}" target="_blank" rel="noopener" class="inline-block group" title="View Document">
                <img src="{{ asset(ltrim($item->transfer_document, '/')) }}" alt="Invoice" class="w-11 h-11 rounded-xl object-cover border-2 border-gray-200 dark:border-gray-700 shadow-sm group-hover:scale-105 transition-transform duration-150" loading="lazy" />
            </a>
        @else
            <span class="text-xs text-gray-400 italic">-</span>
        @endif
    </td>
    <td class="hidden lg:table-cell px-6 py-4 font-black text-sm text-emerald-600 dark:text-emerald-400">
        {{ number_format($item->amount ?? 0, 2) }} €
    </td>
    <td class="hidden lg:table-cell px-6 py-4 text-xs text-gray-500 dark:text-gray-400 max-w-xs truncate">
        {{ $item->notes ?: '-' }}
    </td>
    <td class="hidden lg:table-cell px-6 py-4">
        @switch($item->status)
            @case('approved')
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">{{ __('subscription-renewals.Approved') }}</span>
                @break
            @case('rejected')
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">{{ __('subscription-renewals.Rejected') }}</span>
                @break
            @default
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">{{ __('subscription-renewals.Pending') }}</span>
        @endswitch
    </td>
    <td class="hidden lg:table-cell px-6 py-4">
        <div class="space-y-0.5">
            <div class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1">
                <x-heroicon-o-calendar class="w-3.5 h-3.5 text-blue-500 shrink-0" />
                <span>{{ $item->created_at?->format('d/m/Y H:i') }}</span>
            </div>
            @php
                $months = 1;
                if (!empty($item->notes) && preg_match('/(\d+)\s*(months|muaj)/i', (string) $item->notes, $matches)) {
                    $months = (int) $matches[1];
                } elseif ($item->plan?->duration_months) {
                    $months = (int) $item->plan->duration_months;
                }
            @endphp
            <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400">
                <span>{{ $months }} {{ $months == 12 ? 'Vit (12 Muaj)' : ($months == 24 ? 'Vite (24 Muaj)' : 'Muaj') }}</span>
                @if($item->status === 'approved' && $item->barberShop?->expires_at)
                    <span class="text-emerald-600 dark:text-emerald-400 font-mono font-bold block">
                        {{ __('subscription-renewals.Expires') }}: {{ $item->barberShop->expires_at->format('d/m/Y') }}
                    </span>
                @endif
            </div>
        </div>
    </td>
    <td class="hidden lg:table-cell px-6 py-4 text-right !transition-none">
        <div class="flex justify-end gap-3 !transition-none">
            @if(auth()->user()->is_global_admin || $item->status === 'pending')
                @can('edit_subscription_renewals')
                    <x-a href="{{ route('admin.subscription-renewals.edit', $item) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-4 !py-1.5 !text-[10px] !font-black !uppercase !border-none">{{ __('admin.Edit') }}</x-a>
                @endcan
                @can('delete_subscription_renewals')
                    <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                        <x-modal>
                            <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">{{ __('admin.Delete') }}</button></x-slot>
                            <x-slot name="modalTitle"><div class="text-left dark:text-white">{{ __('admin.Delete') }} #{{ $item->id }}?</div></x-slot>
                            <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p><input x-model="confirmation" placeholder="Type {{ $item->id }} to confirm" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                            <x-slot name="footer"><x-button variant="gray" @click="on = false">{{ __('admin.Cancel') }}</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->id }}'" wire:click="$parent.deleteSubscriptionRenewal('{{ $item->id }}')" @click="on = false">{{ __('admin.Delete') }}</x-button></x-slot>
                        </x-modal>
                    </div>
                @endcan
            @endif
        </div>
    </td>
</tr>
