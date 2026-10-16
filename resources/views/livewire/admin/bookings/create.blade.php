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

                {{-- DYNAMIC TIME SLOTS & DATE SECTION --}}
                <div class="md:col-span-2 bg-gray-50 dark:bg-gray-900/60 p-6 sm:p-8 rounded-3xl border border-gray-100 dark:border-gray-800 space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-800 pb-4">
                        <div>
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                                <span>📅</span> Zgjidh Datën & Oraren e Lirë
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Oraret e lira llogariten automatikisht sipas orarit të berberit dhe takimeve ekzistuese.
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

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <x-form.input name="booking_date" type="date" wire:model.live="booking_date" label="Data e Rezervimit" class="dark:bg-gray-900" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block mb-2 text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                                Oraret e Lira (Kliko për ta zgjedhur)
                            </label>

                            @if(!empty($availableTimeSlots))
                                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 max-h-48 overflow-y-auto p-1">
                                    @foreach($availableTimeSlots as $slot)
                                        <button type="button"
                                                wire:click="selectTimeSlot('{{ $slot }}')"
                                                class="py-2.5 px-3 rounded-2xl text-xs font-black text-center transition-all border flex items-center justify-center gap-1 {{ $selected_time === $slot ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-500/30 scale-105 ring-2 ring-blue-400' : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:border-blue-300' }}">
                                            <span>⏰</span> {{ $slot }}
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl text-amber-800 dark:text-amber-400 text-xs font-semibold flex items-center gap-2">
                                    <span>⚠️</span>
                                    <span>Nuk ka orare të lira të disponueshme për këtë datë/berber.</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Summary pill / selected time preview --}}
                    @if($selected_time)
                        <div class="flex items-center justify-between bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 p-4 rounded-2xl text-xs text-blue-900 dark:text-blue-300">
                            <div class="flex items-center gap-2 font-bold">
                                <span class="bg-blue-600 text-white px-3 py-1 rounded-xl text-xs">I zgjedhur</span>
                                <span>Takimi më {{ \Carbon\Carbon::parse($booking_date)->format('d/m/Y') }} në orën <span class="text-sm underline font-black">{{ $selected_time }}</span></span>
                            </div>
                            <x-button type="button" variant="secondary" wire:click="$set('selected_time', '')" class="!px-3 !py-1 text-xs">
                                Ndrysho
                            </x-button>
                        </div>
                    @endif

                    {{-- Datetime-local Fallback / Advanced Custom Override --}}
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
