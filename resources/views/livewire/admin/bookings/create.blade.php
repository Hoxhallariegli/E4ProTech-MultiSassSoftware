<div class="space-y-8 p-1 sm:p-2">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 sm:p-8 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-xs">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-black bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 mb-2">
                <x-heroicon-o-calendar class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
                <span>{{ __('bookings.Add Booking') }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                {{ __('bookings.Add Booking') }}
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">
                Zgjidhni sallonin, berberin, shërbimin dhe orarin për të regjistruar një takim të ri.
            </p>
        </div>
        <x-back-btn route="admin.bookings.index" />
    </div>

    @include('errors.errors')

    <!-- Main Booking Form Card -->
    <div class="bg-white dark:bg-gray-800 p-6 sm:p-10 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 space-y-8">
        <form wire:submit.prevent="store" class="space-y-8">

            <!-- 1. Salon, Staff & Customer Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- Salon / Barber Shop --}}
                <div>
                    @if(auth()->user()->barber_shop_id && !auth()->user()->is_global_admin)
                        <x-form.input name="barber_shop_name" :label="__('bookings.Barber Shop Id')" value="{{ auth()->user()->barberShop?->name }}" readonly disabled class="bg-gray-50 dark:bg-gray-900 font-bold text-sm" />
                        <input type="hidden" wire:model="barber_shop_id">
                    @else
                        <div class="flex items-end gap-2">
                            <div class="flex-1">
                                <x-form.dropdown-search name="barber_shop_id" wire:model.live="barber_shop_id" :label="__('bookings.Barber Shop Id')" :data="$barberShops" />
                            </div>
                            <x-modal>
                                <x-slot name="trigger">
                                    <button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform" title="Shto Sallon të Ri">
                                        <x-heroicon-o-plus class="w-5 h-5" />
                                    </button>
                                </x-slot>
                                <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Shto Sallon të Ri</div></x-slot>
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
                                <button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform" title="Shto Staf të Ri">
                                    <x-heroicon-o-plus class="w-5 h-5" />
                                </button>
                            </x-slot>
                            <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Shto Staf / Berber të Ri</div></x-slot>
                            <x-slot name="content"><livewire:admin.barbers.quick-create /></x-slot>
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
                                <button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform" title="Shto Klient të Ri">
                                    <x-heroicon-o-plus class="w-5 h-5" />
                                </button>
                            </x-slot>
                            <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Shto Klient të Ri</div></x-slot>
                            <x-slot name="content"><livewire:admin.customers.quick-create /></x-slot>
                        </x-modal>
                    </div>
                </div>

            </div>

            <!-- 2. Interactive Service Selection Cards -->
            <div class="bg-gray-50/80 dark:bg-gray-900/50 p-6 sm:p-8 rounded-3xl border border-gray-100 dark:border-gray-800 space-y-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-800 pb-4">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-900 dark:text-white flex items-center gap-2">
                            <x-heroicon-o-scissors class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                            <span>{{ __('bookings.Service Id') }} (Kliko për të Zgjedhur)</span>
                        </label>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 font-medium">Kliko mbi një nga shërbimet e mëposhtme për ta zgjedhur.</p>
                    </div>

                    {{-- Search Input for Services --}}
                    <div class="w-full sm:w-64 relative">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                        <input type="text"
                               wire:model.live.debounce.200ms="service_search"
                               placeholder="Kërko shërbimin..."
                               class="w-full pl-9 pr-4 py-2.5 text-xs font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xs focus:ring-2 focus:ring-blue-500 dark:text-white">
                    </div>
                </div>

                @if(!empty($serviceListObjects) && $serviceListObjects->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-64 overflow-y-auto p-1 custom-scrollbar">
                        @foreach($serviceListObjects as $s)
                            <button type="button"
                                    wire:click="selectService('{{ $s->id }}')"
                                    class="p-4 rounded-2xl text-left transition-all border flex items-center justify-between gap-3 relative {{ (string)$service_id === (string)$s->id ? 'bg-blue-600 text-white border-blue-600 shadow-xl shadow-blue-500/25 ring-2 ring-blue-400 scale-[1.01]' : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:border-blue-400 hover:bg-blue-50/50' }}">
                                <div class="space-y-1">
                                    <div class="font-black text-sm flex items-center gap-2">
                                        @if((string)$service_id === (string)$s->id)
                                            <x-heroicon-o-check-circle class="w-4 h-4 text-white shrink-0" />
                                        @else
                                            <x-heroicon-o-scissors class="w-4 h-4 text-gray-400 shrink-0" />
                                        @endif
                                        <span>{{ $s->name }}</span>
                                    </div>
                                    <div class="text-[11px] opacity-80 flex items-center gap-3 font-semibold">
                                        <span class="flex items-center gap-1">
                                            <x-heroicon-o-clock class="w-3.5 h-3.5" />
                                            {{ $s->duration_minutes }} min
                                        </span>
                                        <span class="flex items-center gap-1 font-mono font-bold">
                                            {{ number_format($s->price, 0) }} {{ auth()->user()->barberShop?->resolved_currency ?? 'Lekë' }}
                                        </span>
                                    </div>
                                </div>
                                @if((string)$service_id === (string)$s->id)
                                    <span class="bg-white/20 text-white px-2.5 py-1 rounded-xl text-[10px] font-black uppercase shrink-0">
                                        I Zgjedhur
                                    </span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl text-amber-800 dark:text-amber-400 text-xs font-bold">
                        Nuk u gjet asnjë shërbim me këtë kërkim.
                    </div>
                @endif
            </div>

            <!-- 3. Calendar & Time Slot Picker -->
            <div class="bg-gray-50/80 dark:bg-gray-900/50 p-6 sm:p-8 rounded-3xl border border-gray-100 dark:border-gray-800 space-y-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-800 pb-4">
                    <div>
                        <h3 class="text-sm sm:text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                            <x-heroicon-o-calendar class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            <span>Përzgjidh Datën &amp; Orarin e Lirë</span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-medium">
                            Hapi i orarit: <strong class="text-blue-600 dark:text-blue-400 font-bold">{{ $timeSlotData['slot_step'] ?? 15 }} min</strong> • Kohëzgjatja e shërbimit: <strong class="text-blue-600 dark:text-blue-400 font-bold">{{ $timeSlotData['req_duration'] ?? 30 }} min</strong>
                        </p>
                    </div>

                    {{-- Quick Date Buttons --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" wire:click="selectQuickDate(0)"
                                class="px-4 py-2 text-xs font-bold rounded-2xl border transition-all {{ $booking_date === now()->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-500/20' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:border-blue-500' }}">
                            Sot
                        </button>
                        <button type="button" wire:click="selectQuickDate(1)"
                                class="px-4 py-2 text-xs font-bold rounded-2xl border transition-all {{ $booking_date === now()->addDays(1)->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-500/20' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:border-blue-500' }}">
                            Nesër
                        </button>
                        <button type="button" wire:click="selectQuickDate(2)"
                                class="px-4 py-2 text-xs font-bold rounded-2xl border transition-all {{ $booking_date === now()->addDays(2)->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-500/20' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:border-blue-500' }}">
                            Pasnesër
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <div>
                        <x-form.input name="booking_date" type="date" wire:model.live="booking_date" label="Data e Rezervimit" class="dark:bg-gray-900 font-bold" />

                        {{-- Legend --}}
                        <div class="mt-4 p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-2 text-xs">
                            <div class="flex items-center gap-2 font-semibold">
                                <span class="w-3 h-3 rounded-full bg-emerald-500 shrink-0"></span>
                                <span class="text-emerald-700 dark:text-emerald-400">Orar i Lirë</span>
                            </div>
                            <div class="flex items-center gap-2 font-semibold">
                                <span class="w-3 h-3 rounded-full bg-rose-500 shrink-0"></span>
                                <span class="text-rose-700 dark:text-rose-400">Zënë (Busy)</span>
                            </div>
                            <div class="flex items-center gap-2 font-semibold">
                                <span class="w-3 h-3 rounded-full bg-gray-400 shrink-0"></span>
                                <span class="text-gray-500 dark:text-gray-400">Kaluar / Mbyllur</span>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-3">
                        <label class="block mb-2 text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                            Oraret e Lira të Ditës (Kliko për të zgjedhur)
                        </label>

                        @if(($timeSlotData['is_closed'] ?? false) === true)
                            <div class="p-6 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-2xl text-rose-800 dark:text-rose-400 text-xs font-bold flex items-center gap-3">
                                <x-heroicon-o-lock-closed class="w-6 h-6 text-rose-600 shrink-0" />
                                <span>Salloni ose berberi është i mbyllur në këtë ditë të javës.</span>
                            </div>
                        @elseif(!empty($timeSlotData['slots']))
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2.5 max-h-72 overflow-y-auto p-1 custom-scrollbar">
                                @foreach($timeSlotData['slots'] as $slot)
                                    @if($slot['is_available'])
                                        {{-- Available Slot --}}
                                        <button type="button"
                                                wire:click="selectTimeSlot('{{ $slot['time'] }}')"
                                                class="p-3 rounded-2xl text-xs font-black text-center transition-all border flex flex-col items-center justify-center gap-0.5 {{ $selected_time === $slot['time'] ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-500/30 scale-105 ring-2 ring-blue-400' : 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-900 dark:text-emerald-200 border-emerald-200 dark:border-emerald-800 hover:bg-emerald-600 hover:text-white hover:border-emerald-600' }}">
                                            <div class="flex items-center gap-1 font-bold">
                                                <x-heroicon-o-clock class="w-3.5 h-3.5 shrink-0" />
                                                <span>{{ $slot['time'] }}</span>
                                            </div>
                                            <span class="text-[9px] opacity-75 font-medium">Lirë ({{ $slot['end_time'] }})</span>
                                        </button>
                                    @elseif($slot['is_overbooked'])
                                        {{-- Overbooked / Busy Slot --}}
                                        <div class="p-3 rounded-2xl text-xs font-bold text-center border bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800 opacity-90 flex flex-col items-center justify-center gap-0.5"
                                             title="{{ $slot['reason'] }}">
                                            <div class="flex items-center gap-1 font-black">
                                                <span class="w-2 h-2 rounded-full bg-rose-600 inline-block"></span>
                                                <span>{{ $slot['time'] }}</span>
                                            </div>
                                            <span class="text-[9px] font-extrabold text-rose-600 dark:text-rose-400 truncate max-w-full">
                                                ZËNË
                                            </span>
                                        </div>
                                    @else
                                        {{-- Past or Exceeds Close --}}
                                        <div class="p-3 rounded-2xl text-xs font-bold text-center border bg-gray-100 dark:bg-gray-800/60 text-gray-400 border-gray-200 dark:border-gray-700/60 opacity-60 flex flex-col items-center justify-center gap-0.5">
                                            <div class="flex items-center gap-1">
                                                <x-heroicon-o-lock-closed class="w-3.5 h-3.5 shrink-0 text-gray-400" />
                                                <span>{{ $slot['time'] }}</span>
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
                                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-600 shrink-0" />
                                <span>Nuk ka orare të lira për këtë datë.</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Summary preview --}}
                @if($selected_time)
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 p-4 rounded-2xl text-xs text-blue-900 dark:text-blue-300">
                        <div class="flex items-center gap-2 font-bold">
                            <span class="bg-blue-600 text-white px-3 py-1 rounded-xl text-xs">Orari i Përzgjedhur</span>
                            <span>Takimi më <strong>{{ \Carbon\Carbon::parse($booking_date)->format('d/m/Y') }}</strong> në orën <span class="text-sm underline font-black">{{ $selected_time }}</span></span>
                        </div>
                        <x-button type="button" variant="secondary" wire:click="$set('selected_time', '')" class="!px-3 !py-1 text-xs">
                            Ndrysho
                        </x-button>
                    </div>
                @endif
            </div>

            <!-- 4. Status, Pricing & Notes -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- Status --}}
                <div>
                    <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300">{{ __('bookings.Status') }}</label>
                    <select name="status" wire:model="status" class="w-full p-3.5 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl dark:text-white focus:ring-2 focus:ring-blue-500">
                        <option value="confirmed">I Konfirmuar (Confirmed)</option>
                        <option value="pending">Në Pritje (Pending)</option>
                        <option value="completed">I Përfunduar (Completed)</option>
                        <option value="cancelled">Anuluar (Cancelled)</option>
                        <option value="no-show">Nuk Erdhi (No-Show)</option>
                    </select>
                </div>

                {{-- Source --}}
                <div>
                    <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300">{{ __('bookings.Source') }}</label>
                    <select name="source" wire:model="source" class="w-full p-3.5 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl dark:text-white focus:ring-2 focus:ring-blue-500">
                        <option value="walk-in">Walk-In (I pranishëm fizikisht)</option>
                        <option value="phone">Në Telefon (Phone Call)</option>
                        <option value="online">Online (Faqja Publike)</option>
                    </select>
                </div>

                {{-- Çmimi Total --}}
                <div>
                    <x-form.input name="total_price" type="number" step="0.01" min="0" inputmode="decimal" oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*)\./g,'$1').replace(/(\.\d{2}).*/g,'$1')" wire:model.live="total_price" :label="__('bookings.Total Price')" class="dark:bg-gray-900 font-bold text-sm" />
                </div>

                {{-- Shënime --}}
                <div class="md:col-span-3">
                    <x-form.textarea name="notes" wire:model="notes" :label="__('bookings.Notes')" class="dark:bg-gray-900" placeholder="Shtoni shënime shtesë për klientin ose takimin..." />
                </div>

            </div>

            <!-- Submit Button -->
            <div class="mt-8 flex justify-end">
                <x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl shadow-xl shadow-blue-500/20 font-black text-sm">
                    {{ __('bookings.Save') }}
                </x-button>
            </div>

        </form>
    </div>
</div>
