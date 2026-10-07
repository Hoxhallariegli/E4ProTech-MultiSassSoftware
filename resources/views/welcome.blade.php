<!DOCTYPE html>
<html lang="sq" class="h-full bg-[#0D0E12] text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E4ProTech Engine | Faqja e Rezervimeve për Sallonin Tuaj</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full flex flex-col justify-between antialiased bg-[#0D0E12] selection:bg-[#FF9F0A] selection:text-black">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-50 backdrop-blur-md bg-[#0D0E12]/80 border-b border-slate-800/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-[#FF9F0A] to-amber-400 flex items-center justify-center font-black text-xl text-black shadow-lg shadow-[#FF9F0A]/20 transition transform group-hover:scale-105">
                    S
                </div>
                <div>
                    <h1 class="text-xl font-black tracking-tight text-white leading-none">
                        E4ProTech
                    </h1>
                    <p class="text-[10px] text-[#FF9F0A] font-extrabold tracking-wider uppercase">Salloni Juaj Online</p>
                </div>
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.settings') }}" class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-extrabold bg-[#16181E] border border-slate-800 text-slate-200 hover:bg-slate-800 transition">
                    Hyr te Paneli
                </a>
                <a href="{{ route('krijo-sallonin') }}" class="px-5 py-2.5 rounded-xl text-xs font-black text-black bg-[#FF9F0A] hover:bg-amber-400 shadow-lg shadow-[#FF9F0A]/20 transition transform active:scale-95 flex items-center gap-2">
                    Krijo salonin tënd
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative overflow-hidden pt-20 pb-16 lg:pt-28 lg:pb-20">
        <div class="absolute inset-0 bg-gradient-to-b from-[#FF9F0A]/10 via-transparent to-transparent opacity-60"></div>
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">

            <!-- Hero Logo -->
            <div class="w-20 h-20 rounded-3xl bg-gradient-to-tr from-[#FF9F0A] to-amber-400 flex items-center justify-center font-black text-4xl text-black shadow-2xl shadow-[#FF9F0A]/30 mx-auto mb-8">
                S
            </div>

            <!-- Hero Main Title -->
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight leading-[1.1] mb-4">
                Faqja e rezervimeve për<br>sallonin tuaj.
            </h1>

            <!-- Subtitle -->
            <h2 class="text-3xl sm:text-5xl lg:text-6xl font-black text-[#FF9F0A] tracking-tight mb-8">
                Falas derisa t'ju sjellë klientë.
            </h2>

            <!-- Paragraph -->
            <p class="text-base sm:text-lg text-slate-400 max-w-2xl mx-auto mb-10 font-medium leading-relaxed">
                Faqja juaj online për 5 minuta. Klientët rezervojnë vetë, edhe ndërsa ju punoni.
            </p>

            <!-- Hero CTAs -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-6">
                <a href="{{ route('krijo-sallonin') }}"
                   class="w-full sm:w-auto px-8 py-4 rounded-2xl font-black text-sm text-black bg-[#FF9F0A] hover:bg-amber-400 shadow-xl shadow-[#FF9F0A]/25 transition transform active:scale-95 flex items-center justify-center gap-2">
                    <span>Krijo salonin tënd</span>
                    <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>

                <a href="#sallonet"
                   class="w-full sm:w-auto px-8 py-4 rounded-2xl font-extrabold text-sm text-slate-200 bg-[#16181E] border border-slate-800 hover:bg-slate-800 transition flex items-center justify-center">
                    Shihni një sallon të vërtetë
                </a>
            </div>

            <!-- Free badge -->
            <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full text-xs font-extrabold bg-[#16181E] border border-amber-500/30 text-amber-400 shadow-lg">
                1 muaj falas, pa kartë krediti
            </div>
        </div>
    </section>

    <!-- Feature Cards Section -->
    <section class="py-16 bg-[#12141A]/80 border-y border-slate-800/60">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                <!-- Feature 1 -->
                <div class="p-8 rounded-[2rem] bg-[#16181E] border border-slate-800/80 shadow-xl">
                    <div class="w-12 h-12 rounded-2xl bg-[#FF9F0A]/10 border border-[#FF9F0A]/30 text-[#FF9F0A] flex items-center justify-center mb-6 text-2xl font-black">
                        📅
                    </div>
                    <p class="text-xs text-slate-400 font-semibold leading-relaxed mb-2">
                        Takime të shkruara me dorë, thirrje të humbura, klientë që harrojnë?
                    </p>
                    <h3 class="text-base font-black text-white leading-snug">
                        Klientët tuaj rezervojnë vetë, online, 24 orë në ditë.
                    </h3>
                </div>

                <!-- Feature 2 -->
                <div class="p-8 rounded-[2rem] bg-[#16181E] border border-slate-800/80 shadow-xl">
                    <div class="w-12 h-12 rounded-2xl bg-[#FF9F0A]/10 border border-[#FF9F0A]/30 text-[#FF9F0A] flex items-center justify-center mb-6 text-2xl font-black">
                        🖼️
                    </div>
                    <p class="text-xs text-slate-400 font-semibold leading-relaxed mb-2">
                        Prerjet tuaja më të bukura mbeten të padukshme në rrjete sociale?
                    </p>
                    <h3 class="text-base font-black text-white leading-snug">
                        Një galeri që bind, e përditësuar me 2 klikime.
                    </h3>
                </div>

                <!-- Feature 3 -->
                <div class="p-8 rounded-[2rem] bg-[#16181E] border border-slate-800/80 shadow-xl">
                    <div class="w-12 h-12 rounded-2xl bg-[#FF9F0A]/10 border border-[#FF9F0A]/30 text-[#FF9F0A] flex items-center justify-center mb-6 text-2xl font-black">
                        ⭐
                    </div>
                    <p class="text-xs text-slate-400 font-semibold leading-relaxed mb-2">
                        Nuk e dini çfarë mendojnë njerëzit për ju online?
                    </p>
                    <h3 class="text-base font-black text-white leading-snug">
                        Vlerësimet tuaja shfaqen automatikisht në faqen tuaj.
                    </h3>
                </div>

            </div>
        </div>
    </section>

    <!-- Active Salons Showcase Section -->
    <section id="sallonet" class="py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-12">
            <div>
                <h3 class="text-3xl font-black text-white tracking-tight mb-2">Sallonet Partnere</h3>
                <p class="text-xs text-slate-400 font-medium">Kliko mbi cilindo sallon për të parë faqen e tij të re publike të rezervimeve</p>
            </div>
            <a href="{{ route('krijo-sallonin') }}" class="px-5 py-2.5 rounded-xl text-xs font-black text-black bg-[#FF9F0A] hover:bg-amber-400 transition shadow-lg">
                + Shto Sallonin Tënd
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($shops as $shop)
                <div class="p-8 rounded-[2.5rem] bg-[#16181E] border border-slate-800 hover:border-[#FF9F0A]/50 transition duration-300 flex flex-col justify-between group shadow-xl hover:shadow-2xl hover:-translate-y-1 transform">
                    <div>
                        <!-- Header & Badge -->
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-black text-2xl text-white shadow-lg"
                                 style="background: linear-gradient(135deg, {{ $shop->primary_color }}, {{ $shop->secondary_color }});">
                                {{ mb_substr($shop->name, 0, 1) }}
                            </div>
                            <span class="text-[11px] px-3.5 py-1 rounded-full font-extrabold uppercase tracking-wider text-black bg-[#FF9F0A] shadow-sm">
                                {{ $shop->resolved_shop_label }}
                            </span>
                        </div>

                        <!-- Shop Info -->
                        <h4 class="text-2xl font-black text-white group-hover:text-[#FF9F0A] transition mb-2">
                            {{ $shop->name }}
                        </h4>
                        <p class="text-xs text-slate-400 font-medium leading-relaxed mb-6">
                            Lloji: <strong class="text-slate-200">{{ mb_strtoupper($shop->business_type ?? 'general') }}</strong>. Stafi: <strong class="text-slate-200">{{ $shop->resolved_staff_label_plural }}</strong>.
                        </p>
                    </div>

                    <!-- CTA Link -->
                    <div class="pt-6 border-t border-slate-800/80">
                        <a href="{{ route('shop.landing', $shop->slug) }}"
                           class="w-full py-3.5 rounded-2xl font-black text-xs text-black bg-[#FF9F0A] hover:bg-amber-400 flex items-center justify-center gap-2 shadow-lg transition transform active:scale-95">
                            <span>Visito Faqen & Rezervo Online</span>
                            <svg class="w-4 h-4 text-black group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-10 border-t border-slate-800/60 bg-[#0D0E12] mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-slate-500 font-medium">
                © {{ date('Y') }} E4ProTech Engine — Faqja e Rezervimeve për Sallonin Tuaj
            </p>
            <div class="flex gap-6 text-xs text-slate-400 font-bold">
                <a href="{{ route('krijo-sallonin') }}" class="hover:text-[#FF9F0A] transition">Krijo Salonin Tënd</a>
                <a href="{{ route('admin.settings') }}" class="hover:text-[#FF9F0A] transition">Paneli Admin</a>
                <a href="{{ route('app.download.apk') }}" class="hover:text-[#FF9F0A] transition">Shkarko APK-në</a>
            </div>
        </div>
    </footer>

</body>
</html>
