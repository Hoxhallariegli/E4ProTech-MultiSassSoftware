<div class="p-6">
    @if($created)
        <div class="flex flex-col items-center text-center py-10">
            <div class="w-12 h-12 rounded-full bg-green-50 dark:bg-green-900/30 flex items-center justify-center mb-4">
                <x-heroicon-o-check class="w-6 h-6 text-green-500" />
            </div>
            <p class="font-bold text-gray-900 dark:text-white">{{ __('bookings.created') }}</p>
            @if($createdLabel)<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $createdLabel }}</p>@endif
            <button type="button" wire:click="addAnother" class="mt-6 text-xs font-black uppercase tracking-widest text-blue-600 dark:text-blue-400">{{ __('bookings.Add Booking') }}</button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            <div>
                @if(auth()->user()->barber_shop_id && !auth()->user()->is_global_admin)
                    <x-form.input name="barber_shop_name" :label="__('bookings.Barber Shop Id')" value="{{ auth()->user()->barberShop?->name }}" readonly disabled class="bg-gray-50 dark:bg-gray-900" />
                    <input type="hidden" wire:model="barber_shop_id">
                @else
                    <div class="flex items-end gap-2">
                        <div class="flex-1"><x-form.dropdown-search name="barber_shop_id" wire:model.live="barber_shop_id" :label="__('bookings.Barber Shop Id')" :data="$barberShops" /></div>
                    </div>
                @endif
            </div>

            <div>
                <div class="flex items-end gap-2">
                    <div class="flex-1"><x-form.dropdown-search name="barber_id" wire:model.live="barber_id" :label="__('bookings.Barber Id')" :data="$barbers" /></div>
                </div>
            </div>

            {{-- Service Selection Cards + Search --}}
            <div class="md:col-span-2 bg-gray-50 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 dark:border-gray-800 pb-2">
                    <label class="block text-xs font-black uppercase tracking-wider text-gray-900 dark:text-white flex items-center gap-1.5">
                        <x-heroicon-o-scissors class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                        <span>Zgjidh Shërbimin</span>
                    </label>

                    {{-- Search Input for Services --}}
                    <div class="w-full sm:w-56 relative">
                        <x-heroicon-o-magnifying-glass class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                        <input type="text"
                               wire:model.live.debounce.200ms="service_search"
                               placeholder="Kërko shërbimin..."
                               class="w-full pl-8 pr-3 py-1.5 text-xs font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 dark:text-white">
                    </div>
                </div>

                @if(!empty($serviceListObjects) && $serviceListObjects->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 max-h-44 overflow-y-auto p-1 custom-scrollbar">
                        @foreach($serviceListObjects as $s)
                            <button type="button"
                                    wire:click="selectService('{{ $s->id }}')"
                                    class="p-3 rounded-xl text-left transition-all border flex items-center justify-between gap-2 relative {{ (string)$service_id === (string)$s->id ? 'bg-blue-600 text-white border-blue-600 shadow-md ring-2 ring-blue-400 scale-[1.01]' : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:border-blue-400 hover:bg-blue-50/50' }}">
                                <div>
                                    <div class="font-extrabold text-xs flex items-center gap-1">
                                        @if((string)$service_id === (string)$s->id)
                                            <x-heroicon-o-check class="w-3.5 h-3.5 text-white shrink-0" />
                                        @else
                                            <x-heroicon-o-scissors class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                                        @endif
                                        <span>{{ $s->name }}</span>
                                    </div>
                                    <div class="text-[10px] opacity-80 mt-0.5 flex items-center gap-2">
                                        <span class="flex items-center gap-0.5">
                                            <x-heroicon-o-clock class="w-3 h-3" />
                                            {{ $s->duration_minutes }} min
                                        </span>
                                        <span class="flex items-center gap-0.5">
                                            <x-heroicon-o-banknotes class="w-3 h-3" />
                                            {{ number_format($s->price, 0) }} Lekë
                                        </span>
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl text-amber-800 dark:text-amber-400 text-xs font-medium">
                        Nuk u gjet asnjë shërbim me këtë kërkim.
                    </div>
                @endif
            </div>

            <div>
                <div class="flex items-end gap-2">
                    <div class="flex-1"><x-form.dropdown-search name="customer_id" wire:model.live="customer_id" :label="__('bookings.Customer Id')" :data="$customers" /></div>
                </div>
            </div>

            <div>
                <x-form.input name="total_price" type="number" step="0.01" min="0" inputmode="decimal" wire:model.live="total_price" :label="__('bookings.Total Price')" class="dark:bg-gray-900" />
            </div>

            {{-- DYNAMIC TIME SLOTS & OVERBOOKING CALCULATOR --}}
            <div class="md:col-span-2 bg-gray-50 dark:bg-gray-900/60 p-5 rounded-2xl border border-gray-100 dark:border-gray-800 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 dark:border-gray-800 pb-3">
                    <div>
                        <h4 class="text-xs font-black uppercase tracking-wider text-gray-900 dark:text-white flex items-center gap-1.5">
                            <x-heroicon-o-calendar class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                            <span>Data & Kalendari i Orareve</span>
                        </h4>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400">
                            Hapi: <span class="font-bold text-blue-600">{{ $timeSlotData['slot_step'] ?? 15 }} min</span> • Shërbimi: <span class="font-bold text-blue-600">{{ $timeSlotData['req_duration'] ?? 30 }} min</span>
                        </p>
                    </div>

                    {{-- Quick Date Buttons --}}
                    <div class="flex items-center gap-1.5">
                        <button type="button" wire:click="selectQuickDate(0)" class="px-3 py-1 text-[10px] font-bold rounded-lg border transition-all {{ $booking_date === now()->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700' }}">
                            Sot
                        </button>
                        <button type="button" wire:click="selectQuickDate(1)" class="px-3 py-1 text-[10px] font-bold rounded-lg border transition-all {{ $booking_date === now()->addDays(1)->format('Y-m-d') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700' }}">
                            Nesër
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-form.input name="booking_date" type="date" wire:model.live="booking_date" label="Data" class="dark:bg-gray-900 font-bold" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block mb-1 text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                            Oraret e Ditës
                        </label>

                        @if(($timeSlotData['is_closed'] ?? false) === true)
                            <div class="p-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-xl text-rose-800 dark:text-rose-400 text-xs font-bold flex items-center gap-2">
                                <x-heroicon-o-lock-closed class="w-4 h-4 text-rose-600 shrink-0" />
                                <span>Mbyllur sot.</span>
                            </div>
                        @elseif(!empty($timeSlotData['slots']))
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 max-h-44 overflow-y-auto p-1 custom-scrollbar">
                                @foreach($timeSlotData['slots'] as $slot)
                                    @if($slot['is_available'])
                                        <button type="button"
                                                wire:click="selectTimeSlot('{{ $slot['time'] }}')"
                                                class="py-2 px-2.5 rounded-xl text-xs font-bold text-center transition-all border {{ $selected_time === $slot['time'] ? 'bg-blue-600 text-white border-blue-600 shadow' : 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-900 dark:text-emerald-200 border-emerald-200 dark:border-emerald-800 hover:bg-emerald-600 hover:text-white' }}">
                                            {{ $slot['time'] }}
                                        </button>
                                    @elseif($slot['is_overbooked'])
                                        <div class="py-2 px-2.5 rounded-xl text-[10px] font-black text-center border bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 opacity-90 truncate flex items-center justify-center gap-1" title="{{ $slot['reason'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600 inline-block"></span>
                                            <span>{{ $slot['time'] }} ZËNË</span>
                                        </div>
                                    @else
                                        <div class="py-2 px-2.5 rounded-xl text-[10px] font-bold text-center border bg-gray-100 dark:bg-gray-800 text-gray-400 border-gray-200 dark:border-gray-700 opacity-50">
                                            {{ $slot['time'] }}
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl text-amber-800 dark:text-amber-400 text-xs font-medium">
                                Nuk ka orare të lira për këtë datë.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div>
                <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest">{{ __('bookings.Status') }}</label>
                <select name="status" wire:model="status" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl dark:text-white">
                    <option value="pending">pending</option>
                    <option value="confirmed">confirmed</option>
                    <option value="completed">completed</option>
                    <option value="cancelled">cancelled</option>
                    <option value="no-show">no-show</option>
                </select>
            </div>

            <div>
                <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest">{{ __('bookings.Source') }}</label>
                <select name="source" wire:model="source" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl dark:text-white">
                    <option value="walk-in">walk-in</option>
                    <option value="phone">phone</option>
                    <option value="online">online</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <x-form.textarea name="notes" wire:model="notes" :label="__('bookings.Notes')" class="dark:bg-gray-900" />
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <x-button wire:click="store" variant="blue">{{ __('bookings.Save') }}</x-button>
        </div>
    @endif
</div>
