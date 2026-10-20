<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth bg-[#FAF8F2] text-[#1A1D20] antialiased font-sans light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        (function() {
            document.documentElement.classList.remove('dark');
            document.documentElement.classList.add('light');
            try {
                localStorage.setItem('theme', 'light');
            } catch(e) {}
        })();
    </script>
    <title>E4ProTech Engine — {{ __('Multi-SaaS Salon Management Engine & SIM SMS Gateway') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root, html, body {
            color-scheme: light !important;
            background-color: #FAF8F2 !important;
            color: #1A1D20 !important;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        h1, h2, h3, h4, h5, h6 {
            color: #1A1D20 !important;
        }
        p, label {
            color: #1A1D20 !important;
        }
        .bg-\[\#0A4D44\] *, .bg-\[\#1A1D20\] *, .bg-\[\#7C5CFC\] * {
            color: inherit !important;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between bg-[#FAF8F2] text-[#1A1D20] selection:bg-[#7C5CFC] selection:text-white">

    <!-- Top Navigation Bar (E4ProTech Style & Clean Language Switcher) -->
    <header class="sticky top-0 z-50 backdrop-blur-md bg-[#FAF8F2]/90 border-b border-stone-200/60">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-2 sm:gap-4">
            <a href="/" class="flex items-center gap-2 group shrink-0">
                <img src="{{ asset('E4ProTech.webp') }}" alt="E4ProTech Engine" class="h-16 sm:h-20 w-auto max-w-[200px] sm:max-w-[280px] -my-3 sm:-my-4 object-contain transition transform group-hover:scale-105" onerror="this.onerror=null; this.src='{{ asset('E4ProTech-Engine.png') }}';">
            </a>

            <!-- Nav Links -->
            <nav class="hidden lg:flex items-center gap-8 text-xs font-bold text-slate-700">
                <a href="#welcome" class="hover:text-[#7C5CFC] transition">{{ __('welcome.Home') }}</a>
                <a href="#how-it-works" class="hover:text-[#7C5CFC] transition">{{ __('welcome.How It Works') }}</a>
                <a href="#services" class="hover:text-[#7C5CFC] transition">{{ __('welcome.Services') }}</a>
                <a href="#sallonet" class="hover:text-[#7C5CFC] transition">{{ __('welcome.Salons') }}</a>
                <a href="#faq" class="hover:text-[#7C5CFC] transition">{{ __('welcome.FAQ') }}</a>
            </nav>

            <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                <!-- Language Switcher Pill -->
                <div class="flex items-center gap-0.5 p-0.5 sm:p-1 rounded-full bg-white border border-slate-300 shadow-sm text-[11px] sm:text-xs font-black">
                    <a href="{{ route('language.switch', 'sq') }}"
                       class="px-2 sm:px-3 py-1 sm:py-1.5 rounded-full transition {{ app()->getLocale() === 'sq' ? 'bg-[#7C5CFC] text-white shadow-sm' : 'text-slate-600 hover:text-black' }}">
                        SQ
                    </a>
                    <a href="{{ route('language.switch', 'en') }}"
                       class="px-2 sm:px-3 py-1 sm:py-1.5 rounded-full transition {{ app()->getLocale() === 'en' ? 'bg-[#7C5CFC] text-white shadow-sm' : 'text-slate-600 hover:text-black' }}">
                        EN
                    </a>
                </div>

                <a href="{{ route('login') }}" class="inline-flex items-center gap-1 sm:gap-2 px-2.5 sm:px-5 py-1.5 sm:py-2.5 rounded-full text-[10px] sm:text-xs font-extrabold bg-[#1A1D20] text-white hover:bg-black transition shadow-sm whitespace-nowrap">
                    <span>{{ __('welcome.Login') }}</span>
                    <span class="hidden sm:inline">{{ __('welcome.Panel') }}</span>
                    <span>↗</span>
                </a>
                <a href="{{ route('krijo-sallonin') }}" class="px-2.5 sm:px-6 py-1.5 sm:py-2.5 rounded-full text-[10px] sm:text-xs font-black text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-lg shadow-[#7C5CFC]/25 transition transform active:scale-95 flex items-center gap-1 whitespace-nowrap">
                    <span>{{ __('welcome.Create') }}</span>
                    <span class="hidden sm:inline">{{ __('welcome.Salon') }}</span>
                    <span>↗</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Showcase Section -->
    <section id="welcome" class="relative overflow-hidden py-16 lg:py-28 bg-[#FAF8F2]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

                <!-- Left Column text -->
                <div class="lg:col-span-7 text-left">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-black bg-[#0A4D44]/10 text-[#0A4D44] mb-6">
                        <span class="w-2 h-2 rounded-full bg-[#7C5CFC] animate-pulse"></span>
                        Multi-SaaS Engine • SIM Gateway v1.0.111
                    </div>

                    <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-[#1A1D20] tracking-tight leading-[1.08] mb-6">
                        {!! app()->getLocale() === 'en'
                            ? 'The Complete Engine for <span class="underline decoration-[#7C5CFC] decoration-4 underline-offset-4">Your Salon.</span>'
                            : 'Platforma e Plotë SaaS për <span class="underline decoration-[#7C5CFC] decoration-4 underline-offset-4">Sallonin Tuaj.</span>' !!}
                    </h1>

                    <p class="text-base sm:text-lg text-slate-700 font-extrabold max-w-xl mb-8 leading-relaxed">
                        {!! app()->getLocale() === 'en'
                            ? 'Empower your barbershop, beauty salon, nail studio or spa with 24/7 online booking, automated SIM SMS confirmations, mobile app & real-time analytics.'
                            : 'Fuqizoni berberanën, parukerinë apo qendrën tuaj estetike me rezervime online 24/7, dërgim automatik SMS me SIM pa kosto të larta, dhe Aplikacionin Celular.' !!}
                    </p>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 mb-8">
                        <a href="{{ route('krijo-sallonin') }}" class="px-8 py-4 rounded-full text-sm font-black text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-xl shadow-[#7C5CFC]/25 transition transform active:scale-95 flex items-center justify-center gap-2 text-center">
                            <span>{{ __('welcome.Create Your Salon') }}</span>
                            <span class="text-lg">↗</span>
                        </a>

                        <a href="#sallonet" class="px-8 py-4 rounded-full text-sm font-black text-[#1A1D20] bg-white border border-stone-200/80 hover:bg-stone-50 transition shadow-sm flex items-center justify-center gap-2 text-center">
                            <span>{{ __('welcome.View Partner Salons') }}</span>
                        </a>
                    </div>

                    <div class="flex items-center gap-6 text-xs font-extrabold text-slate-600">
                        <span class="flex items-center gap-1.5"><strong class="text-[#0A4D44]">✓</strong> {{ __('welcome.1 Month Free Trial') }}</span>
                        <span class="flex items-center gap-1.5"><strong class="text-[#0A4D44]">✓</strong> {{ __('welcome.No Credit Card Required') }}</span>
                        <span class="flex items-center gap-1.5"><strong class="text-[#0A4D44]">✓</strong> {{ __('welcome.SIM Gateway') }}</span>
                    </div>
                </div>

                <!-- Right Column Visual Card -->
                <div class="lg:col-span-5">
                    <div class="p-8 sm:p-10 rounded-[2.5rem] bg-white border border-stone-200/80 shadow-2xl relative overflow-hidden">
                        <div class="flex items-center justify-between mb-8 pb-4 border-b border-stone-100">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">DEMO METRICS</span>
                                <h3 class="text-lg font-black text-[#1A1D20]">The Station Barbers</h3>
                            </div>
                            <span class="px-3.5 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800">
                                {{ __('welcome.Satisfaction') }}
                            </span>
                        </div>

                        <div class="space-y-4 mb-6">
                            <div class="p-4 rounded-2xl bg-[#FAF8F2] border border-stone-200/60 flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-slate-500 font-bold">{{ __('welcome.Todays Bookings') }}</p>
                                    <h4 class="text-xl font-black text-[#1A1D20]">{{ __('welcome.24 Online Appointments') }}</h4>
                                </div>
                                <span class="w-10 h-10 rounded-xl bg-[#7C5CFC]/10 text-[#7C5CFC] font-black flex items-center justify-center text-lg">✂️</span>
                            </div>

                            <div class="p-4 rounded-2xl bg-[#FAF8F2] border border-stone-200/60 flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-slate-500 font-bold">{{ __('welcome.SMS Status') }}</p>
                                    <h4 class="text-xl font-black text-emerald-600">{{ __('welcome.100% Sent') }}</h4>
                                </div>
                                <span class="w-10 h-10 rounded-xl bg-[#0A4D44]/10 text-[#0A4D44] font-black flex items-center justify-center text-lg">📲</span>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-[#0A4D44] text-white text-xs font-medium leading-relaxed flex items-center gap-3">
                            <span>"Rezervimet dhe SMS-të e konfirmimit dërgohen automatikisht në kohë reale pa vonesë."</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- How It Works Step-By-Step Section -->
    <section id="how-it-works" class="py-20 lg:py-28 bg-[#0A4D44] text-white border-t border-stone-200/80 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-extrabold uppercase tracking-widest text-emerald-300">HOW IT WORKS</span>
                <h2 class="text-3xl sm:text-5xl font-black tracking-tight text-white mt-2 mb-4">
                    {{ __('welcome.Transforming Your Salon') }}
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="p-8 rounded-[2.5rem] bg-white/10 backdrop-blur-md border border-emerald-400/20 shadow-xl">
                    <span class="text-xs font-black uppercase text-amber-300">{{ __('welcome.STEP 1') }}</span>
                    <h3 class="text-xl font-black text-white mt-2 mb-3">{{ __('welcome.Discovery & Online Page') }}</h3>
                    <p class="text-xs text-emerald-100 leading-relaxed font-medium">
                        {{ app()->getLocale() === 'en'
                            ? 'Clients find your customized public link and view your services, prices and team.'
                            : 'Klientët gjejnë adresën tuaj të personalizuar dhe shohin shërbimet, çmimet dhe ekipin.' }}
                    </p>
                </div>

                <div class="p-8 rounded-[2.5rem] bg-white/10 backdrop-blur-md border border-emerald-400/20 shadow-xl">
                    <span class="text-xs font-black uppercase text-amber-300">{{ __('welcome.STEP 2') }}</span>
                    <h3 class="text-xl font-black text-white mt-2 mb-3">{{ __('welcome.24/7 Online Bookings') }}</h3>
                    <p class="text-xs text-emerald-100 leading-relaxed font-medium">
                        {{ app()->getLocale() === 'en'
                            ? 'Clients select their preferred service, stylist, and time slot 24/7.'
                            : 'Klientët zgjedhin shërbimin, berberin dhe orarin e lirë në çdo moment 24/7.' }}
                    </p>
                </div>

                <div class="p-8 rounded-[2.5rem] bg-white/10 backdrop-blur-md border border-emerald-400/20 shadow-xl">
                    <span class="text-xs font-black uppercase text-amber-300">{{ __('welcome.STEP 3') }}</span>
                    <h3 class="text-xl font-black text-white mt-2 mb-3">{{ __('welcome.Automated SMS & Reminders') }}</h3>
                    <p class="text-xs text-emerald-100 leading-relaxed font-medium">
                        {{ app()->getLocale() === 'en'
                            ? 'Automated confirmation and reminder SMS dispatched directly from your Android mobile SIM.'
                            : 'Dërgim automatik i SMS-ve të konfirmimit dhe rikujtesës direkt nga celulari juaj Android.' }}
                    </p>
                </div>

                <div class="p-8 rounded-[2.5rem] bg-white/10 backdrop-blur-md border border-emerald-400/20 shadow-xl">
                    <span class="text-xs font-black uppercase text-amber-300">{{ __('welcome.STEP 4') }}</span>
                    <h3 class="text-xl font-black text-white mt-2 mb-3">{{ __('welcome.Mobile App & Live Sync') }}</h3>
                    <p class="text-xs text-emerald-100 leading-relaxed font-medium">
                        {{ app()->getLocale() === 'en'
                            ? 'Appointments sync instantly with your mobile app and admin dashboard.'
                            : 'Rezervimi sinkronizohet menjëherë me aplikacionin celular dhe panelin tuaj të kontrollit.' }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Feature Showcase Section -->
    <section id="services" class="py-20 lg:py-28 bg-[#FAF8F2] border-t border-stone-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-extrabold uppercase tracking-widest text-[#7C5CFC]">SERVICES &amp; MODULES</span>
                <h2 class="text-3xl sm:text-5xl font-black text-[#1A1D20] tracking-tight mt-2 mb-4">
                    {{ __('welcome.Powerful Services for Salons') }}
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="p-8 rounded-[2.5rem] bg-white border border-stone-200/80 shadow-xl hover:shadow-2xl transition duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-[#7C5CFC]/10 text-[#7C5CFC] font-black flex items-center justify-center mb-6">
                        <x-heroicon-o-globe-alt class="w-7 h-7 text-[#7C5CFC]" />
                    </div>
                    <h3 class="text-xl font-black text-[#1A1D20] mb-3">
                        {{ __('welcome.Customized Public Website') }}
                    </h3>
                    <p class="text-xs text-slate-600 leading-relaxed font-medium mb-6">
                        {{ app()->getLocale() === 'en'
                            ? 'Display services, gallery, staff members, and allow clients to book directly from mobile or desktop.'
                            : 'Shfaqni shërbimet, galeritë e prerjeve, stafet dhe lejojini klientët të rezervojnë direkt nga telefoni apo kompjuteri.' }}
                    </p>
                    <span class="inline-block px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-[11px] font-extrabold">Web / Mobile Responsive</span>
                </div>

                <!-- Card 2 -->
                <div class="p-8 rounded-[2.5rem] bg-white border border-stone-200/80 shadow-xl hover:shadow-2xl transition duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-[#0A4D44]/10 text-[#0A4D44] font-black flex items-center justify-center mb-6">
                        <x-heroicon-o-device-phone-mobile class="w-7 h-7 text-[#0A4D44]" />
                    </div>
                    <h3 class="text-xl font-black text-[#1A1D20] mb-3">SIM SMS Gateway &amp; Push</h3>
                    <p class="text-xs text-slate-600 leading-relaxed font-medium mb-6">
                        {{ app()->getLocale() === 'en'
                            ? 'Your Android mobile automatically sends SMS confirmations & reminders at low SIM cost with FCM push.'
                            : 'Celulari juaj dërgon automatikisht SMS konfirmimi e rikujtese pa kosto të larta serveri, me sinkronizim Push në kohë reale.' }}
                    </p>
                    <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-extrabold">SMS SIM Direct Sending</span>
                </div>

                <!-- Card 3 -->
                <div class="p-8 rounded-[2.5rem] bg-white border border-stone-200/80 shadow-xl hover:shadow-2xl transition duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-amber-500/10 text-amber-600 font-black flex items-center justify-center mb-6">
                        <x-heroicon-o-chart-bar class="w-7 h-7 text-amber-600" />
                    </div>
                    <h3 class="text-xl font-black text-[#1A1D20] mb-3">
                        {{ __('welcome.Mobile APK App & Analytics') }}
                    </h3>
                    <p class="text-xs text-slate-600 leading-relaxed font-medium mb-6">
                        {{ app()->getLocale() === 'en'
                            ? 'Manage bookings, payments, customer history, and revenue with dedicated Android APK.'
                            : 'Menaxhoni rezervimet, pagesat, historikun e klientëve dhe ardhjet me Aplikacionin Celular (APK) të dedikuar.' }}
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
                    <span class="text-xs font-extrabold uppercase tracking-widest text-[#7C5CFC]">ACTIVE NETWORK</span>
                    <h2 class="text-3xl sm:text-4xl font-black text-[#1A1D20] tracking-tight mt-1">
                        {{ __('welcome.Registered Salons') }}
                    </h2>
                </div>
                <a href="{{ route('krijo-sallonin') }}" class="px-6 py-3 rounded-full text-xs font-black text-white bg-[#7C5CFC] hover:bg-[#6366F1] transition shadow-lg shadow-[#7C5CFC]/20">
                    {{ __('welcome.+ Add Your Salon') }}
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
                                {{ __('welcome.Type:') }} <strong class="text-slate-800">{{ mb_strtoupper($shop->business_type ?? 'general') }}</strong>. {{ __('welcome.Staff:') }} <strong class="text-slate-800">{{ $shop->resolved_staff_label_plural }}</strong>.
                            </p>
                        </div>

                        <div class="pt-6 border-t border-stone-200/80">
                            <a href="{{ route('shop.landing', $shop->slug) }}"
                               class="w-full py-3.5 rounded-full font-black text-xs text-white bg-[#7C5CFC] hover:bg-[#6366F1] flex items-center justify-center gap-2 shadow-md transition transform active:scale-95">
                                <span>{{ __('welcome.Visit Page & Book') }}</span>
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
                    {{ __('welcome.Frequently Asked Questions') }}
                </h2>
            </div>

            <div class="space-y-4">
                <div class="p-6 rounded-2xl bg-white border border-stone-200/80 shadow-sm">
                    <h4 class="font-black text-sm text-[#1A1D20] mb-2">
                        {{ __('welcome.Q1') }}
                    </h4>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">
                        {{ app()->getLocale() === 'en'
                            ? 'The system connects to your Android phone running E4ProTech Engine. SMS are sent automatically from your SIM card at no extra fee.'
                            : 'Sistemi lidhet me celularin tuaj Android ku është instaluar Aplikacioni E4ProTech Engine. SMS-të dërgohen automatikisht nga karta juaj SIM pa pasur nevojë për kosto shtesë.' }}
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-white border border-stone-200/80 shadow-sm">
                    <h4 class="font-black text-sm text-[#1A1D20] mb-2">
                        {{ __('welcome.Q2') }}
                    </h4>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">
                        {{ app()->getLocale() === 'en'
                            ? 'Yes! Every salon gets its unique link (e.g. app.e4protech.com/s/your-salon) where clients book directly.'
                            : 'Po! Çdo sallon merr adresën e tij unike (p.sh: app.e4protech.com/s/salloni-juaj) ku klientët kryejnë rezervime direkte.' }}
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-white border border-stone-200/80 shadow-sm">
                    <h4 class="font-black text-sm text-[#1A1D20] mb-2">
                        {{ __('welcome.Q3') }}
                    </h4>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">
                        {{ app()->getLocale() === 'en'
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
                <a href="{{ route('krijo-sallonin') }}" class="hover:text-amber-300 text-emerald-100 transition">{{ __('welcome.Create Your Salon') }}</a>
                <a href="{{ route('dashboard') }}" class="hover:text-amber-300 text-emerald-100 transition">{{ __('welcome.Login') }}</a>
                <a href="{{ route('app.download.apk') }}" class="hover:text-amber-300 text-emerald-100 transition">Download APK</a>
            </div>
        </div>
    </footer>

</body>
</html>
