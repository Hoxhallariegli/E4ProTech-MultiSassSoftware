<!DOCTYPE html>
<html lang="sq" class="h-full bg-[#0D0E12] text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Krijo Sallonin Tënd | E4ProTech Engine</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-[#0D0E12] flex items-center justify-center p-4 antialiased selection:bg-[#FF9F0A] selection:text-black">

    <div class="w-full max-w-md">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center justify-center w-16 h-16 rounded-3xl bg-gradient-to-tr from-[#FF9F0A] to-amber-500 shadow-xl shadow-[#FF9F0A]/20 mb-4 font-black text-2xl text-black">
                S
            </a>
            <h1 class="text-2xl font-black text-white tracking-tight mb-2">Saloni juaj, gati në 5 minuta.</h1>
            <p class="text-xs text-slate-400 font-medium max-w-xs mx-auto">
                Krijojeni dhe provojeni falas. Paguani vetëm kur bëhet publik — nuk kërkohet kartë.
            </p>
        </div>

        <!-- Card Container -->
        <div class="p-8 rounded-[2rem] bg-[#16181E] border border-slate-800/80 shadow-2xl relative overflow-hidden">

            @if($step === 1)
                <!-- Step 1 Form -->
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-xs font-bold text-[#FF9F0A]">Hapi 1 nga 3</span>
                        <span class="text-xs font-bold text-slate-500">Informacioni i Sallonit</span>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full h-1.5 bg-slate-800 rounded-full mb-8 overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-[#FF9F0A] to-amber-400 w-1/3 transition-all duration-300"></div>
                    </div>

                    <form wire:submit.prevent="nextStep" class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Emri i salonit *</label>
                            <input type="text" wire:model.live="salonName" placeholder="p.sh: Elegance Beauty Salon"
                                   class="w-full px-4 py-3.5 rounded-2xl bg-[#0D0E12] border border-slate-800 text-white text-sm font-bold focus:border-[#FF9F0A] focus:outline-none transition">
                            @error('salonName') <p class="text-xs text-rose-400 mt-1.5 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Adresa e faqeve *</label>
                            <div class="flex items-center rounded-2xl bg-[#0D0E12] border border-slate-800 overflow-hidden px-4 py-3.5 focus-within:border-[#FF9F0A] transition">
                                <span class="text-xs text-slate-500 font-bold mr-1">app.e4protech.com/s/</span>
                                <input type="text" wire:model="salonSlug" placeholder="elegance-beauty"
                                       class="w-full bg-transparent text-white text-sm font-bold focus:outline-none">
                            </div>
                            @error('salonSlug') <p class="text-xs text-rose-400 mt-1.5 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Lloji i biznesit</label>
                            <select wire:model="businessType"
                                    class="w-full px-4 py-3.5 rounded-2xl bg-[#0D0E12] border border-slate-800 text-white text-sm font-bold focus:border-[#FF9F0A] focus:outline-none">
                                <option value="barber">💈 Barber Shop / Sallon Qethje</option>
                                <option value="beauty">💇‍♀️ Beauty Salon / Parukeri</option>
                                <option value="nails">💅 Nail Studio / Qendër Thonjsh</option>
                                <option value="spa">🌸 Spa & Massage</option>
                            </select>
                        </div>

                        <button type="submit"
                                class="w-full py-4 rounded-2xl font-black text-sm text-black bg-[#FF9F0A] hover:bg-amber-400 shadow-lg shadow-[#FF9F0A]/20 transition transform active:scale-95 flex items-center justify-center gap-2">
                            <span>Vazhdo</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>
                </div>

            @elseif($step === 2)
                <!-- Step 2 Form -->
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-xs font-bold text-[#FF9F0A]">Hapi 2 nga 3</span>
                        <span class="text-xs font-bold text-slate-500">Të dhënat e Pronarit</span>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full h-1.5 bg-slate-800 rounded-full mb-8 overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-[#FF9F0A] to-amber-400 w-2/3 transition-all duration-300"></div>
                    </div>

                    <form wire:submit.prevent="nextStep" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Emri & Mbiemri Juaj *</label>
                            <input type="text" wire:model="ownerName" placeholder="p.sh: Albano Hoxha"
                                   class="w-full px-4 py-3 rounded-2xl bg-[#0D0E12] border border-slate-800 text-white text-sm font-bold focus:border-[#FF9F0A] focus:outline-none transition">
                            @error('ownerName') <p class="text-xs text-rose-400 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Numri i Telefonit *</label>
                            <input type="text" wire:model="ownerPhone" placeholder="p.sh: +355691234567"
                                   class="w-full px-4 py-3 rounded-2xl bg-[#0D0E12] border border-slate-800 text-white text-sm font-bold focus:border-[#FF9F0A] focus:outline-none transition">
                            @error('ownerPhone') <p class="text-xs text-rose-400 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Adresa e E-mailit *</label>
                            <input type="email" wire:model="email" placeholder="p.sh: pronari@salloni.al"
                                   class="w-full px-4 py-3 rounded-2xl bg-[#0D0E12] border border-slate-800 text-white text-sm font-bold focus:border-[#FF9F0A] focus:outline-none transition">
                            @error('email') <p class="text-xs text-rose-400 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Fjalëkalimi *</label>
                            <input type="password" wire:model="password" placeholder="••••••••"
                                   class="w-full px-4 py-3 rounded-2xl bg-[#0D0E12] border border-slate-800 text-white text-sm font-bold focus:border-[#FF9F0A] focus:outline-none transition">
                            @error('password') <p class="text-xs text-rose-400 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" wire:click="previousStep"
                                    class="w-1/3 py-3.5 rounded-2xl font-bold text-xs text-slate-300 bg-slate-900 border border-slate-800 hover:bg-slate-800 transition">
                                Mbrapsht
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                    class="w-2/3 py-3.5 rounded-2xl font-black text-xs text-black bg-[#FF9F0A] hover:bg-amber-400 shadow-lg shadow-[#FF9F0A]/20 transition transform active:scale-95 flex items-center justify-center gap-2">
                                <span wire:loading.remove>Krijo Sallonin Tim 🚀</span>
                                <span wire:loading>Duke krijuar...</span>
                            </button>
                        </div>
                    </form>
                </div>

            @elseif($step === 3)
                <!-- Step 3 Success -->
                <div class="text-center py-4">
                    <div class="w-16 h-16 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center justify-center mx-auto mb-4 text-2xl font-black">
                        ✓
                    </div>
                    <h2 class="text-xl font-black text-white mb-2">Salloni juaj u krijua me sukses! 🎉</h2>
                    <p class="text-xs text-slate-400 leading-relaxed mb-6">
                        Urimë! Faqja juaj e re publike është aktive te adresa:<br>
                        <strong class="text-[#FF9F0A]">app.e4protech.com/s/{{ $createdShop?->slug }}</strong>
                    </p>

                    <a href="{{ route('dashboard') }}"
                       class="w-full py-4 rounded-2xl font-black text-sm text-black bg-[#FF9F0A] hover:bg-amber-400 shadow-lg shadow-[#FF9F0A]/20 transition block text-center">
                        Hyr në Panelin Admin 🚀
                    </a>
                </div>
            @endif

        </div>
    </div>

</body>
</html>
