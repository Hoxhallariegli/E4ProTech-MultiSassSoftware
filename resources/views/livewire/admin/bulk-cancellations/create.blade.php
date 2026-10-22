<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1">
        <div>
            <x-h1>{{ __('bulk-cancellations.Add BulkCancellation') }}</x-h1>
            <x-short-description class="dark:text-gray-400">{{ __('bulk-cancellations.New record') }}</x-short-description>
        </div>
        <x-back-btn route="admin.bulk-cancellations.index" />
    </div>

    @include('errors.errors')

    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700 space-y-10">
        <form wire:submit.prevent="store" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                <div>
                    @if(auth()->user()->barber_shop_id && !auth()->user()->is_global_admin)
                        <x-form.input name="barber_shop_name" :label="__('bulk-cancellations.Barber Shop Id')" value="{{ auth()->user()->barberShop->name }}" readonly disabled class="bg-gray-50" />
                        <input type="hidden" wire:model="barber_shop_id">
                    @else
                        <div class="flex items-end gap-2">
                            <div class="flex-1"><x-form.dropdown-search name="barber_shop_id" wire:model.live="barber_shop_id" :label="__('bulk-cancellations.Barber Shop Id')" :data="$barberShops" /></div>
                            <x-modal>
                                <x-slot name="trigger"><button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button></x-slot>
                                <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Add New BarberShop</div></x-slot>
                                <x-slot name="content"><livewire:admin.barber-shops.quick-create /></x-slot>
                            </x-modal>
                        </div>
                    @endif
                </div>

                <div>
                    <div class="flex items-end gap-2">
                        <div class="flex-1"><x-form.dropdown-search name="barber_id" wire:model.live="barber_id" :label="__('bulk-cancellations.Barber Id')" :data="$barbers" /></div>
                        <x-modal>
                            <x-slot name="trigger"><button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button></x-slot>
                            <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Add New Barber</div></x-slot>
                            <x-slot name="content"><livewire:admin.barbers.quick-create /></x-slot>
                        </x-modal>
                    </div>
                </div>

                <div><x-form.input name="start_at" type="datetime-local" wire:model.live="start_at" :label="__('bulk-cancellations.Start At')" class="dark:bg-gray-900 font-bold" /></div>
                <div><x-form.input name="end_at" type="datetime-local" wire:model.live="end_at" :label="__('bulk-cancellations.End At')" class="dark:bg-gray-900 font-bold" /></div>

                <div class="md:col-span-2">
                    <x-form.input name="reason" type="text" wire:model.live="reason" :label="__('bulk-cancellations.Reason')" placeholder="P.sh. Emergjencë Familjare / Problem me shëndetin" class="dark:bg-gray-900" />
                </div>
            </div>

            {{-- LIVE PREVIEW OF AFFECTED BOOKINGS & SMS RATE-LIMITING SCHEDULE --}}
            @if($this->barber_id && $start_at && $end_at)
                <div class="mt-8 p-6 bg-gray-50 dark:bg-gray-900/70 rounded-3xl border border-gray-100 dark:border-gray-800 space-y-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-black">
                                <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
                            </div>
                            <div>
                                <h4 class="font-extrabold text-base text-gray-900 dark:text-white">
                                    {{ $affectedBookings->count() }} {{ __('Takime Aktive do të Anulohen') }}
                                </h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-0.5">
                                    {{ __('SMS-të do të dërgohen te Message Queue me radhë (1 SMS për çdo minutë) për të parandaluar bllokimin nga operatorët.') }}
                                </p>
                            </div>
                        </div>
                        <div class="px-4 py-2 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 font-black text-xs rounded-2xl border border-amber-200/50">
                            {{ $affectedBookings->count() }} {{ __('Klientë në Radhë') }}
                        </div>
                    </div>

                    @if($affectedBookings->isNotEmpty())
                        <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700">
                            <table class="w-full text-xs text-left text-gray-600 dark:text-gray-300">
                                <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 uppercase font-black tracking-wider border-b border-gray-200 dark:border-gray-700">
                                    <tr>
                                        <th class="px-4 py-3">Radha SMS</th>
                                        <th class="px-4 py-3">Ora e Takimit</th>
                                        <th class="px-4 py-3">Klienti</th>
                                        <th class="px-4 py-3">Telefon</th>
                                        <th class="px-4 py-3">Shërbimi</th>
                                        <th class="px-4 py-3 text-right">Dërgimi i SMS (1/min)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                                    @foreach($affectedBookings as $index => $b)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50">
                                            <td class="px-4 py-3 font-black text-blue-600 dark:text-blue-400">#{{ $index + 1 }}</td>
                                            <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">{{ $b->appointment_at?->format('H:i d/m/Y') }}</td>
                                            <td class="px-4 py-3 font-bold">{{ $b->customer?->name ?? 'Klient' }}</td>
                                            <td class="px-4 py-3 font-mono text-gray-500">{{ $b->customer?->phone ?? '-' }}</td>
                                            <td class="px-4 py-3">{{ $b->service?->name ?? 'Shërbim' }}</td>
                                            <td class="px-4 py-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                                +{{ $index }} min ({{ now()->addMinutes($index)->format('H:i') }})
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-6 text-center text-sm font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50/50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100 dark:border-emerald-800/30">
                            ✓ Nuk u gjet asnjë takim aktiv në këtë orar. Asnjë klient nuk do të anulohet.
                        </div>
                    @endif
                </div>
            @endif

            <div class="mt-10 flex justify-end">
                <x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl font-black">
                    {{ __('bulk-cancellations.Save') }}
                </x-button>
            </div>
        </form>
    </div>
</div>
