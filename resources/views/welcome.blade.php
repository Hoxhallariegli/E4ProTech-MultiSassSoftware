<!DOCTYPE html>
<html lang="sq" class="h-full bg-[#FAF8F2] text-[#1A1D20]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E4ProTech Engine | Platforma Më e Sigurt për Sallonin Tuaj</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full flex flex-col justify-between antialiased bg-[#FAF8F2] text-[#1A1D20] selection:bg-[#7C5CFC] selection:text-white">

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
                <a href="#welcome" class="hover:text-[#7C5CFC] transition">Kreu</a>
                <a href="#how-it-works" class="hover:text-[#7C5CFC] transition">Si Funksionon</a>
                <a href="#services" class="hover:text-[#7C5CFC] transition">Shërbimet</a>
                <a href="#sallonet" class="hover:text-[#7C5CFC] transition">Sallonet</a>
                <a href="#faq" class="hover:text-[#7C5CFC] transition">Pyetjet</a>
            </nav>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.settings') }}" class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-extrabold bg-[#1A1D20] text-white hover:bg-black transition shadow-sm">
                    Paneli Admin ↗
                </a>
                <a href="{{ route('krijo-sallonin') }}" class="px-6 py-2.5 rounded-full text-xs font-black text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-lg shadow-[#7C5CFC]/25 transition transform active:scale-95 flex items-center gap-2">
                    Krijo Salonin ↗
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section (E4ProTech Style) -->
    <section id="welcome" class="relative overflow-hidden pt-16 pb-20 lg:pt-24 lg:pb-28 bg-[#FAF8F2]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

                <!-- Left Text Column -->
                <div class="lg:col-span-7 text-left">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-black bg-[#0A4D44]/10 text-[#0A4D44] mb-6">
                        <span class="w-2 h-2 rounded-full bg-[#7C5CFC] animate-pulse"></span>
                        E4ProTech Engine • Managed IT Solutions
                    </div>

                    <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-[#1A1D20] tracking-tight leading-[1.08] mb-6">
                        Platforma Më e Sigurt & e Shpejtë për <span class="underline decoration-[#7C5CFC] decoration-4 underline-offset-4">Sallonin Tuaj.</span>
                    </h1>

                    <p class="text-base sm:text-lg text-slate-600 max-w-xl mb-10 font-medium leading-relaxed">
                        Faqe publike e personalizuar për 5 minuta, rezervime online 24/7, konfirmime &amp; rikujtesa me SMS automatike dhe aplikacion celular.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center gap-4 mb-8">
                        <a href="{{ route('krijo-sallonin') }}"
                           class="w-full sm:w-auto px-8 py-4 rounded-full font-black text-sm text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-xl shadow-[#7C5CFC]/30 transition transform active:scale-95 flex items-center justify-center gap-2">
                            <span>Krijo Salonin Tënd</span>
                            <span class="text-lg">↗</span>
                        </a>

                        <a href="#sallonet"
                           class="w-full sm:w-auto px-8 py-4 rounded-full font-extrabold text-sm text-[#1A1D20] bg-white border border-slate-300 hover:bg-stone-50 transition flex items-center justify-center gap-2 shadow-sm">
                            <span>Shih Sallonet Partnere</span>
                            <span class="text-lg">✉</span>
                        </a>
                    </div>

                    <div class="flex items-center gap-6 text-xs font-extrabold text-slate-500">
                        <span class="flex items-center gap-1.5"><strong class="text-[#0A4D44]">✓</strong> 1 Muaj Falas</span>
                        <span class="flex items-center gap-1.5"><strong class="text-[#0A4D44]">✓</strong> Pa Kartë Krediti</span>
                        <span class="flex items-center gap-1.5"><strong class="text-[#0A4D44]">✓</strong> SMS Gateway SIM</span>
                    </div>
                </div>

                <!-- Right Visual Card Column -->
                <div class="lg:col-span-5 relative">
                    <div class="p-8 rounded-[2.5rem] bg-white border border-slate-200/80 shadow-2xl relative z-10">
                        <div class="flex items-center justify-between mb-6">
                            <span class="text-xs font-black uppercase tracking-wider text-[#7C5CFC]">Statistics &amp; Growth</span>
                            <span class="text-xs px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 font-extrabold">98.8% Kënaqësi</span>
                        </div>

                        <div class="space-y-4 mb-6">
                            <div class="p-4 rounded-2xl bg-[#FAF8F2] border border-stone-200/60 flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-slate-500 font-bold">Rezervime Sot</p>
                                    <h4 class="text-xl font-black text-[#1A1D20]">24 Takime Online</h4>
                                </div>
                                <span class="w-10 h-10 rounded-xl bg-[#7C5CFC]/10 text-[#7C5CFC] font-black flex items-center justify-center text-lg">📈</span>
                            </div>

                            <div class="p-4 rounded-2xl bg-[#FAF8F2] border border-stone-200/60 flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-slate-500 font-bold">Statusi i SMS-ve</p>
                                    <h4 class="text-xl font-black text-emerald-600">100% Dërguar</h4>
                                </div>
                                <span class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 font-black flex items-center justify-center text-lg">📩</span>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-[#0A4D44] text-white text-xs font-medium leading-relaxed">
                            💡 "Klientët rezervojnë vetë 24/7. Faqja juaj punon edhe ndërsa ju jeni në punë apo pushim."
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- How We Work Section (E4ProTech Dark Teal Wave `#0A4D44`) -->
    <section id="how-it-works" class="relative bg-[#0A4D44] text-white py-20 lg:py-28">

        <!-- Jagged Top Shape -->
        <div class="absolute top-0 left-0 right-0 h-6 bg-[#FAF8F2]" style="clip-path: polygon(0 0, 25% 100%, 50% 0, 75% 100%, 100% 0, 100% 0, 0 0);"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pt-6">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-extrabold uppercase tracking-widest text-amber-300">How We Work</span>
                <h2 class="text-3xl sm:text-5xl font-black tracking-tight text-white mt-2 mb-4">
                    Transformimi i Sallonit Tuaj, Hap Pas Hapi
                </h2>
                <p class="text-sm text-teal-100 font-medium">
                    Çdo biznes është unik. Ja si e përshtatim platformën E4ProTech për nevojat tuaja:
                </p>
            </div>

            <!-- Steps Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">

                <!-- Step 1 -->
                <div class="p-8 rounded-[2rem] bg-white/10 backdrop-blur-md border border-white/15">
                    <span class="text-xs font-black uppercase text-amber-300">Hapi 1</span>
                    <h3 class="text-xl font-black text-white mt-2 mb-3">Discovery &amp; Faqja Online</h3>
                    <p class="text-xs text-teal-100 leading-relaxed font-medium">
                        Krijoni profilin e sallonit, përzgjidhni stafin, shërbimet dhe çmimet. Faqja juaj e re publike është gatshme në 5 minuta.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="p-8 rounded-[2rem] bg-white/10 backdrop-blur-md border border-white/15">
                    <span class="text-xs font-black uppercase text-amber-300">Hapi 2</span>
                    <h3 class="text-xl font-black text-white mt-2 mb-3">Rezervime 24/7</h3>
                    <p class="text-xs text-teal-100 leading-relaxed font-medium">
                        Klientët zgjedhin berberin/parukierin, shërbimin dhe orarin e lirë në mënyrë interaktive pa u mbivendosur asnjë takimi.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="p-8 rounded-[2rem] bg-white/10 backdrop-blur-md border border-white/15">
                    <span class="text-xs font-black uppercase text-amber-300">Hapi 3</span>
                    <h3 class="text-xl font-black text-white mt-2 mb-3">SMS &amp; Rikujtesa</h3>
                    <p class="text-xs text-teal-100 leading-relaxed font-medium">
                        Sistemi llogarit dhe dërgon automatikisht konfirmimet dhe rikujtesat paraprake te telefoni i klientit nëpërmjet SIM Gateway.
                    </p>
                </div>

                <!-- Step 4 -->
                <div class="p-8 rounded-[2rem] bg-white/10 backdrop-blur-md border border-white/15">
                    <span class="text-xs font-black uppercase text-amber-300">Hapi 4</span>
                    <h3 class="text-xl font-black text-white mt-2 mb-3">Aplikacion &amp; Live Sync</h3>
                    <p class="text-xs text-teal-100 leading-relaxed font-medium">
                        Axhenda përditësohet në kohë reale te paneli admin dhe te Aplikacioni Celular (APK) me njoftime Push.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- Core Services & Expertise Section (Cream `#FAF8F2`) -->
    <section id="services" class="py-20 lg:py-28 bg-[#FAF8F2]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-extrabold uppercase tracking-widest text-[#7C5CFC]">Core Expertise</span>
                <h2 class="text-3xl sm:text-5xl font-black tracking-tight text-[#1A1D20] mt-2 mb-4">
                    Shërbimet Më të Fuqishme për Sallonet
                </h2>
                <p class="text-sm text-slate-600 font-medium">
                    Mjete moderne të integruara për të rritur klientelën dhe automatizuar menaxhimin:
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                <!-- Card 1 -->
                <div class="p-8 rounded-[2.5rem] bg-white border border-stone-200/80 shadow-xl hover:shadow-2xl transition duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-[#7C5CFC]/10 text-[#7C5CFC] font-black flex items-center justify-center mb-6 text-2xl">
                        🌐
                    </div>
                    <h3 class="text-xl font-black text-[#1A1D20] mb-3">Faqe Publike e Personalizuar</h3>
                    <p class="text-xs text-slate-600 leading-relaxed font-medium mb-6">
                        Shfaqni shërbimet, galeritë e prerjeve, stafet dhe lejojini klientët të rezervojnë direkt nga telefoni apo kompjuteri.
                    </p>
                    <span class="inline-block px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-[11px] font-extrabold">Web / Mobile Responsive</span>
                </div>

                <!-- Card 2 -->
                <div class="p-8 rounded-[2.5rem] bg-white border border-stone-200/80 shadow-xl hover:shadow-2xl transition duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-[#0A4D44]/10 text-[#0A4D44] font-black flex items-center justify-center mb-6 text-2xl">
                        📱
                    </div>
                    <h3 class="text-xl font-black text-[#1A1D20] mb-3">SIM SMS Gateway &amp; Push</h3>
                    <p class="text-xs text-slate-600 leading-relaxed font-medium mb-6">
                        Celulari juaj dërgon automatikisht SMS konfirmimi e rikujtese pa kosto të larta serveri, me sinkronizim Push në kohë reale.
                    </p>
                    <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-extrabold">SMS SIM Direct Sending</span>
                </div>

                <!-- Card 3 -->
                <div class="p-8 rounded-[2.5rem] bg-white border border-stone-200/80 shadow-xl hover:shadow-2xl transition duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-amber-500/10 text-amber-600 font-black flex items-center justify-center mb-6 text-2xl">
                        📊
                    </div>
                    <h3 class="text-xl font-black text-[#1A1D20] mb-3">Aplikacion Celular APK &amp; Analizë</h3>
                    <p class="text-xs text-slate-600 leading-relaxed font-medium mb-6">
                        Menaxhoni rezervimet, pagesat, historikun e klientëve dhe ardhjet me Aplikacionin Celular (APK) të dedikuar.
                    </p>
                    <span class="inline-block px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-[11px] font-extrabold">Android &amp; Web Engine</span>
                </div>

            </div>
        </div>
    </section>

    <!-- Active Salons Showcase Section -->
    <section id="sallonet" class="py-20 lg:py-28 bg-white border-t border-stone-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-12">
                <div>
                    <span class="text-xs font-extrabold uppercase tracking-widest text-[#7C5CFC]">Active Network</span>
                    <h2 class="text-3xl sm:text-4xl font-black text-[#1A1D20] tracking-tight mt-1">Sallonet e Regjistruara</h2>
                </div>
                <a href="{{ route('krijo-sallonin') }}" class="px-6 py-3 rounded-full text-xs font-black text-white bg-[#7C5CFC] hover:bg-[#6366F1] transition shadow-lg shadow-[#7C5CFC]/20">
                    + Shto Sallonin Tënd ↗
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($shops as $shop)
                    <div class="p-8 rounded-[2.5rem] bg-[#FAF8F2] border border-stone-200/80 hover:border-[#7C5CFC] transition duration-300 flex flex-col justify-between group shadow-md hover:shadow-xl hover:-translate-y-1 transform">
                        <div>
                            <div class="flex items-center justify-between mb-6">
                                <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-black text-2xl text-white shadow-md"
                                     style="background: linear-gradient(135deg, {{ $shop->primary_color }}, {{ $shop->secondary_color }});">
                                    {{ mb_substr($shop->name, 0, 1) }}
                                </div>
                                <span class="text-[11px] px-3.5 py-1 rounded-full font-extrabold uppercase tracking-wider text-white bg-[#0A4D44]">
                                    {{ $shop->resolved_shop_label }}
                                </span>
                            </div>

                            <h4 class="text-2xl font-black text-[#1A1D20] group-hover:text-[#7C5CFC] transition mb-2">
                                {{ $shop->name }}
                            </h4>
                            <p class="text-xs text-slate-500 font-medium leading-relaxed mb-6">
                                Lloji: <strong class="text-slate-800">{{ mb_strtoupper($shop->business_type ?? 'general') }}</strong>. Stafi: <strong class="text-slate-800">{{ $shop->resolved_staff_label_plural }}</strong>.
                            </p>
                        </div>

                        <div class="pt-6 border-t border-stone-200/80">
                            <a href="{{ route('shop.landing', $shop->slug) }}"
                               class="w-full py-3.5 rounded-full font-black text-xs text-white bg-[#7C5CFC] hover:bg-[#6366F1] flex items-center justify-center gap-2 shadow-md transition transform active:scale-95">
                                <span>Visito Faqen &amp; Rezervo ↗</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- FAQ Section (E4ProTech Style) -->
    <section id="faq" class="py-20 lg:py-28 bg-[#FAF8F2] border-t border-stone-200/80">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <span class="text-xs font-extrabold uppercase tracking-widest text-[#7C5CFC]">FAQ</span>
                <h2 class="text-3xl sm:text-4xl font-black text-[#1A1D20] tracking-tight mt-1">Pyetjet Më të Shpeshta</h2>
            </div>

            <div class="space-y-4">
                <div class="p-6 rounded-2xl bg-white border border-stone-200/80 shadow-sm">
                    <h4 class="font-black text-sm text-[#1A1D20] mb-2">Si dërgohen SMS-të e konfirmimit dhe rikujtesës?</h4>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">
                        Sistemi lidhet me celularin tuaj Android ku është instaluar Aplikacioni E4ProTech Engine. SMS-të dërgohen automatikisht nga karta juaj SIM pa pasur nevojë për kosto shtesë.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-white border border-stone-200/80 shadow-sm">
                    <h4 class="font-black text-sm text-[#1A1D20] mb-2">A është e mundur të kemi faqe publike me adresën tonë?</h4>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">
                        Po! Çdo sallon merr adresën e tij unike (p.sh: <code class="text-[#7C5CFC]">app.e4protech.com/s/salloni-juaj</code>) ku klientët kryejnë rezervime direkte.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-white border border-stone-200/80 shadow-sm">
                    <h4 class="font-black text-sm text-[#1A1D20] mb-2">Sa kohë duhet për të aktivizuar llogarinë time?</h4>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">
                        Vetëm 5 minuta! Përdorni butonin "Krijo Salonin Tënd", plotësoni të dhënat dhe paneli juaj është menjëherë gati për punë.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer (E4ProTech Dark Teal `#0A4D44`) -->
    <footer class="py-12 bg-[#0A4D44] text-white mt-auto">
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
                <a href="{{ route('krijo-sallonin') }}" class="hover:text-amber-300 transition">Krijo Salonin Tënd</a>
                <a href="{{ route('admin.settings') }}" class="hover:text-amber-300 transition">Paneli Admin</a>
                <a href="{{ route('app.download.apk') }}" class="hover:text-amber-300 transition">Shkarko APK-në</a>
            </div>
        </div>
    </footer>

</body>
</html>
