<tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-none border-b border-gray-50 dark:border-gray-700/50 last:border-none">
    <td class="px-6 py-4 font-black text-xs text-blue-600 dark:text-blue-400">#{{ $item->id }}</td>
    <td class="px-6 py-4">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">
                🏪
            </div>
            <div>
                <div class="font-extrabold text-sm text-gray-900 dark:text-white">
                    {{ $item->barberShop?->name ?? 'Salloni #' . $item->barber_shop_id }}
                </div>
                <div class="text-xs text-gray-400 font-medium">
                    {{ $item->plan?->name ?? 'Plani i Abonimit' }}
                </div>
            </div>
        </div>
    </td>
    <td class="px-6 py-4 font-bold text-xs text-gray-700 dark:text-gray-300">
        @switch($item->payment_method)
            @case('bank_transfer')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">🏦 Transfertë Bankare</span>
                @break
            @case('cash')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">💵 Cash (Në Dorë)</span>
                @break
            @case('card')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">💳 Kartë Kreditit</span>
                @break
            @default
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">🌐 Online</span>
        @endswitch
    </td>
    <td class="px-6 py-3">
        @if($item->transfer_document)
            <a href="{{ asset(ltrim($item->transfer_document, '/')) }}" target="_blank" rel="noopener" class="inline-block group" title="Kliko për të parë dokumentin e plotë">
                <img src="{{ asset(ltrim($item->transfer_document, '/')) }}" alt="Mandat Pagesa" class="w-11 h-11 rounded-xl object-cover border-2 border-gray-200 dark:border-gray-700 shadow-sm group-hover:scale-105 transition-transform duration-150" loading="lazy" />
            </a>
        @else
            <span class="text-xs text-gray-400 italic">Pa Faturë</span>
        @endif
    </td>
    <td class="px-6 py-4 font-black text-sm text-emerald-600 dark:text-emerald-400">
        {{ number_format($item->amount ?? 0, 2) }} €
    </td>
    <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400 max-w-xs truncate">
        {{ $item->notes ?: '-' }}
    </td>
    <td class="px-6 py-4">
        @switch($item->status)
            @case('approved')
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">✅ I MIRATUAR</span>
                @break
            @case('rejected')
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">❌ I REFUZUAR</span>
                @break
            @default
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">⏳ NË PRITJE</span>
        @endswitch
    </td>
    <td class="px-6 py-4 text-right !transition-none">
        <div class="flex justify-end gap-3 !transition-none">
            @can('edit_subscription_renewals')
                <x-a href="{{ route('admin.subscription-renewals.edit', $item) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-4 !py-1.5 !text-[10px] !font-black !uppercase !border-none">Edito / Shqyrto</x-a>
            @endcan
            @can('delete_subscription_renewals')
                <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                    <x-modal>
                        <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">Fshi</button></x-slot>
                        <x-slot name="modalTitle"><div class="text-left dark:text-white">Fshi kërkesën #{{ $item->id }}?</div></x-slot>
                        <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">Ky veprim nuk mund të kthehet prapa.</p><input x-model="confirmation" placeholder="Shkruaj {{ $item->id }} për konfirmim" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                        <x-slot name="footer"><x-button variant="gray" @click="on = false">Anulo</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->id }}'" wire:click="$parent.deleteSubscriptionRenewal('{{ $item->id }}')" @click="on = false">Fshi</x-button></x-slot>
                    </x-modal>
                </div>
            @endcan
        </div>
    </td>
</tr>
