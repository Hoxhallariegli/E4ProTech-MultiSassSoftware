<div id="booking-section" class="py-12 bg-slate-900/80 border-t border-slate-800/80 rounded-3xl p-6 sm:p-10 shadow-2xl">
    @if($bookingSuccess && $createdBooking)
        <div class="text-center py-10 space-y-6">
            <div class="w-20 h-20 bg-emerald-500/20 border-2 border-emerald-500 text-emerald-400 rounded-full flex items-center justify-center mx-auto text-4xl shadow-lg">
                ✓
            </div>
            <h3 class="text-2xl sm:text-3xl font-black text-white">Rezervimi u Krye me Sukses! 🎯</h3>
            <p class="text-slate-300 text-sm max-w-md mx-auto">
                Faleminderit <strong class="text-white">{{ $customerName }}</strong>! Takimi juaj u regjistrua me sukses te <strong class="text-amber-400">{{ $shop->name }}</strong>.
            </p>

            <div class="p-6 rounded-2xl bg-slate-950 border border-slate-800 text-left max-w-md mx-auto space-y-3 text-xs">
                <div class="flex justify-between border-b border-slate-800 pb-2">
                    <span class="text-slate-400">Pjesëtari i Stafit:</span>
                    <span class="font-bold text-white">{{ $createdBooking->barber?->name }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-800 pb-2">
                    <span class="text-slate-400">Shërbimi:</span>
                    <span class="font-bold text-white">{{ $createdBooking->service?->name }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-800 pb-2">
                    <span class="text-slate-400">Data & Ora:</span>
                    <span class="font-bold text-emerald-400">{{ $createdBooking->appointment_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between pt-1">
                    <span class="text-slate-400">Çmimi Total:</span>
                    <span class="font-extrabold text-white text-sm">{{ number_format($createdBooking->total_price, 0) }} Lekë</span>
                </div>
            </div>

            <button wire:click="resetForm" class="px-8 py-3 rounded-2xl font-black text-xs text-white bg-slate-800 hover:bg-slate-700 transition">
                + Bëj një Rezervim Tjetër
            </button>
        </div>
    @else
        <form wire:submit.prevent="submitBooking" class="space-y-8">
            <div>
                <h3 class="text-2xl font-black text-white flex items-center gap-2">
                    <span>📅</span> Rezervo Takim Online
                </h3>
                <p class="text-xs text-slate-400 mt-1">Zgjidhni shërbimin, personin dhe orarin tuaj të preferuar</p>
            </div>

            <!-- 1. Select Service -->
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-3">1. Zgjidhni Shërbimin</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($services as $s)
                        <button type="button"
                                wire:click="selectService({{ $s->id }})"
                                class="p-4 rounded-2xl border text-left transition flex items-center justify-between {{ $selectedServiceId === $s->id ? 'border-amber-500 bg-amber-500/10 ring-2 ring-amber-500/30' : 'border-slate-800 bg-slate-950/60 hover:border-slate-700' }}">
                            <div>
                                <h4 class="font-extrabold text-sm text-white">{{ $s->name }}</h4>
                                <span class="text-xs text-slate-400 font-medium">{{ $s->duration_minutes }} min</span>
                            </div>
                            <span class="font-black text-emerald-400 text-sm whitespace-nowrap">{{ number_format($s->price, 0) }} Lekë</span>
                        </button>
                    @endforeach
                </div>
                @error('selectedServiceId') <p class="text-xs text-rose-400 mt-1 font-bold">{{ $message }}</p> @enderror
            </div>

            <!-- 2. Select Staff -->
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-3">2. Zgjidhni {{ $shop->resolved_staff_label }}un</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($staff as $st)
                        <button type="button"
                                wire:click="selectBarber({{ $st->id }})"
                                class="p-3.5 rounded-2xl border text-center transition {{ $selectedBarberId === $st->id ? 'border-amber-500 bg-amber-500/10 ring-2 ring-amber-500/30' : 'border-slate-800 bg-slate-950/60 hover:border-slate-700' }}">
                            <div class="w-10 h-10 rounded-full bg-slate-800 text-white font-bold flex items-center justify-center mx-auto mb-2" style="color: {{ $shop->primary_color }};">
                                {{ mb_substr($st->name, 0, 1) }}
                            </div>
                            <h5 class="font-bold text-xs text-white truncate">{{ $st->name }}</h5>
                        </button>
                    @endforeach
                </div>
                @error('selectedBarberId') <p class="text-xs text-rose-400 mt-1 font-bold">{{ $message }}</p> @enderror
            </div>

            <!-- 3. Date & Time -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">3. Data e Takimit</label>
                    <input type="date" wire:model.live="bookingDate" min="{{ date('Y-m-d') }}"
                           class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white font-bold text-sm focus:border-amber-500 focus:outline-none">
                    @error('bookingDate') <p class="text-xs text-rose-400 mt-1 font-bold">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">4. Ora e Takimit</label>
                    <select wire:model.live="bookingTime"
                            class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white font-bold text-sm focus:border-amber-500 focus:outline-none">
                        @foreach(['09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30'] as $timeSlot)
                            <option value="{{ $timeSlot }}">{{ $timeSlot }}</option>
                        @endforeach
                    </select>
                    @error('bookingTime') <p class="text-xs text-rose-400 mt-1 font-bold">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- 4. Customer Info -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-slate-800 pt-6">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Emri & Mbiemri Juaj *</label>
                    <input type="text" wire:model.live="customerName" placeholder="p.sh: Albano Hoxha"
                           class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white text-sm focus:border-amber-500 focus:outline-none">
                    @error('customerName') <p class="text-xs text-rose-400 mt-1 font-bold">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Numri i Telefonit *</label>
                    <input type="text" wire:model.live="customerPhone" placeholder="p.sh: +355691234567"
                           class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white text-sm focus:border-amber-500 focus:outline-none">
                    @error('customerPhone') <p class="text-xs text-rose-400 mt-1 font-bold">{{ $message }}</p> @enderror
                </div>
            </div>

            <button type="submit" wire:loading.attr="disabled"
                    class="w-full py-4 rounded-2xl font-black text-sm text-white shadow-xl transition transform active:scale-95 flex items-center justify-center gap-2"
                    style="background: linear-gradient(135deg, {{ $shop->primary_color }}, {{ $shop->secondary_color }});">
                <span wire:loading.remove>Konfirmo Rezervimin Online 🚀</span>
                <span wire:loading>Duke dërguar rezervimin...</span>
            </button>
        </form>
    @endif
</div>
