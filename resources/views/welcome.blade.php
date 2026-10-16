<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full bg-[#FAF8F2] text-[#1A1D20]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E4ProTech Engine | {{ app()->getLocale() === 'en' ? 'Most Secure & Fast Platform for Your Salon' : 'Platforma Më e Sigurt për Sallonin Tuaj' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full flex flex-col justify-between antialiased bg-[#FAF8F2] text-[#1A1D20] selection:bg-[#7C5CFC] selection:text-white">

    @php $isEn = app()->getLocale() === 'en'; @endphp

    <!-- Top Navigation Bar (E4ProTech Style & Clean Language Switcher) -->
    <header class="sticky top-0 z-50 backdrop-blur-md bg-[#FAF8F2]/90 border-b border-stone-200/60">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-2 sm:gap-4">
            <a href="/" class="flex items-center gap-2 group shrink-0">
                <img src="{{ asset('E4ProTech.webp') }}" alt="E4ProTech Engine" class="h-16 sm:h-20 w-auto max-w-[200px] sm:max-w-[280px] -my-3 sm:-my-4 object-contain transition transform group-hover:scale-105" onerror="this.onerror=null; this.src='{{ asset('E4ProTech-Engine.png') }}';">
            </a>

            <!-- Nav Links -->
            <nav class="hidden lg:flex items-center gap-8 text-xs font-bold text-slate-700">
                <a href="#welcome" class="hover:text-[#7C5CFC] transition">{{ $isEn ? 'Home' : 'Kreu' }}</a>
                <a href="#how-it-works" class="hover:text-[#7C5CFC] transition">{{ $isEn ? 'How It Works' : 'Si Funksionon' }}</a>
                <a href="#services" class="hover:text-[#7C5CFC] transition">{{ $isEn ? 'Services' : 'Shërbimet' }}</a>
                <a href="#sallonet" class="hover:text-[#7C5CFC] transition">{{ $isEn ? 'Salons' : 'Sallonet' }}</a>
                <a href="#faq" class="hover:text-[#7C5CFC] transition">{{ $isEn ? 'FAQ' : 'Pyetjet' }}</a>
            </nav>

            <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                <!-- Language Switcher Pill -->
                <div class="flex items-center gap-0.5 p-0.5 sm:p-1 rounded-full bg-white border border-slate-300 shadow-sm text-[11px] sm:text-xs font-black">
                    <a href="{{ route('language.switch', 'sq') }}"
                       class="px-2 sm:px-3 py-1 sm:py-1.5 rounded-full transition {{ !$isEn ? 'bg-[#7C5CFC] text-white shadow-sm' : 'text-slate-600 hover:text-black' }}">
                        <span class="hidden sm:inline">🇦🇱 </span>SQ
                    </a>
                    <a href="{{ route('language.switch', 'en') }}"
                       class="px-2 sm:px-3 py-1 sm:py-1.5 rounded-full transition {{ $isEn ? 'bg-[#7C5CFC] text-white shadow-sm' : 'text-slate-600 hover:text-black' }}">
                        <span class="hidden sm:inline">🇬🇧 </span>EN
                    </a>
                </div>

                <a href="{{ route('login') }}" class="inline-flex items-center gap-1 sm:gap-2 px-2.5 sm:px-5 py-1.5 sm:py-2.5 rounded-full text-[10px] sm:text-xs font-extrabold bg-[#1A1D20] text-white hover:bg-black transition shadow-sm whitespace-nowrap">
                    <span>{{ $isEn ? 'Login' : 'Kyçja' }}</span>
                    <span class="hidden sm:inline">{{ $isEn ? 'Panel' : 'Admin' }}</span>
                    <span>↗</span>
                </a>
                <a href="{{ route('krijo-sallonin') }}" class="px-2.5 sm:px-6 py-1.5 sm:py-2.5 rounded-full text-[10px] sm:text-xs font-black text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-lg shadow-[#7C5CFC]/25 transition transform active:scale-95 flex items-center gap-1 whitespace-nowrap">
                    <span>{{ $isEn ? 'Create' : 'Krijo' }}</span>
                    <span class="hidden sm:inline">{{ $isEn ? 'Salon' : 'Salonin' }}</span>
                    <span>↗</span>
                </a>
            </div>
        </div>
    </header>
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
                        E4ProTech Engine • {{ __('Managed IT Solutions') }}
                    </div>

                    <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-[#1A1D20] tracking-tight leading-[1.08] mb-6">
                        {!! $isEn
                            ? 'The Most Secure &amp; Fast Platform for <span class="underline decoration-[#7C5CFC] decoration-4 underline-offset-4">Your Salon.</span>'
                            : 'Platforma Më e Sigurt &amp; e Shpejtë për <span class="underline decoration-[#7C5CFC] decoration-4 underline-offset-4">Sallonin Tuaj.</span>' !!}
                    </h1>

                    <p class="text-base sm:text-lg text-slate-600 max-w-xl mb-10 font-medium leading-relaxed">
                        {{ $isEn
                            ? 'Customized public page in 5 minutes, 24/7 online bookings, automated SMS confirmations & reminders, and mobile application.'
                            : 'Faqe publike e personalizuar për 5 minuta, rezervime online 24/7, konfirmime & rikujtesa me SMS automatike dhe aplikacion celular.' }}
                    </p>

                    <div class="flex flex-col sm:flex-row items-center gap-4 mb-8">
                        <a href="{{ route('krijo-sallonin') }}"
                           class="w-full sm:w-auto px-8 py-4 rounded-full font-black text-sm text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-xl shadow-[#7C5CFC]/30 transition transform active:scale-95 flex items-center justify-center gap-2">
                            <span>{{ $isEn ? 'Create Your Salon' : 'Krijo Salonin Tënd' }}</span>
                            <span class="text-lg">↗</span>
                        </a>

                        <a href="#sallonet"
                           class="w-full sm:w-auto px-8 py-4 rounded-full font-extrabold text-sm text-[#1A1D20] bg-white border border-slate-300 hover:bg-stone-50 transition flex items-center justify-center gap-2 shadow-sm">
                            <span>{{ $isEn ? 'View Partner Salons' : 'Shih Sallonet Partnere' }}</span>
                            <span class="text-lg">✉</span>
                        </a>
                    </div>

                    <div class="flex items-center gap-6 text-xs font-extrabold text-slate-500">
                        <span class="flex items-center gap-1.5"><strong class="text-[#0A4D44]">✓</strong> {{ $isEn ? '1 Month Free' : '1 Muaj Falas' }}</span>
                        <span class="flex items-center gap-1.5"><strong class="text-[#0A4D44]">✓</strong> {{ $isEn ? 'No Credit Card' : 'Pa Kartë Krediti' }}</span>
                        <span class="flex items-center gap-1.5"><strong class="text-[#0A4D44]">✓</strong> {{ $isEn ? 'SIM Gateway SMS' : 'SMS Gateway SIM' }}</span>
                    </div>
                </div>

                <!-- Right Visual Card Column -->
                <div class="lg:col-span-5 relative">
                    <div class="p-8 rounded-[2.5rem] bg-white border border-slate-200/80 shadow-2xl relative z-10">
                        <div class="flex items-center justify-between mb-6">
                            <span class="text-xs font-black uppercase tracking-wider text-[#7C5CFC]">STATISTICS &amp; GROWTH</span>
                            <span class="text-xs px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 font-extrabold">
                                {{ $isEn ? '98.8% Satisfaction' : '98.8% Kënaqësi' }}
                            </span>
                        </div>

                        <div class="space-y-4 mb-6">
                            <div class="p-4 rounded-2xl bg-[#FAF8F2] border border-stone-200/60 flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-slate-500 font-bold">{{ $isEn ? 'Today\'s Bookings' : 'Rezervime Sot' }}</p>
                                    <h4 class="text-xl font-black text-[#1A1D20]">{{ $isEn ? '24 Online Appointments' : '24 Takime Online' }}</h4>
                                </div>
                                <span class="w-10 h-10 rounded-xl bg-[#7C5CFC]/10 text-[#7C5CFC] font-black flex items-center justify-center text-lg">📈</span>
                            </div>

                            <div class="p-4 rounded-2xl bg-[#FAF8F2] border border-stone-200/60 flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-slate-500 font-bold">{{ $isEn ? 'SMS Status' : 'Statusi i SMS-ve' }}</p>
                                    <h4 class="text-xl font-black text-emerald-600">{{ $isEn ? '100% Sent' : '100% Dërguar' }}</h4>
                                </div>
                                <span class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 font-black flex items-center justify-center text-lg">📩</span>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-[#0A4D44] text-white text-xs font-medium leading-relaxed">
                            {{ $isEn
                                ? '💡 "Clients book 24/7 on their own. Your page works even while you are working or resting."'
                                : '💡 "Klientët rezervojnë vetë 24/7. Faqja juaj punon edhe ndërsa ju jeni në punë apo pushim."' }}
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
                <span class="text-xs font-extrabold uppercase tracking-widest text-amber-300">HOW WE WORK</span>
                <h2 class="text-3xl sm:text-5xl font-black tracking-tight text-white mt-2 mb-4">
                    {{ $isEn ? 'Transforming Your Salon, Step By Step' : 'Transformimi i Sallonit Tuaj, Hap Pas Hapi' }}
                </h2>
                <p class="text-sm text-teal-100 font-medium">
                    {{ $isEn
                        ? 'Every business is unique. Here is how we adapt the E4ProTech platform for your needs:'
                        : 'Çdo biznes është unik. Ja si e përshtatim platformën E4ProTech për nevojat tuaja:' }}
                </p>
            </div>

            <!-- Steps Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">

                <!-- Step 1 -->
                <div class="p-8 rounded-[2rem] bg-white/10 backdrop-blur-md border border-white/15">
                    <span class="text-xs font-black uppercase text-amber-300">{{ $isEn ? 'STEP 1' : 'HAPI 1' }}</span>
                    <h3 class="text-xl font-black text-white mt-2 mb-3">{{ $isEn ? 'Discovery & Online Page' : 'Discovery & Faqja Online' }}</h3>
                    <p class="text-xs text-teal-100 leading-relaxed font-medium">
                        {{ $isEn
                            ? 'Create your salon profile, select staff, services and prices. Your new public page is ready in 5 minutes.'
                            : 'Krijoni profilin e sallonit, përzgjidhni stafin, shërbimet dhe çmimet. Faqja juaj e re publike është gatshme në 5 minuta.' }}
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="p-8 rounded-[2rem] bg-white/10 backdrop-blur-md border border-white/15">
                    <span class="text-xs font-black uppercase text-amber-300">{{ $isEn ? 'STEP 2' : 'HAPI 2' }}</span>
                    <h3 class="text-xl font-black text-white mt-2 mb-3">{{ $isEn ? '24/7 Online Bookings' : 'Rezervime 24/7' }}</h3>
                    <p class="text-xs text-teal-100 leading-relaxed font-medium">
                        {{ $isEn
                            ? 'Clients interactively pick their preferred stylist, service, and available time without double booking.'
                            : 'Klientët zgjedhin berberin/parukierin, shërbimin dhe orarin e lirë në mënyrë interaktive pa u mbivendosur asnjë takimi.' }}
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="p-8 rounded-[2rem] bg-white/10 backdrop-blur-md border border-white/15">
                    <span class="text-xs font-black uppercase text-amber-300">{{ $isEn ? 'STEP 3' : 'HAPI 3' }}</span>
                    <h3 class="text-xl font-black text-white mt-2 mb-3">{{ $isEn ? 'Automated SMS & Reminders' : 'SMS & Rikujtesa' }}</h3>
                    <p class="text-xs text-teal-100 leading-relaxed font-medium">
                        {{ $isEn
                            ? 'System automatically dispatches SMS confirmations and upcoming reminders via SIM Gateway.'
                            : 'Sistemi llogarit dhe dërgon automatikisht konfirmimet dhe rikujtesat paraprake te telefoni i klientit nëpërmjet SIM Gateway.' }}
                    </p>
                </div>

                <!-- Step 4 -->
                <div class="p-8 rounded-[2rem] bg-white/10 backdrop-blur-md border border-white/15">
                    <span class="text-xs font-black uppercase text-amber-300">{{ $isEn ? 'STEP 4' : 'HAPI 4' }}</span>
                    <h3 class="text-xl font-black text-white mt-2 mb-3">{{ $isEn ? 'Mobile App & Live Sync' : 'Aplikacion & Live Sync' }}</h3>
                    <p class="text-xs text-teal-100 leading-relaxed font-medium">
                        {{ $isEn
                            ? 'Schedule updates in real-time on your admin panel and Mobile App (APK) with Push Notifications.'
                            : 'Axhenda përditësohet në kohë reale te paneli admin dhe te Aplikacioni Celular (APK) me njoftime Push.' }}
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- Core Services & Expertise Section (Cream `#FAF8F2`) -->
    <section id="services" class="py-20 lg:py-28 bg-[#FAF8F2]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-extrabold uppercase tracking-widest text-[#7C5CFC]">CORE EXPERTISE</span>
                <h2 class="text-3xl sm:text-5xl font-black tracking-tight text-[#1A1D20] mt-2 mb-4">
                    {{ $isEn ? 'Powerful Services for Salons' : 'Shërbimet Më të Fuqishme për Sallonet' }}
                </h2>
                <p class="text-sm text-slate-600 font-medium">
                    {{ $isEn
                        ? 'Integrated modern tools to grow your clients and automate management:'
                        : 'Mjete moderne të integruara për të rritur klientelën dhe automatizuar menaxhimin:' }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                <!-- Card 1 -->
                <div class="p-8 rounded-[2.5rem] bg-white border border-stone-200/80 shadow-xl hover:shadow-2xl transition duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-[#7C5CFC]/10 text-[#7C5CFC] font-black flex items-center justify-center mb-6 text-2xl">
                        🌐
                    </div>
                    <h3 class="text-xl font-black text-[#1A1D20] mb-3">
                        {{ $isEn ? 'Customized Public Website' : 'Faqe Publike e Personalizuar' }}
                    </h3>
                    <p class="text-xs text-slate-600 leading-relaxed font-medium mb-6">
                        {{ $isEn
                            ? 'Display services, gallery, staff members, and allow clients to book directly from mobile or desktop.'
                            : 'Shfaqni shërbimet, galeritë e prerjeve, stafet dhe lejojini klientët të rezervojnë direkt nga telefoni apo kompjuteri.' }}
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
                        {{ $isEn
                            ? 'Your Android mobile automatically sends SMS confirmations & reminders at low SIM cost with FCM push.'
                            : 'Celulari juaj dërgon automatikisht SMS konfirmimi e rikujtese pa kosto të larta serveri, me sinkronizim Push në kohë reale.' }}
                    </p>
                    <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-extrabold">SMS SIM Direct Sending</span>
                </div>

                <!-- Card 3 -->
                <div class="p-8 rounded-[2.5rem] bg-white border border-stone-200/80 shadow-xl hover:shadow-2xl transition duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-amber-500/10 text-amber-600 font-black flex items-center justify-center mb-6 text-2xl">
                        📊
                    </div>
                    <h3 class="text-xl font-black text-[#1A1D20] mb-3">
                        {{ $isEn ? 'Mobile APK App & Analytics' : 'Aplikacion Celular APK & Analizë' }}
                    </h3>
                    <p class="text-xs text-slate-600 leading-relaxed font-medium mb-6">
                        {{ $isEn
                            ? 'Manage bookings, payments, customer history, and revenue with dedicated Android APK.'
                            : 'Menaxhoni rezervimet, pagesat, historikun e klientëve dhe ardhjet me Aplikacionin Celular (APK) të dedikuar.' }}
                    </p>
                    <span class="inline-block px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-[11px] font-extrabold">Android &amp; Web Engine</span>
                </div>

            </div>
        </div>
    </section>

    <!-- Interactive SMS Gateway & APK Demo Section -->
    <section id="sms-demo" class="py-20 lg:py-28 bg-[#0A4D44] text-white relative overflow-hidden border-t border-stone-200/80">
        <!-- Background Glow -->
        <div class="absolute -top-24 right-0 w-96 h-96 rounded-full bg-[#7C5CFC]/20 blur-[100px]"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-extrabold uppercase tracking-widest text-emerald-300">LIVE SIMULATION</span>
                <h2 class="text-3xl sm:text-5xl font-black tracking-tight text-white mt-2 mb-4">
                    {{ $isEn ? 'How SMS Gateway Works with APK App?' : 'Si Funksionon SMS Gateway me Aplikacionin APK?' }}
                </h2>
                <p class="text-sm text-emerald-100 font-medium leading-relaxed">
                    {{ $isEn
                        ? 'Your Android phone turns into an automated SMS dispatch hub for all bookings, with no high server fees!'
                        : 'Celulari juaj Android kthehet në një qendër automatike dërgimi SMS për të gjitha rezervimet, pa kosto të larta serveri apo pako të treta!' }}
                </p>
            </div>

            <!-- Demo 3-Step Process Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">

                <!-- Step 1 -->
                <div class="p-8 rounded-[2.5rem] bg-white/10 backdrop-blur-md border border-emerald-400/20 shadow-2xl relative">
                    <span class="absolute -top-4 left-8 px-4 py-1 rounded-full text-xs font-black bg-[#7C5CFC] text-white">
                        {{ $isEn ? 'STEP 1' : 'HAPI 1' }}
                    </span>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-400/20 text-emerald-300 flex items-center justify-center font-black text-xl mb-6 mt-2">
                        📅
                    </div>
                    <h3 class="text-lg font-black text-white mb-2">{{ $isEn ? 'Client Books Online' : 'Klienti Rezervon Online' }}</h3>
                    <p class="text-xs text-emerald-100 leading-relaxed font-medium">
                        {{ $isEn
                            ? 'Client opens your salon page, selects service, stylist and preferred time in seconds.'
                            : 'Klienti futet te faqja e sallonit tuaj, zgjedh shërbimin, berberin dhe orarin e dëshiruar në pak sekonda.' }}
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="p-8 rounded-[2.5rem] bg-white/10 backdrop-blur-md border border-emerald-400/20 shadow-2xl relative">
                    <span class="absolute -top-4 left-8 px-4 py-1 rounded-full text-xs font-black bg-[#7C5CFC] text-white">
                        {{ $isEn ? 'STEP 2' : 'HAPI 2' }}
                    </span>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-400/20 text-emerald-300 flex items-center justify-center font-black text-xl mb-6 mt-2">
                        📲
                    </div>
                    <h3 class="text-lg font-black text-white mb-2">{{ $isEn ? 'System Dispatches FCM Push' : 'Sistemi Dërgon FCM Silent Push' }}</h3>
                    <p class="text-xs text-emerald-100 leading-relaxed font-medium">
                        {{ $isEn
                            ? 'Server dispatches encrypted FCM push signal to your Android Mobile App (APK) in the background.'
                            : 'Serveri i dërgon një sinjal mbrojtës të enkriptuar Firebase Aplikacionit tuaj Celular (APK) në prapaskenë.' }}
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="p-8 rounded-[2.5rem] bg-white/10 backdrop-blur-md border border-emerald-400/20 shadow-2xl relative">
                    <span class="absolute -top-4 left-8 px-4 py-1 rounded-full text-xs font-black bg-[#7C5CFC] text-white">
                        {{ $isEn ? 'STEP 3' : 'HAPI 3' }}
                    </span>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-400/20 text-emerald-300 flex items-center justify-center font-black text-xl mb-6 mt-2">
                        💬
                    </div>
                    <h3 class="text-lg font-black text-white mb-2">{{ $isEn ? 'Phone Sends SMS' : 'Celulari Juaj Dërgon SMS-në' }}</h3>
                    <p class="text-xs text-emerald-100 leading-relaxed font-medium">
                        {{ $isEn
                            ? 'APK app automatically sends SMS confirmation and 2-hour reminder directly from your SIM card!'
                            : 'Aplikacioni APK dërgon automatikisht SMS-në e konfirmimit dhe rikujtesës 2 orë para orarit direkt nga karta SIM!' }}
                    </p>
                </div>

            </div>

            <!-- Live Phone Simulator Card -->
            <div class="max-w-2xl mx-auto p-8 rounded-[2.5rem] bg-[#0F1117] border border-emerald-500/30 shadow-2xl text-left space-y-4">
                <div class="flex items-center justify-between border-b border-stone-800 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse"></div>
                        <span class="text-xs font-black uppercase text-emerald-300 tracking-wider">SMS Gateway Simulator (E4ProTech Engine)</span>
                    </div>
                    <span class="text-[10px] font-extrabold px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300">
                        SIM Status: OK
                    </span>
                </div>

                <div class="p-4 rounded-2xl bg-stone-900 border border-stone-800 font-mono text-xs text-emerald-400 space-y-2">
                    <p class="text-slate-400 text-[11px]">&gt; [14:00:02] Booking #47 created for customer Anisa Kliente</p>
                    <p class="text-slate-400 text-[11px]">&gt; [14:00:03] FCM Silent Push dispatched to Android APK Device</p>
                    <p class="text-emerald-300 font-bold">&gt; [14:00:04] SMS Sent via SIM Card to +35569XXXXXXX: "Pershendetje Anisa! Rezervimi juaj u konfirmua per oren 14:00. Faleminderit!"</p>
                </div>
            </div>

        </div>
    </section>

    <!-- Active Salons Showcase Section -->
    <section id="sallonet" class="py-20 lg:py-28 bg-white border-t border-stone-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-12">
                <div>
                    <span class="text-xs font-extrabold uppercase tracking-widest text-[#7C5CFC]">ACTIVE NETWORK</span>
                    <h2 class="text-3xl sm:text-4xl font-black text-[#1A1D20] tracking-tight mt-1">
                        {{ $isEn ? 'Registered Salons' : 'Sallonet e Regjistruara' }}
                    </h2>
                </div>
                <a href="{{ route('krijo-sallonin') }}" class="px-6 py-3 rounded-full text-xs font-black text-white bg-[#7C5CFC] hover:bg-[#6366F1] transition shadow-lg shadow-[#7C5CFC]/20">
                    {{ $isEn ? '+ Add Your Salon ↗' : '+ Shto Sallonin Tënd ↗' }}
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($shops as $shop)
                    <div class="p-8 rounded-[2.5rem] bg-[#FAF8F2] border border-stone-200/80 hover:border-[#7C5CFC] transition duration-300 flex flex-col justify-between group shadow-md hover:shadow-xl hover:-translate-y-1 transform">
                        <div>
                            <div class="flex items-center justify-between mb-6">
                                @if(!empty($shop->logo))
                                    <img src="{{ asset(ltrim($shop->logo, '/')) }}" alt="{{ $shop->name }}" class="w-14 h-14 rounded-2xl object-cover shadow-md border border-stone-200/80 shrink-0">
                                @else
                                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-black text-2xl text-white shadow-md shrink-0"
                                         style="background: linear-gradient(135deg, {{ $shop->primary_color }}, {{ $shop->secondary_color }});">
                                        {{ mb_substr($shop->name, 0, 1) }}
                                    </div>
                                @endif
                                <span class="text-[11px] px-3.5 py-1 rounded-full font-extrabold uppercase tracking-wider text-white bg-[#0A4D44]">
                                    {{ $shop->resolved_shop_label }}
                                </span>
                            </div>

                            <h4 class="text-2xl font-black text-[#1A1D20] group-hover:text-[#7C5CFC] transition mb-2">
                                {{ $shop->name }}
                            </h4>
                            <p class="text-xs text-slate-500 font-medium leading-relaxed mb-6">
                                {{ $isEn ? 'Type:' : 'Lloji:' }} <strong class="text-slate-800">{{ mb_strtoupper($shop->business_type ?? 'general') }}</strong>. {{ $isEn ? 'Staff:' : 'Stafi:' }} <strong class="text-slate-800">{{ $shop->resolved_staff_label_plural }}</strong>.
                            </p>
                        </div>

                        <div class="pt-6 border-t border-stone-200/80">
                            <a href="{{ route('shop.landing', $shop->slug) }}"
                               class="w-full py-3.5 rounded-full font-black text-xs text-white bg-[#7C5CFC] hover:bg-[#6366F1] flex items-center justify-center gap-2 shadow-md transition transform active:scale-95">
                                <span>{{ $isEn ? 'Visit Page & Book ↗' : 'Visito Faqen & Rezervo ↗' }}</span>
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
                <h2 class="text-3xl sm:text-4xl font-black text-[#1A1D20] tracking-tight mt-1">
                    {{ $isEn ? 'Frequently Asked Questions' : 'Pyetjet Më të Shpeshta' }}
                </h2>
            </div>

            <div class="space-y-4">
                <div class="p-6 rounded-2xl bg-white border border-stone-200/80 shadow-sm">
                    <h4 class="font-black text-sm text-[#1A1D20] mb-2">
                        {{ $isEn ? 'How are confirmation and reminder SMS sent?' : 'Si dërgohen SMS-të e konfirmimit dhe rikujtesës?' }}
                    </h4>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">
                        {{ $isEn
                            ? 'The system connects to your Android phone running E4ProTech Engine. SMS are sent automatically from your SIM card at no extra fee.'
                            : 'Sistemi lidhet me celularin tuaj Android ku është instaluar Aplikacioni E4ProTech Engine. SMS-të dërgohen automatikisht nga karta juaj SIM pa pasur nevojë për kosto shtesë.' }}
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-white border border-stone-200/80 shadow-sm">
                    <h4 class="font-black text-sm text-[#1A1D20] mb-2">
                        {{ $isEn ? 'Is it possible to have a public page with our own link?' : 'A është e mundur të kemi faqe publike me adresën tonë?' }}
                    </h4>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">
                        {{ $isEn
                            ? 'Yes! Every salon gets its unique link (e.g. app.e4protech.com/s/your-salon) where clients book directly.'
                            : 'Po! Çdo sallon merr adresën e tij unike (p.sh: app.e4protech.com/s/salloni-juaj) ku klientët kryejnë rezervime direkte.' }}
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-white border border-stone-200/80 shadow-sm">
                    <h4 class="font-black text-sm text-[#1A1D20] mb-2">
                        {{ $isEn ? 'How long does it take to activate my account?' : 'Sa kohë duhet për të aktivizuar llogarinë time?' }}
                    </h4>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">
                        {{ $isEn
                            ? 'Just 5 minutes! Use the "Create Your Salon" button, fill in details and your panel is instantly ready.'
                            : 'Vetëm 5 minuta! Përdorni butonin "Krijo Salonin Tënd", plotësoni të dhënat dhe paneli juaj është menjëherë gati për punë.' }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer (E4ProTech Dark Teal `#0A4D44`) -->
    <footer class="py-12 bg-[#0A4D44] text-white mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-white text-[#0A4D44] font-black flex items-center justify-center text-sm shrink-0">
                    E4
                </div>
                <span class="text-xs font-medium text-emerald-100 !text-emerald-100">
                    © {{ date('Y') }} E4ProTech Engine — {{ __('Managed IT & SaaS Solutions') }}
                </span>
            </div>

            <div class="flex gap-6 text-xs text-emerald-100 font-bold">
                <a href="{{ route('krijo-sallonin') }}" class="hover:text-amber-300 text-emerald-100 transition">{{ $isEn ? 'Create Your Salon' : 'Krijo Salonin Tënd' }}</a>
                <a href="{{ route('dashboard') }}" class="hover:text-amber-300 text-emerald-100 transition">{{ $isEn ? 'Admin Panel' : 'Paneli Admin' }}</a>
                <a href="{{ route('app.download.apk') }}" class="hover:text-amber-300 text-emerald-100 transition">{{ $isEn ? 'Download APK' : 'Shkarko APK-në' }}</a>
            </div>
        </div>
    </footer>

</body>
</html>
