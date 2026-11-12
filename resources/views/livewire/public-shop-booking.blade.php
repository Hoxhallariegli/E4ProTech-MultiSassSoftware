<div id="booking-section" class="py-8 bg-white border border-stone-200/80 rounded-[2.5rem] p-4 sm:p-10 shadow-2xl transition-all duration-300">
    @php
        $primaryColor = !empty($shop->primary_color) ? $shop->primary_color : '#7C5CFC';
        $secondaryColor = !empty($shop->secondary_color) ? $shop->secondary_color : '#0A4D44';
        if (in_array(strtolower($secondaryColor), ['#1c1c1e', '#1c1c1f', '#000000', '#111827'])) {
            $secondaryColor = '#0A4D44';
        }

        $selectedService = $services->firstWhere('id', $selectedServiceId);
        $selectedBarber = $barbers->firstWhere('id', $selectedBarberId);
    @endphp

    @if($shop->is_expired)
        <div class="text-center py-12 space-y-4">
            <div class="w-16 h-16 bg-amber-500/10 border border-amber-500/30 text-amber-700 rounded-3xl flex items-center justify-center mx-auto shadow-lg">
                <x-heroicon-o-exclamation-triangle class="w-8 h-8 text-amber-600" />
            </div>
            <h3 class="text-2xl font-black text-[#1A1D20]">
                {{ __('public_booking.Online Bookings Suspended') }}
            </h3>
            <p class="text-slate-600 text-xs max-w-md mx-auto leading-relaxed font-medium">
                {{ __('public_booking.Online Booking Suspended Desc', ['shop' => $shop->name]) }}
            </p>
        </div>
    @elseif($bookingSuccess && $createdBooking)
        <div class="text-center py-10 space-y-6 animate-fade-in">
            <div class="w-20 h-20 bg-emerald-100 border-2 border-emerald-500 text-emerald-700 rounded-full flex items-center justify-center mx-auto text-4xl shadow-lg">
                ✓
            </div>
            <h3 class="text-2xl sm:text-3xl font-black text-[#1A1D20]">
                {{ __('public_booking.Booking Completed') }}
            </h3>
            <p class="text-slate-600 text-sm max-w-md mx-auto font-medium">
                {{ __('public_booking.Thank You Booking Registered', ['customer' => $customerName, 'shop' => $shop->name]) }}
            </p>

            <div class="p-6 rounded-2xl text-white text-left max-w-md mx-auto space-y-3 text-xs shadow-lg"
                 style="background-color: {{ $secondaryColor }};">
                <div class="flex justify-between border-b border-white/20 pb-2">
                    <span class="text-teal-100">{{ __('public_booking.Staff Member') }}</span>
                    <span class="font-extrabold text-white">{{ $createdBooking->barber?->name }}</span>
                </div>
                <div class="flex justify-between border-b border-white/20 pb-2">
                    <span class="text-teal-100">{{ __('public_booking.Service') }}</span>
                    <span class="font-extrabold text-white">{{ $createdBooking->service?->name }}</span>
                </div>
                <div class="flex justify-between border-b border-white/20 pb-2">
                    <span class="text-teal-100">{{ __('public_booking.Date & Time') }}</span>
                    <span class="font-black text-amber-300">{{ $createdBooking->appointment_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between pt-1">
                    <span class="text-teal-100">{{ __('public_booking.Total Price') }}</span>
                    <span class="font-black text-white text-sm">{{ number_format($createdBooking->total_price, 0) }} {{ $shop->resolved_currency }}</span>
                </div>
            </div>

            <button wire:click="resetForm" class="px-8 py-3.5 rounded-full font-black text-xs text-white bg-[#1A1D20] hover:bg-black transition shadow-md">
                {{ __('public_booking.Make Another Booking') }}
            </button>
        </div>
    @else
        <!-- STEPPER HEADER PROGRESS BAR -->
        <div class="mb-8">
            <div class="flex items-center justify-between border-b border-stone-200 pb-4 mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full animate-pulse" style="background-color: {{ $primaryColor }};"></span>
                    <span class="text-xs font-black uppercase tracking-wider text-slate-500">
                        {{ __('public_booking.Automated 24/7 Booking') }}
                    </span>
                </div>
                <span class="text-xs font-extrabold px-3 py-1 rounded-full text-white" style="background-color: {{ $primaryColor }};">
                    {{ $currentStep }} / 4
                </span>
            </div>

            <!-- Stepper Steps Tabs -->
            <div class="grid grid-cols-4 gap-1 sm:gap-2 text-center">
                <button type="button" wire:click="goToStep(1)" class="py-2 px-1 rounded-xl text-[10px] sm:text-xs font-extrabold transition flex flex-col sm:flex-row items-center justify-center gap-1 {{ $currentStep === 1 ? 'text-white shadow-md' : ($selectedServiceId ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-stone-100 text-stone-500') }}"
                        style="{{ $currentStep === 1 ? 'background-color: '.$primaryColor.';' : '' }}">
                    <span>1.</span> <span class="truncate">{{ __('public_booking.Service') }}</span>
                </button>
                <button type="button" wire:click="goToStep(2)" class="py-2 px-1 rounded-xl text-[10px] sm:text-xs font-extrabold transition flex flex-col sm:flex-row items-center justify-center gap-1 {{ $currentStep === 2 ? 'text-white shadow-md' : ($selectedBarberId ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-stone-100 text-stone-500') }}"
                        style="{{ $currentStep === 2 ? 'background-color: '.$primaryColor.';' : '' }}">
                    <span>2.</span> <span class="truncate">{{ __('public_booking.Staff Member') }}</span>
                </button>
                <button type="button" wire:click="goToStep(3)" class="py-2 px-1 rounded-xl text-[10px] sm:text-xs font-extrabold transition flex flex-col sm:flex-row items-center justify-center gap-1 {{ $currentStep === 3 ? 'text-white shadow-md' : ($bookingDate && $bookingTime ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-stone-100 text-stone-500') }}"
                        style="{{ $currentStep === 3 ? 'background-color: '.$primaryColor.';' : '' }}">
                    <span>3.</span> <span class="truncate">{{ __('public_booking.Date & Time') }}</span>
                </button>
                <button type="button" wire:click="goToStep(4)" class="py-2 px-1 rounded-xl text-[10px] sm:text-xs font-extrabold transition flex flex-col sm:flex-row items-center justify-center gap-1 {{ $currentStep === 4 ? 'text-white shadow-md' : 'bg-stone-100 text-stone-500' }}"
                        style="{{ $currentStep === 4 ? 'background-color: '.$primaryColor.';' : '' }}">
                    <span>4.</span> <span class="truncate">{{ __('public_booking.Your Name') }}</span>
                </button>
            </div>
        </div>

        <form wire:submit.prevent="submitBooking" class="space-y-6">

            <!-- STEP 1: SELECT SERVICE -->
            @if($currentStep === 1)
                <div class="space-y-4 animate-fade-in">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-black text-[#1A1D20] uppercase tracking-wider">
                            {{ __('public_booking.1 Select Service') }}
                        </label>
                        <span class="text-[11px] text-slate-500 font-bold">Zgjidhni shërbimin për të kaluar te stafi</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($services as $s)
                            <button type="button"
                                    wire:click="selectService({{ $s->id }})"
                                    class="p-4 rounded-2xl border text-left transition flex items-center justify-between cursor-pointer active:scale-98"
                                    style="{{ $selectedServiceId === $s->id ? 'border-color: '.$primaryColor.'; background-color: '.$primaryColor.'15; box-shadow: 0 0 0 2px '.$primaryColor.'40;' : 'border-color: #e2e8f0; background-color: #FAF8F2;' }}">
                                <div class="pr-2">
                                    <h4 class="font-extrabold text-sm text-[#1A1D20]">{{ $s->name }}</h4>
                                    <span class="text-xs text-slate-500 font-medium">{{ $s->duration_minutes }} min</span>
                                </div>
                                <span class="font-black text-sm whitespace-nowrap shrink-0" style="color: {{ $secondaryColor }};">{{ $s->formatted_price_range }}</span>
                            </button>
                        @endforeach
                    </div>
                    @error('selectedServiceId') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror

                    @if($selectedServiceId)
                        <div class="pt-4 flex justify-end">
                            <button type="button" wire:click="nextStep" class="px-6 py-3 rounded-full text-xs font-black text-white shadow-md transition transform active:scale-95 flex items-center gap-2"
                                    style="background-color: {{ $primaryColor }};">
                                <span>Vazhdo te Stafi</span> <span>➔</span>
                            </button>
                        </div>
                    @endif
                </div>

            <!-- STEP 2: SELECT STAFF -->
            @elseif($currentStep === 2)
                <div class="space-y-4 animate-fade-in">
                    <div class="flex items-center justify-between">
                        <button type="button" wire:click="previousStep" class="text-xs font-black text-slate-600 hover:text-black flex items-center gap-1">
                            <span>←</span> <span>Mbrapa</span>
                        </button>
                        <label class="block text-xs font-black text-[#1A1D20] uppercase tracking-wider">
                            {{ __('public_booking.2 Select Staff Member') }}
                        </label>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($staff as $st)
                            <button type="button"
                                    wire:click="selectBarber({{ $st->id }})"
                                    class="p-4 rounded-2xl border text-center transition cursor-pointer active:scale-98"
                                    style="{{ $selectedBarberId === $st->id ? 'border-color: '.$primaryColor.'; background-color: '.$primaryColor.'15; box-shadow: 0 0 0 2px '.$primaryColor.'40;' : 'border-color: #e2e8f0; background-color: #FAF8F2;' }}">
                                <div class="w-12 h-12 rounded-2xl font-black flex items-center justify-center mx-auto mb-2 text-lg"
                                     style="background-color: {{ $secondaryColor }}15; color: {{ $secondaryColor }}; border: 1px solid {{ $secondaryColor }}30;">
                                    {{ mb_substr($st->name, 0, 1) }}
                                </div>
                                <h5 class="font-extrabold text-xs text-[#1A1D20] truncate">{{ $st->name }}</h5>
                            </button>
                        @endforeach
                    </div>
                    @error('selectedBarberId') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror

                    <div class="pt-4 flex items-center justify-between">
                        <button type="button" wire:click="previousStep" class="px-5 py-2.5 rounded-full text-xs font-bold text-slate-600 bg-stone-100 hover:bg-stone-200 transition">
                            ← Mbrapa
                        </button>
                        @if($selectedBarberId)
                            <button type="button" wire:click="nextStep" class="px-6 py-3 rounded-full text-xs font-black text-white shadow-md transition transform active:scale-95 flex items-center gap-2"
                                    style="background-color: {{ $primaryColor }};">
                                <span>Vazhdo te Orari</span> <span>➔</span>
                            </button>
                        @endif
                    </div>
                </div>

            <!-- STEP 3: DATE & TIME -->
            @elseif($currentStep === 3)
                <div class="space-y-4 animate-fade-in">
                    <div class="flex items-center justify-between">
                        <button type="button" wire:click="previousStep" class="text-xs font-black text-slate-600 hover:text-black flex items-center gap-1">
                            <span>←</span> <span>Mbrapa</span>
                        </button>
                        <label class="block text-xs font-black text-[#1A1D20] uppercase tracking-wider">
                            {{ __('public_booking.3 Appointment Date') }} & {{ __('public_booking.4 Appointment Time') }}
                        </label>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-extrabold text-[#1A1D20] uppercase tracking-wider mb-2">
                                {{ __('public_booking.3 Appointment Date') }}
                            </label>
                            <input type="date" wire:model.live="bookingDate" min="{{ date('Y-m-d') }}"
                                   class="w-full px-5 py-3.5 rounded-2xl bg-[#FAF8F2] border border-slate-200 text-[#1A1D20] font-bold text-sm focus:outline-none transition">
                            @error('bookingDate') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-[#1A1D20] uppercase tracking-wider mb-2">
                                {{ __('public_booking.4 Appointment Time') }}
                            </label>
                            @if(!empty($availableTimeSlots))
                                <select wire:model.live="bookingTime"
                                        class="w-full px-5 py-3.5 rounded-2xl bg-[#FAF8F2] border border-slate-200 text-[#1A1D20] font-bold text-sm focus:outline-none transition">
                                    @foreach($availableTimeSlots as $timeSlot)
                                        <option value="{{ $timeSlot }}">{{ $timeSlot }}</option>
                                    @endforeach
                                </select>
                            @else
                                <div class="px-4 py-3 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-800 font-bold text-xs flex items-center gap-2">
                                    {{ __('public_booking.No Slots Available') }}
                                </div>
                            @endif
                            @error('bookingTime') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-between">
                        <button type="button" wire:click="previousStep" class="px-5 py-2.5 rounded-full text-xs font-bold text-slate-600 bg-stone-100 hover:bg-stone-200 transition">
                            ← Mbrapa
                        </button>
                        @if($bookingDate && $bookingTime)
                            <button type="button" wire:click="nextStep" class="px-6 py-3 rounded-full text-xs font-black text-white shadow-md transition transform active:scale-95 flex items-center gap-2"
                                    style="background-color: {{ $primaryColor }};">
                                <span>Vazhdo te Detajet</span> <span>➔</span>
                            </button>
                        @endif
                    </div>
                </div>

            <!-- STEP 4: CUSTOMER DETAILS & CONFIRMATION -->
            @elseif($currentStep === 4)
                <div class="space-y-6 animate-fade-in">
                    <!-- Summary Card -->
                    <div class="p-5 rounded-2xl text-white text-xs space-y-2 shadow-md" style="background-color: {{ $secondaryColor }};">
                        <div class="flex justify-between border-b border-white/20 pb-1.5">
                            <span class="text-teal-100 font-medium">Shërbimi:</span>
                            <span class="font-extrabold text-white">{{ $selectedService?->name ?: '-' }} ({{ $selectedService?->duration_minutes }} min)</span>
                        </div>
                        <div class="flex justify-between border-b border-white/20 pb-1.5">
                            <span class="text-teal-100 font-medium">Stafi:</span>
                            <span class="font-extrabold text-white">{{ $selectedBarber?->name ?: '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-white/20 pb-1.5">
                            <span class="text-teal-100 font-medium">Data & Ora:</span>
                            <span class="font-black text-amber-300">{{ $bookingDate }} | {{ $bookingTime }}</span>
                        </div>
                        <div class="flex justify-between pt-0.5">
                            <span class="text-teal-100 font-medium">Çmimi:</span>
                            <span class="font-black text-white text-sm">{{ $selectedService?->formatted_price_range }}</span>
                        </div>
                    </div>

                    <!-- Customer Info Form -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-extrabold text-[#1A1D20] uppercase tracking-wider mb-2">
                                {{ __('public_booking.Your Name') }}
                            </label>
                            <input type="text" wire:model.live="customerName" placeholder="{{ __('public_booking.Name Placeholder') }}"
                                   class="w-full px-5 py-3.5 rounded-2xl bg-[#FAF8F2] border border-slate-200 text-[#1A1D20] text-sm font-bold focus:outline-none transition">
                            @error('customerName') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-[#1A1D20] uppercase tracking-wider mb-2">
                                {{ __('public_booking.Phone Number') }}
                            </label>
                            <input type="text" wire:model.live="customerPhone" placeholder="{{ __('public_booking.Phone Placeholder') }}"
                                   class="w-full px-5 py-3.5 rounded-2xl bg-[#FAF8F2] border border-slate-200 text-[#1A1D20] text-sm font-bold focus:outline-none transition">
                            @error('customerPhone') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-between gap-4">
                        <button type="button" wire:click="previousStep" class="px-5 py-3 rounded-full text-xs font-bold text-slate-600 bg-stone-100 hover:bg-stone-200 transition shrink-0">
                            ← Mbrapa
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="flex-1 py-4 rounded-full font-black text-sm text-white shadow-xl transition transform active:scale-95 flex items-center justify-center gap-2 cursor-pointer"
                                style="background-color: {{ $primaryColor }};">
                            <span wire:loading.remove>{{ __('public_booking.Confirm Booking') }}</span>
                            <span wire:loading>{{ __('public_booking.Submitting') }}</span>
                        </button>
                    </div>
                </div>
            @endif

        </form>
    @endif
</div>
