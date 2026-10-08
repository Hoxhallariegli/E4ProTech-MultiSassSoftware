<!DOCTYPE html>
<html lang="sq" class="h-full bg-[#FAF8F2] text-[#1A1D20]">
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
<body class="min-h-screen bg-[#FAF8F2] flex items-center justify-center p-4 antialiased selection:bg-[#7C5CFC] selection:text-white">

    <div class="w-full max-w-md my-8">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center justify-center w-16 h-16 rounded-3xl bg-[#0A4D44] shadow-xl shadow-[#0A4D44]/20 mb-4 font-black text-2xl text-white">
                E4
            </a>
            <h1 class="text-2xl font-black text-[#1A1D20] tracking-tight mb-2">Salloni juaj, gati në 5 minuta.</h1>
            <p class="text-xs text-slate-500 font-medium max-w-xs mx-auto">
                Krijojeni dhe provojeni falas. Paguani vetëm kur bëhet publik — nuk kërkohet kartë krediti.
            </p>
        </div>

        <!-- Card Container -->
        <div class="p-8 rounded-[2.5rem] bg-white border border-stone-200/80 shadow-2xl relative overflow-hidden">

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

                    <form wire:submit.prevent="nextStep" class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Emri i sallonit *</label>
                            <input type="text" wire:model.live="salonName" placeholder="p.sh: Elegance Beauty Salon"
                                   class="w-full px-4 py-3.5 rounded-2xl bg-[#FAF8F2] border border-slate-200 text-[#1A1D20] text-sm font-bold focus:border-[#7C5CFC] focus:outline-none transition">
                            @error('salonName') <p class="text-xs text-rose-500 mt-1.5 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Adresa e faqeve *</label>
                            <div class="flex items-center rounded-2xl bg-[#FAF8F2] border border-slate-200 overflow-hidden px-4 py-3.5 focus-within:border-[#7C5CFC] transition">
                                <span class="text-xs text-slate-400 font-bold mr-1">app.e4protech.com/s/</span>
                                <input type="text" wire:model="salonSlug" placeholder="elegance-beauty"
                                       class="w-full bg-transparent text-[#1A1D20] text-sm font-bold focus:outline-none">
                            </div>
                            @error('salonSlug') <p class="text-xs text-rose-500 mt-1.5 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Lloji i biznesit</label>
                            <select wire:model="businessType"
                                    class="w-full px-4 py-3.5 rounded-2xl bg-[#FAF8F2] border border-slate-200 text-[#1A1D20] text-sm font-bold focus:border-[#7C5CFC] focus:outline-none">
                                <option value="barber">💈 Barber Shop / Sallon Qethje</option>
                                <option value="beauty">💇‍♀️ Beauty Salon / Parukeri</option>
                                <option value="nails">💅 Nail Studio / Qendër Thonjsh</option>
                                <option value="spa">🌸 Spa & Massage</option>
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
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Emri & Mbiemri Juaj *</label>
                            <input type="text" wire:model="ownerName" placeholder="p.sh: Albano Hoxha"
                                   class="w-full px-4 py-3 rounded-2xl bg-[#FAF8F2] border border-slate-200 text-[#1A1D20] text-sm font-bold focus:border-[#7C5CFC] focus:outline-none transition">
                            @error('ownerName') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Numri i Telefonit *</label>
                            <input type="text" wire:model="ownerPhone" placeholder="p.sh: +355691234567"
                                   class="w-full px-4 py-3 rounded-2xl bg-[#FAF8F2] border border-slate-200 text-[#1A1D20] text-sm font-bold focus:border-[#7C5CFC] focus:outline-none transition">
                            @error('ownerPhone') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Adresa e E-mailit *</label>
                            <input type="email" wire:model="email" placeholder="p.sh: pronari@salloni.al"
                                   class="w-full px-4 py-3 rounded-2xl bg-[#FAF8F2] border border-slate-200 text-[#1A1D20] text-sm font-bold focus:border-[#7C5CFC] focus:outline-none transition">
                            @error('email') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Fjalëkalimi *</label>
                            <input type="password" wire:model="password" placeholder="••••••••"
                                   class="w-full px-4 py-3 rounded-2xl bg-[#FAF8F2] border border-slate-200 text-[#1A1D20] text-sm font-bold focus:border-[#7C5CFC] focus:outline-none transition">
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
                    <h2 class="text-xl font-black text-[#1A1D20] mb-2">Salloni juaj u krijua me sukses! 🎉</h2>
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
    </div>

</body>
</html>
