<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1">
        <div>
            <x-h1>{{ __('bookings.Add Booking') }}</x-h1>
            <x-short-description class="dark:text-gray-400">{{ __('bookings.New record') }}</x-short-description>
        </div>
        <x-back-btn route="admin.bookings.index" />
    </div>

    @include('errors.errors')

    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700">
        <form wire:submit.prevent="store" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">

                {{-- Salon / Barber Shop --}}
                <div>
                    @if(auth()->user()->barber_shop_id && !auth()->user()->is_global_admin)
                        <x-form.input name="barber_shop_name" :label="__('bookings.Barber Shop Id')" value="{{ auth()->user()->barberShop?->name }}" readonly disabled class="bg-gray-50 dark:bg-gray-900" />
                        <input type="hidden" wire:model="barber_shop_id">
                    @else
                        <div class="flex items-end gap-2">
                            <div class="flex-1">
                                <x-form.dropdown-search name="barber_shop_id" wire:model.live="barber_shop_id" :label="__('bookings.Barber Shop Id')" :data="$barberShops" />
                            </div>
                            <x-modal>
                                <x-slot name="trigger">
                                    <button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button>
                                </x-slot>
                                <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Add New BarberShop</div></x-slot>
                                <x-slot name="content"><livewire:admin.barber-shops.quick-create /></x-slot>
                            </x-modal>
                        </div>
                    @endif
                </div>

                {{-- Barber / Stafi --}}
                <div>
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <x-form.dropdown-search name="barber_id" wire:model.live="barber_id" :label="__('bookings.Barber Id')" :data="$barbers" />
                        </div>
                        <x-modal>
                            <x-slot name="trigger">
                                <button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button>
                            </x-slot>
                            <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Add New Barber</div></x-slot>
                            <x-slot name="content"><livewire:admin.barbers.quick-create /></x-slot>
                        </x-modal>
                    </div>
                </div>

                {{-- Service / Shërbimi --}}
                <div>
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <x-form.dropdown-search name="service_id" wire:model.live="service_id" :label="__('bookings.Service Id')" :data="$services" />
                        </div>
                        <x-modal>
                            <x-slot name="trigger">
                                <button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button>
                            </x-slot>
                            <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Add New Service</div></x-slot>
                            <x-slot name="content"><livewire:admin.services.quick-create /></x-slot>
                        </x-modal>
                    </div>
                </div>

                {{-- Customer / Klienti --}}
                <div>
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <x-form.dropdown-search name="customer_id" wire:model.live="customer_id" :label="__('bookings.Customer Id')" :data="$customers" />
                        </div>
                        <x-modal>
                            <x-slot name="trigger">
                                <button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button>
                            </x-slot>
                            <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Add New Customer</div></x-slot>
                            <x-slot name="content"><livewire:admin.customers.quick-create /></x-slot>
                        </x-modal>
                    </div>
                </div>

                {{-- ADVANCED TIMELINE & SLOT OVERBOOKING CALCULATOR --}}
                <div class="md:col-span-2 bg-gray-50 dark:bg-gray-900/60 p-6 sm:p-8 rounded-3xl border border-gray-100 dark:border-gray-800 space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-800 pb-4">
                        <div>
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                                <span>⚡</span> Kalendari i Orareve (Min Service Time & Overbooking Detection)
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Hapi i orareve: <span class="font-bold text-blue-600 dark:text-blue-400">{{ $timeSlotData['slot_step'] ?? 15 }} min</span> (min_service_time) • Kohëzgjatja e shërbimit: <span class="font-bold text-blue-600 dark:text-blue-400">{{ $timeSlotData['req_duration'] ?? 30 }} min</span>
                            </p>
                        </div>

                        {{-- Quick Date Buttons --}}
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="selectQuickDate(0)" class="px-4 py-2 text-xs font-bold rounded-xl border transition-all {{ $booking_date === now()->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-500/20' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:border-blue-500' }}">
                                Sot
                            </button>
                            <button type="button" wire:click="selectQuickDate(1)" class="px-4 py-2 text-xs font-bold rounded-xl border transition-all {{ $booking_date === now()->addDays(1)->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-500/20' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:border-blue-500' }}">
                                Nesër
                            </button>
                            <button type="button" wire:click="selectQuickDate(2)" class="px-4 py-2 text-xs font-bold rounded-xl border transition-all {{ $booking_date === now()->addDays(2)->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-500/20' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:border-blue-500' }}">
                                Pasnesër
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <x-form.input name="booking_date" type="date" wire:model.live="booking_date" label="Data e Rezervimit" class="dark:bg-gray-900 font-bold" />

                            {{-- Legend --}}
                            <div class="mt-4 p-3 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-2 text-[11px]">
                                <div class="flex items-center gap-2 font-semibold">
                                    <span class="w-3 h-3 rounded-full bg-emerald-500 shrink-0"></span>
                                    <span class="text-emerald-700 dark:text-emerald-400">Orar i Lirë</span>
                                </div>
                                <div class="flex items-center gap-2 font-semibold">
                                    <span class="w-3 h-3 rounded-full bg-rose-500 shrink-0"></span>
                                    <span class="text-rose-700 dark:text-rose-400">🔴 Zënë / Overbooked</span>
                                </div>
                                <div class="flex items-center gap-2 font-semibold">
                                    <span class="w-3 h-3 rounded-full bg-gray-400 shrink-0"></span>
                                    <span class="text-gray-500 dark:text-gray-400">Kaluar / Mbyllur</span>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-3">
                            <label class="block mb-2 text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                                Pamja e Plotë e Ditës (Kliko mbi orarin e lirë)
                            </label>

                            @if(($timeSlotData['is_closed'] ?? false) === true)
                                <div class="p-6 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-2xl text-rose-800 dark:text-rose-400 text-xs font-bold flex items-center gap-3">
                                    <span class="text-lg">🛑</span>
                                    <span>Dyqani / Berber-i është i mbyllur në këtë ditë të javës.</span>
                                </div>
                            @elseif(!empty($timeSlotData['slots']))
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2.5 max-h-72 overflow-y-auto p-1">
                                    @foreach($timeSlotData['slots'] as $slot)
                                        @if($slot['is_available'])
                                            {{-- Available Slot --}}
                                            <button type="button"
                                                    wire:click="selectTimeSlot('{{ $slot['time'] }}')"
                                                    class="p-2.5 rounded-2xl text-xs font-black text-center transition-all border flex flex-col items-center justify-center gap-0.5 {{ $selected_time === $slot['time'] ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-500/30 scale-105 ring-2 ring-blue-400' : 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-900 dark:text-emerald-200 border-emerald-200 dark:border-emerald-800 hover:bg-emerald-600 hover:text-white hover:border-emerald-600' }}">
                                                <div class="flex items-center gap-1 font-bold">
                                                    <span>⏰</span> {{ $slot['time'] }}
                                                </div>
                                                <span class="text-[9px] opacity-75 font-medium">Lirë ({{ $slot['end_time'] }})</span>
                                            </button>
                                        @elseif($slot['is_overbooked'])
                                            {{-- Overbooked / Busy Slot --}}
                                            <div class="p-2.5 rounded-2xl text-xs font-bold text-center border bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800 opacity-90 flex flex-col items-center justify-center gap-0.5"
                                                 title="{{ $slot['reason'] }}">
                                                <div class="flex items-center gap-1 font-black">
                                                    <span>🔴</span> {{ $slot['time'] }}
                                                </div>
                                                <span class="text-[9px] font-extrabold text-rose-600 dark:text-rose-400 truncate max-w-full">
                                                    ZËNË
                                                </span>
                                            </div>
                                        @else
                                            {{-- Past or Exceeds Close --}}
                                            <div class="p-2.5 rounded-2xl text-xs font-bold text-center border bg-gray-100 dark:bg-gray-800/60 text-gray-400 border-gray-200 dark:border-gray-700/60 opacity-60 flex flex-col items-center justify-center gap-0.5">
                                                <div class="flex items-center gap-1">
                                                    <span>🔒</span> {{ $slot['time'] }}
                                                </div>
                                                <span class="text-[9px] font-medium text-gray-500 dark:text-gray-400">
                                                    {{ $slot['reason'] ?? 'Mbyllur' }}
                                                </span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl text-amber-800 dark:text-amber-400 text-xs font-semibold flex items-center gap-2">
                                    <span>⚠️</span>
                                    <span>Nuk ka orare të lira për këtë datë.</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Summary preview --}}
                    @if($selected_time)
                        <div class="flex items-center justify-between bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 p-4 rounded-2xl text-xs text-blue-900 dark:text-blue-300">
                            <div class="flex items-center gap-2 font-bold">
                                <span class="bg-blue-600 text-white px-3 py-1 rounded-xl text-xs">Orari i Përzgjedhur</span>
                                <span>Takimi më {{ \Carbon\Carbon::parse($booking_date)->format('d/m/Y') }} në orën <span class="text-sm underline font-black">{{ $selected_time }}</span></span>
                            </div>
                            <x-button type="button" variant="secondary" wire:click="$set('selected_time', '')" class="!px-3 !py-1 text-xs">
                                Ndrysho
                            </x-button>
                        </div>
                    @endif

                    {{-- Datetime-local Override --}}
                    <div class="pt-2 border-t border-gray-200 dark:border-gray-800">
                        <details class="text-xs text-gray-500">
                            <summary class="cursor-pointer font-bold hover:text-blue-600 transition-colors">
                                ⚙️ Vendos orar tjetër specifik manulizisht (Advanced Override)
                            </summary>
                            <div class="mt-3 max-w-xs">
                                <x-form.input name="appointment_at" type="datetime-local" wire:model.live="appointment_at" label="Treguesi i Plotë i Takimit (datetime-local)" class="dark:bg-gray-900" />
                            </div>
                        </details>
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">{{ __('bookings.Status') }}</label>
                    <select name="status" wire:model="status" class="w-full p-3.5 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl dark:text-white">
                        <option value="pending">pending</option>
                        <option value="confirmed">confirmed</option>
                        <option value="completed">completed</option>
                        <option value="cancelled">cancelled</option>
                        <option value="no-show">no-show</option>
                    </select>
                </div>

                {{-- Çmimi Total --}}
                <div>
                    <x-form.input name="total_price" type="number" step="0.01" min="0" inputmode="decimal" oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*)\./g,'$1').replace(/(\.\d{2}).*/g,'$1')" wire:model.live="total_price" :label="__('bookings.Total Price')" class="dark:bg-gray-900 font-bold" />
                </div>

                {{-- Burimi / Source --}}
                <div>
                    <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">{{ __('bookings.Source') }}</label>
                    <select name="source" wire:model="source" class="w-full p-3.5 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl dark:text-white">
                        <option value="walk-in">walk-in</option>
                        <option value="phone">phone</option>
                        <option value="online">online</option>
                    </select>
                </div>

                {{-- Shënime --}}
                <div class="md:col-span-2">
                    <x-form.textarea name="notes" wire:model="notes" :label="__('bookings.Notes')" class="dark:bg-gray-900" />
                </div>

            </div>

            <div class="mt-10 flex justify-end">
                <x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl shadow-lg shadow-blue-500/20">
                    {{ __('bookings.Save') }}
                </x-button>
            </div>
        </form>
    </div>
</div>
