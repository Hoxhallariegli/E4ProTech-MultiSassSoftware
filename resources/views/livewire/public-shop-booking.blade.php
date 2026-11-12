<div id="booking-section" class="py-6 sm:py-10 bg-white border border-stone-200/80 rounded-[2rem] sm:rounded-[2.5rem] p-4 sm:p-10 shadow-2xl transition-all duration-300">
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
        <div class="text-center py-8 sm:py-10 space-y-6">
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
        <!-- SLEEK PROGRESS BAR HEADER -->
        <div class="mb-6 space-y-3">
            <div class="flex items-center justify-between gap-2">
                @if($currentStep > 1)
                    <button type="button" wire:click="previousStep" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-black transition">
                        <span>←</span> <span>{{ __('public_booking.Back') }}</span>
                    </button>
                @else
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-stone-100 text-stone-600 text-[11px] font-black">
                        <span class="w-2 h-2 rounded-full animate-pulse" style="background-color: {{ $primaryColor }};"></span>
                        <span>{{ __('public_booking.Automated 24/7 Booking') }}</span>
                    </div>
                @endif

                <span class="text-xs font-black text-slate-500 uppercase tracking-wider">
                    {{ __('public_booking.Step Count', ['step' => $currentStep, 'total' => 4]) }}
                </span>
            </div>

            <!-- Progress Line Bar -->
            <div class="w-full h-2 bg-stone-100 rounded-full overflow-hidden">
                <div class="h-full transition-all duration-300 rounded-full"
                     style="width: {{ $currentStep * 25 }}%; background-color: {{ $primaryColor }};"></div>
            </div>
        </div>

        <form wire:submit.prevent="submitBooking" class="space-y-6">

            <!-- STEP 1: SELECT SERVICE -->
            @if($currentStep === 1)
                <div class="space-y-4">
                    <div class="border-b border-stone-200 pb-3">
                        <h3 class="text-xl sm:text-2xl font-black text-[#1A1D20] tracking-tight">
                            {{ __('public_booking.1 Select Service') }}
                        </h3>
                        <p class="text-xs text-slate-500 font-bold mt-1">
                            {{ __('public_booking.Click service to proceed') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($services as $s)
                            <button type="button"
                                    wire:click="selectService({{ $s->id }})"
                                    class="p-4 rounded-2xl border text-left transition flex items-center justify-between cursor-pointer active:scale-98 group hover:shadow-md"
                                    style="{{ $selectedServiceId === $s->id ? 'border-color: '.$primaryColor.'; background-color: '.$primaryColor.'15; box-shadow: 0 0 0 2px '.$primaryColor.'40;' : 'border-color: #e2e8f0; background-color: #FAF8F2;' }}">
                                <div class="pr-2">
                                    <h4 class="font-extrabold text-sm text-[#1A1D20] flex items-center gap-1.5">
                                        <span>{{ $s->name }}</span>
                                        @if($selectedServiceId === $s->id)
                                            <span class="text-xs font-bold text-emerald-600">✓</span>
                                        @endif
                                    </h4>
                                    <span class="text-xs text-slate-500 font-medium">{{ $s->duration_minutes }} min</span>
                                </div>
                                <span class="font-black text-sm whitespace-nowrap shrink-0" style="color: {{ $secondaryColor }};">{{ $s->formatted_price_range }}</span>
                            </button>
                        @endforeach
                    </div>
                    @error('selectedServiceId') <p class="text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror

                    @if($selectedServiceId)
                        <div class="pt-2 flex justify-end">
                            <button type="button" wire:click="nextStep" class="w-full sm:w-auto px-8 py-3.5 rounded-full text-xs font-black text-white shadow-xl transition transform active:scale-95 flex items-center justify-center gap-2"
                                    style="background-color: {{ $primaryColor }};">
                                <span>{{ __('public_booking.Continue to Staff') }}</span> <span>➔</span>
                            </button>
                        </div>
                    @endif
                </div>

            <!-- STEP 2: SELECT STAFF -->
            @elseif($currentStep === 2)
                <div class="space-y-4">
                    <div class="border-b border-stone-200 pb-3">
                        <h3 class="text-xl sm:text-2xl font-black text-[#1A1D20] tracking-tight">
                            {{ __('public_booking.2 Select Staff Member') }}
                        </h3>
                        <p class="text-xs text-slate-500 font-bold mt-1">
                            {{ __('public_booking.Click staff to proceed') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($staff as $st)
                            <button type="button"
                                    wire:click="selectBarber({{ $st->id }})"
                                    class="p-4 rounded-2xl border text-center transition cursor-pointer active:scale-98 hover:shadow-md"
                                    style="{{ $selectedBarberId === $st->id ? 'border-color: '.$primaryColor.'; background-color: '.$primaryColor.'15; box-shadow: 0 0 0 2px '.$primaryColor.'40;' : 'border-color: #e2e8f0; background-color: #FAF8F2;' }}">
                                <div class="w-12 h-12 rounded-2xl font-black flex items-center justify-center mx-auto mb-2 text-lg relative"
                                     style="background-color: {{ $secondaryColor }}15; color: {{ $secondaryColor }}; border: 1px solid {{ $secondaryColor }}30;">
                                    {{ mb_substr($st->name, 0, 1) }}
                                    @if($selectedBarberId === $st->id)
                                        <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-emerald-500 text-white text-[10px] font-black flex items-center justify-center border-2 border-white">✓</span>
                                    @endif
                                </div>
                                <h5 class="font-extrabold text-xs text-[#1A1D20] truncate">{{ $st->name }}</h5>
                            </button>
                        @endforeach
                    </div>
                    @error('selectedBarberId') <p class="text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror

                    <div class="pt-2 flex items-center justify-between gap-3">
                        <button type="button" wire:click="previousStep" class="px-5 py-3 rounded-full text-xs font-bold text-slate-700 bg-stone-100 hover:bg-stone-200 transition">
                            ← {{ __('public_booking.Back') }}
                        </button>
                        @if($selectedBarberId)
                            <button type="button" wire:click="nextStep" class="px-8 py-3.5 rounded-full text-xs font-black text-white shadow-xl transition transform active:scale-95 flex items-center gap-2"
                                    style="background-color: {{ $primaryColor }};">
                                <span>{{ __('public_booking.Continue to Schedule') }}</span> <span>➔</span>
                            </button>
                        @endif
                    </div>
                </div>

            <!-- STEP 3: DATE & TIME -->
            @elseif($currentStep === 3)
                <div class="space-y-6">
                    <div class="border-b border-stone-200 pb-3">
                        <h3 class="text-xl sm:text-2xl font-black text-[#1A1D20] tracking-tight">
                            {{ __('public_booking.Select date and time') }}
                        </h3>
                    </div>

                    <!-- Top Row: 7-Day Interactive Date Buttons Strip -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-xs font-black text-[#1A1D20] uppercase tracking-wider">
                                {{ __('public_booking.3 Appointment Date') }}
                            </label>
                            @if($bookingDate)
                                <span class="text-xs font-black text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
                                    {{ \Carbon\Carbon::parse($bookingDate)->format('d/m/Y') }}
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-7 gap-1 sm:gap-2">
                            @foreach($availableDates as $d)
                                <button type="button"
                                        wire:click="selectBookingDate('{{ $d['date'] }}')"
                                        class="py-2.5 px-0.5 rounded-xl sm:rounded-2xl border text-center transition flex flex-col items-center justify-center gap-0.5 cursor-pointer active:scale-95"
                                        style="{{ $bookingDate === $d['date'] ? 'background-color: '.$primaryColor.'; border-color: '.$primaryColor.'; color: #ffffff; box-shadow: 0 4px 12px '.$primaryColor.'40;' : 'background-color: #FAF8F2; border-color: #e2e8f0; color: #1A1D20;' }}">
                                    <span class="text-[10px] font-black uppercase {{ $bookingDate === $d['date'] ? 'text-amber-300' : 'text-slate-500' }}">{{ $d['day'] }}</span>
                                    <span class="text-xs sm:text-sm font-black">{{ $d['day_num'] }}</span>
                                </button>
                            @endforeach
                        </div>
                        @error('bookingDate') <p class="text-xs text-rose-500 mt-1.5 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <!-- Bottom Row: Time Slots Grid Chips -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-xs font-black text-[#1A1D20] uppercase tracking-wider">
                                {{ __('public_booking.4 Appointment Time') }}
                            </label>
                            @if(!empty($availableTimeSlots))
                                <span class="text-xs font-bold text-slate-500">
                                    {{ count($availableTimeSlots) }} {{ app()->getLocale() === 'en' ? 'slots available' : 'orare të lira' }}
                                </span>
                            @endif
                        </div>

                        @if(!empty($availableTimeSlots))
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 sm:gap-2.5 max-h-[280px] overflow-y-auto p-1">
                                @foreach($availableTimeSlots as $timeSlot)
                                    <button type="button"
                                            wire:click="$set('bookingTime', '{{ $timeSlot }}')"
                                            class="py-3 px-3 rounded-2xl text-xs font-black transition-all flex items-center justify-center gap-1 cursor-pointer active:scale-95"
                                            style="{{ $bookingTime === $timeSlot ? 'background-color: '.$primaryColor.'; color: #ffffff; box-shadow: 0 4px 12px '.$primaryColor.'40;' : 'background-color: #FAF8F2; border: 1px solid #e2e8f0; color: #1A1D20;' }}">
                                        <span>{{ $timeSlot }}</span>
                                        @if($bookingTime === $timeSlot)
                                            <span class="text-[10px] text-emerald-300">✓</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <div class="p-6 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-800 font-bold text-xs text-center space-y-1">
                                <p class="text-sm font-black">{{ __('public_booking.No Slots Available') }}</p>
                                <p class="text-[11px] opacity-80">Ju lutemi zgjidhni një datë tjetër për të parë oraret e lira.</p>
                            </div>
                        @endif
                        @error('bookingTime') <p class="text-xs text-rose-500 mt-1.5 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-2 flex items-center justify-between gap-3">
                        <button type="button" wire:click="previousStep" class="px-5 py-3 rounded-full text-xs font-bold text-slate-700 bg-stone-100 hover:bg-stone-200 transition">
                            ← {{ __('public_booking.Back') }}
                        </button>
                        @if($bookingDate && $bookingTime)
                            <button type="button" wire:click="nextStep" class="px-8 py-3.5 rounded-full text-xs font-black text-white shadow-xl transition transform active:scale-95 flex items-center gap-2"
                                    style="background-color: {{ $primaryColor }};">
                                <span>{{ __('public_booking.Continue to Details') }}</span> <span>➔</span>
                            </button>
                        @endif
                    </div>
                </div>

            <!-- STEP 4: CUSTOMER DETAILS & CONFIRMATION -->
            @elseif($currentStep === 4)
                <div class="space-y-6">
                    <div class="border-b border-stone-200 pb-3">
                        <h3 class="text-xl sm:text-2xl font-black text-[#1A1D20] tracking-tight">
                            {{ __('public_booking.Fill customer details') }}
                        </h3>
                        <p class="text-xs text-slate-500 font-bold mt-1">
                            {{ __('public_booking.Review summary and enter info') }}
                        </p>
                    </div>

                    <!-- Summary Card -->
                    <div class="p-5 rounded-2xl text-white text-xs space-y-2.5 shadow-md" style="background-color: {{ $secondaryColor }};">
                        <div class="flex justify-between border-b border-white/20 pb-2">
                            <span class="text-teal-100 font-medium">{{ __('public_booking.Service') }}</span>
                            <span class="font-extrabold text-white">{{ $selectedService?->name ?: '-' }} ({{ $selectedService?->duration_minutes }} min)</span>
                        </div>
                        <div class="flex justify-between border-b border-white/20 pb-2">
                            <span class="text-teal-100 font-medium">{{ __('public_booking.Staff Member') }}</span>
                            <span class="font-extrabold text-white">{{ $selectedBarber?->name ?: '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-white/20 pb-2">
                            <span class="text-teal-100 font-medium">{{ __('public_booking.Date & Time') }}</span>
                            <span class="font-black text-amber-300">{{ \Carbon\Carbon::parse($bookingDate)->format('d/m/Y') }} | {{ $bookingTime }}</span>
                        </div>
                        <div class="flex justify-between pt-0.5">
                            <span class="text-teal-100 font-medium">{{ __('public_booking.Total Price') }}</span>
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

                    <div class="pt-2 flex items-center justify-between gap-3">
                        <button type="button" wire:click="previousStep" class="px-5 py-3.5 rounded-full text-xs font-bold text-slate-700 bg-stone-100 hover:bg-stone-200 transition shrink-0">
                            ← {{ __('public_booking.Back') }}
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
