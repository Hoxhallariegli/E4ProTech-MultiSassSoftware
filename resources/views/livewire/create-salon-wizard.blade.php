<div class="min-h-screen flex flex-col justify-between bg-[#FAF8F2] text-[#1A1D20] antialiased selection:bg-[#7C5CFC] selection:text-white">

    <!-- Top Navigation Bar (E4ProTech Style) -->
    <header class="sticky top-0 z-50 backdrop-blur-md bg-[#FAF8F2]/90 border-b border-stone-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-2xl bg-[#0A4D44] flex items-center justify-center font-black text-lg text-white shadow-md transition transform group-hover:scale-105">
                    E4
                </div>
                <div>
                    <h1 class="text-xl font-black tracking-tight text-[#1A1D20] leading-none">
                        E4PROTECH
                    </h1>
                    <p class="text-[10px] text-[#0A4D44] font-extrabold tracking-wider uppercase">Engine Multi-SaaS</p>
                </div>
            </a>

            <!-- Nav Links -->
            <nav class="hidden md:flex items-center gap-8 text-xs font-bold text-slate-700">
                <a href="/" class="hover:text-[#7C5CFC] transition">Kreu</a>
                <a href="/#how-it-works" class="hover:text-[#7C5CFC] transition">Si Funksionon</a>
                <a href="/#services" class="hover:text-[#7C5CFC] transition">Shërbimet</a>
                <a href="/#sallonet" class="hover:text-[#7C5CFC] transition">Sallonet</a>
            </nav>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.settings') }}" class="px-5 py-2.5 rounded-full text-xs font-extrabold bg-[#1A1D20] text-white hover:bg-black transition shadow-sm">
                    Hyr te Paneli ↗
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container Area -->
    <main class="py-12 lg:py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full flex-grow">

        <!-- Header Text -->
        <div class="text-center max-w-2xl mx-auto mb-10">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-black bg-[#0A4D44]/10 text-[#0A4D44] mb-4">
                <span class="w-2 h-2 rounded-full bg-[#7C5CFC] animate-pulse"></span>
                Saloni juaj, gati në 5 minuta
            </div>
            <h1 class="text-3xl sm:text-5xl font-black text-[#1A1D20] tracking-tight leading-tight mb-3">
                Krijo Faqen e Rezervimeve për Sallonin Tuaj
            </h1>
            <p class="text-sm text-slate-500 font-medium">
                Krijojeni dhe provojeni falas. Paguani vetëm kur bëhet publik — nuk kërkohet kartë krediti.
            </p>
        </div>

        <!-- Wizard Card Container -->
        <div class="w-full max-w-xl mx-auto p-8 sm:p-10 rounded-[2.5rem] bg-white border border-stone-200/80 shadow-2xl relative overflow-hidden">

            @if($step === 1)
                <!-- Step 1 Form -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-black uppercase text-[#7C5CFC]">Hapi 1 nga 3</span>
                        <span class="text-xs font-bold text-slate-400">Informacioni i Sallonit</span>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full h-2 bg-slate-100 rounded-full mb-8 overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-[#7C5CFC] to-[#6366F1] w-1/3 transition-all duration-300"></div>
                    </div>

                    <form wire:submit.prevent="nextStep" class="space-y-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Emri i sallonit *</label>
                            <input type="text" wire:model.live="salonName" placeholder="p.sh: Elegance Beauty Salon"
                                   class="w-full px-5 py-4 rounded-2xl bg-[#FAF8F2] border border-slate-200 text-[#1A1D20] text-sm font-bold focus:border-[#7C5CFC] focus:outline-none transition">
                            @error('salonName') <p class="text-xs text-rose-500 mt-1.5 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Adresa e faqeve *</label>
                            <div class="flex items-center rounded-2xl bg-[#FAF8F2] border border-slate-200 overflow-hidden px-5 py-4 focus-within:border-[#7C5CFC] transition">
                                <span class="text-xs text-slate-400 font-bold mr-1">app.e4protech.com/s/</span>
                                <input type="text" wire:model="salonSlug" placeholder="elegance-beauty"
                                       class="w-full bg-transparent text-[#1A1D20] text-sm font-bold focus:outline-none">
                            </div>
                            @error('salonSlug') <p class="text-xs text-rose-500 mt-1.5 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Lloji i biznesit</label>
                            <select wire:model="businessType"
                                    class="w-full px-5 py-4 rounded-2xl bg-[#FAF8F2] border border-slate-200 text-[#1A1D20] text-sm font-bold focus:border-[#7C5CFC] focus:outline-none">
                                <option value="barber">💈 Barber Shop / Sallon Qethje</option>
                                <option value="beauty">💇‍♀️ Beauty Salon / Parukeri</option>
                                <option value="nails">💅 Nail Studio / Qendër Thonjsh</option>
                                <option value="spa">🌸 Spa &amp; Massage</option>
                            </select>
                        </div>

                        <button type="submit"
                                class="w-full py-4 rounded-full font-black text-sm text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-lg shadow-[#7C5CFC]/25 transition transform active:scale-95 flex items-center justify-center gap-2">
                            <span>Vazhdo ↗</span>
                        </button>
                    </form>
                </div>

            @elseif($step === 2)
                <!-- Step 2 Form -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-black uppercase text-[#7C5CFC]">Hapi 2 nga 3</span>
                        <span class="text-xs font-bold text-slate-400">Të dhënat e Pronarit</span>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full h-2 bg-slate-100 rounded-full mb-8 overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-[#7C5CFC] to-[#6366F1] w-2/3 transition-all duration-300"></div>
                    </div>

                    <form wire:submit.prevent="nextStep" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Emri &amp; Mbiemri Juaj *</label>
                            <input type="text" wire:model="ownerName" placeholder="p.sh: Albano Hoxha"
                                   class="w-full px-5 py-3.5 rounded-2xl bg-[#FAF8F2] border border-slate-200 text-[#1A1D20] text-sm font-bold focus:border-[#7C5CFC] focus:outline-none transition">
                            @error('ownerName') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Numri i Telefonit *</label>
                            <input type="text" wire:model="ownerPhone" placeholder="p.sh: +355691234567"
                                   class="w-full px-5 py-3.5 rounded-2xl bg-[#FAF8F2] border border-slate-200 text-[#1A1D20] text-sm font-bold focus:border-[#7C5CFC] focus:outline-none transition">
                            @error('ownerPhone') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Adresa e E-mailit *</label>
                            <input type="email" wire:model="email" placeholder="p.sh: pronari@salloni.al"
                                   class="w-full px-5 py-3.5 rounded-2xl bg-[#FAF8F2] border border-slate-200 text-[#1A1D20] text-sm font-bold focus:border-[#7C5CFC] focus:outline-none transition">
                            @error('email') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Fjalëkalimi *</label>
                            <input type="password" wire:model="password" placeholder="••••••••"
                                   class="w-full px-5 py-3.5 rounded-2xl bg-[#FAF8F2] border border-slate-200 text-[#1A1D20] text-sm font-bold focus:border-[#7C5CFC] focus:outline-none transition">
                            @error('password') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" wire:click="previousStep"
                                    class="w-1/3 py-3.5 rounded-full font-bold text-xs text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                                Mbrapsht
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                    class="w-2/3 py-3.5 rounded-full font-black text-xs text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-lg shadow-[#7C5CFC]/20 transition transform active:scale-95 flex items-center justify-center gap-2">
                                <span wire:loading.remove>Krijo Sallonin Tim 🚀</span>
                                <span wire:loading>Duke krijuar...</span>
                            </button>
                        </div>
                    </form>
                </div>

            @elseif($step === 3)
                <!-- Step 3 Success -->
                <div class="text-center py-4">
                    <div class="w-16 h-16 rounded-full bg-emerald-100 border border-emerald-300 text-emerald-600 flex items-center justify-center mx-auto mb-4 text-2xl font-black">
                        ✓
                    </div>
                    <h2 class="text-2xl font-black text-[#1A1D20] mb-2">Salloni juaj u krijua me sukses! 🎉</h2>
                    <p class="text-xs text-slate-600 leading-relaxed mb-6">
                        Urimë! Faqja juaj e re publike është aktive te adresa:<br>
                        <strong class="text-[#7C5CFC]">app.e4protech.com/s/{{ $createdShop?->slug }}</strong>
                    </p>

                    <a href="{{ route('dashboard') }}"
                       class="w-full py-4 rounded-full font-black text-sm text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-lg shadow-[#7C5CFC]/20 transition block text-center">
                        Hyr në Panelin Admin 🚀
                    </a>
                </div>
            @endif

        </div>
    </main>

    <!-- Footer (E4ProTech Dark Teal `#0A4D44`) -->
    <footer class="py-10 bg-[#0A4D44] text-white mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-white text-[#0A4D44] font-black flex items-center justify-center text-sm">
                    E4
                </div>
                <p class="text-xs text-teal-100 font-medium">
                    © {{ date('Y') }} E4ProTech Engine — Managed IT &amp; SaaS Solutions
                </p>
            </div>

            <div class="flex gap-6 text-xs text-teal-100 font-bold">
                <a href="/" class="hover:text-amber-300 transition">Kreu</a>
                <a href="{{ route('admin.settings') }}" class="hover:text-amber-300 transition">Paneli Admin</a>
                <a href="{{ route('app.download.apk') }}" class="hover:text-amber-300 transition">Shkarko APK-në</a>
            </div>
        </div>
    </footer>

</div>
