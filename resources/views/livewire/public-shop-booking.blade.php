<div id="booking-section" class="py-10 bg-white border border-stone-200/80 rounded-[2.5rem] p-6 sm:p-12 shadow-2xl">
    @php
        $primaryColor = !empty($shop->primary_color) ? $shop->primary_color : '#7C5CFC';
        $secondaryColor = !empty($shop->secondary_color) ? $shop->secondary_color : '#0A4D44';
        if (in_array(strtolower($secondaryColor), ['#1c1c1e', '#1c1c1f', '#000000', '#111827'])) {
            $secondaryColor = '#0A4D44';
        }
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
                {{ app()->getLocale() === 'en'
                    ? "Online booking for {$shop->name} is temporarily suspended as the shop subscription has expired. Please contact the salon directly."
                    : "Faqja e rezervimeve online për {$shop->name} është e pezulluar përkohësisht pasi abonimi i sallonit ka përfunduar. Ju lutemi kontaktoni drejtpërdrejt sallonin." }}
            </p>
        </div>
    @elseif($bookingSuccess && $createdBooking)
        <div class="text-center py-10 space-y-6">
            <div class="w-20 h-20 bg-emerald-100 border-2 border-emerald-500 text-emerald-700 rounded-full flex items-center justify-center mx-auto text-4xl shadow-lg">
                ✓
            </div>
            <h3 class="text-2xl sm:text-3xl font-black text-[#1A1D20]">
                {{ __('public_booking.Booking Completed') }}
            </h3>
            <p class="text-slate-600 text-sm max-w-md mx-auto font-medium">
                {{ app()->getLocale() === 'en'
                    ? "Thank you {$customerName}! Your appointment has been registered at {$shop->name}."
                    : "Faleminderit {$customerName}! Takimi juaj u regjistrua me sukses te {$shop->name}." }}
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
        <form wire:submit.prevent="submitBooking" class="space-y-8">
            <div class="border-b border-stone-200 pb-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-black mb-3"
                     style="background-color: {{ $secondaryColor }}1A; color: {{ $secondaryColor }};">
                    <span class="w-2 h-2 rounded-full animate-pulse" style="background-color: {{ $primaryColor }};"></span>
                    {{ __('public_booking.Automated 24/7 Booking') }}
                </div>
                <h3 class="text-2xl sm:text-3xl font-black text-[#1A1D20] tracking-tight">
                    {{ __('public_booking.Book Appointment Online') }}
                </h3>
                <p class="text-xs text-slate-500 font-medium mt-1">
                    {{ __('public_booking.Booking Subtitle') }}
                </p>
            </div>

            <!-- 1. Select Service -->
            <div>
                <label class="block text-xs font-extrabold text-[#1A1D20] uppercase tracking-wider mb-3">
                    {{ __('public_booking.1 Select Service') }}
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($services as $s)
                        <button type="button"
                                wire:click="selectService({{ $s->id }})"
                                class="p-4 rounded-2xl border text-left transition flex items-center justify-between"
                                style="{{ $selectedServiceId === $s->id ? 'border-color: '.$primaryColor.'; background-color: '.$primaryColor.'15; box-shadow: 0 0 0 2px '.$primaryColor.'40;' : 'border-color: #e2e8f0; background-color: #FAF8F2;' }}">
                            <div>
                                <h4 class="font-extrabold text-sm text-[#1A1D20]">{{ $s->name }}</h4>
                                <span class="text-xs text-slate-500 font-medium">{{ $s->duration_minutes }} min</span>
                            </div>
                            <span class="font-black text-sm whitespace-nowrap" style="color: {{ $secondaryColor }};">{{ number_format($s->price, 0) }} {{ $shop->resolved_currency }}</span>
                        </button>
                    @endforeach
                </div>
                @error('selectedServiceId') <p class="text-xs text-rose-500 mt-1.5 font-bold">{{ $message }}</p> @enderror
            </div>

            <!-- 2. Select Staff -->
            <div>
                <label class="block text-xs font-extrabold text-[#1A1D20] uppercase tracking-wider mb-3">
                    {{ __('public_booking.2 Select Staff Member') }}
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($staff as $st)
                        <button type="button"
                                wire:click="selectBarber({{ $st->id }})"
                                class="p-4 rounded-2xl border text-center transition"
                                style="{{ $selectedBarberId === $st->id ? 'border-color: '.$primaryColor.'; background-color: '.$primaryColor.'15; box-shadow: 0 0 0 2px '.$primaryColor.'40;' : 'border-color: #e2e8f0; background-color: #FAF8F2;' }}">
                            <div class="w-12 h-12 rounded-2xl font-black flex items-center justify-center mx-auto mb-2 text-lg"
                                 style="background-color: {{ $secondaryColor }}15; color: {{ $secondaryColor }}; border: 1px solid {{ $secondaryColor }}30;">
                                {{ mb_substr($st->name, 0, 1) }}
                            </div>
                            <h5 class="font-extrabold text-xs text-[#1A1D20] truncate">{{ $st->name }}</h5>
                        </button>
                    @endforeach
                </div>
                @error('selectedBarberId') <p class="text-xs text-rose-500 mt-1.5 font-bold">{{ $message }}</p> @enderror
            </div>

            <!-- 3. Date & Time -->
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

            <!-- 4. Customer Info -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-stone-200 pt-6">
                <div>
                    <label class="block text-xs font-extrabold text-[#1A1D20] uppercase tracking-wider mb-2">
                        {{ __('public_booking.Your Name') }}
                    </label>
                    <input type="text" wire:model.live="customerName" placeholder="p.sh: Albano Hoxha"
                           class="w-full px-5 py-3.5 rounded-2xl bg-[#FAF8F2] border border-slate-200 text-[#1A1D20] text-sm font-bold focus:outline-none transition">
                    @error('customerName') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-extrabold text-[#1A1D20] uppercase tracking-wider mb-2">
                        {{ __('public_booking.Phone Number') }}
                    </label>
                    <input type="text" wire:model.live="customerPhone" placeholder="p.sh: +355691234567"
                           class="w-full px-5 py-3.5 rounded-2xl bg-[#FAF8F2] border border-slate-200 text-[#1A1D20] text-sm font-bold focus:outline-none transition">
                    @error('customerPhone') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                </div>
            </div>

            <button type="submit" wire:loading.attr="disabled"
                    class="w-full py-4 rounded-full font-black text-sm text-white shadow-xl transition transform active:scale-95 flex items-center justify-center gap-2"
                    style="background-color: {{ $primaryColor }};">
                <span wire:loading.remove>{{ __('public_booking.Confirm Booking') }}</span>
                <span wire:loading>{{ __('public_booking.Submitting') }}</span>
            </button>
        </form>
    @endif
</div>
