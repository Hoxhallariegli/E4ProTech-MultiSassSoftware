<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full bg-[#FAF8F2] text-[#1A1D20]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $shop->name }} | {{ $shop->resolved_shop_label }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @livewireStyles
</head>
<body class="min-h-full flex flex-col justify-between selection:bg-[#7C5CFC] selection:text-white antialiased bg-[#FAF8F2] text-[#1A1D20]">

    @php
        use App\Services\ShopTranslationService;

        // Dynamic Primary & Secondary Colors from barber_shops table
        $primaryColor = !empty($shop->primary_color) ? $shop->primary_color : '#7C5CFC';
        $secondaryColor = !empty($shop->secondary_color) ? $shop->secondary_color : '#0A4D44';

        // Fallback for neutral legacy dark hexes
        if (in_array(strtolower($secondaryColor), ['#1c1c1e', '#1c1c1f', '#000000', '#111827'])) {
            $secondaryColor = '#0A4D44';
        }

        $heroBadge = ShopTranslationService::getShopText($shop, 'hero_badge', "✨ {$shop->resolved_shop_label} Zyrtare • {$shop->name}");
        $heroTitle = ShopTranslationService::getShopText($shop, 'hero_title', "Eksperiencë Premium për {$shop->resolved_service_label} & Stilim");
        $heroSubtitle = ShopTranslationService::getShopText($shop, 'hero_subtitle', "Rezervoni takimin tuaj online me ekipin tonë profesional në pak sekonda. Zgjidhni shërbimin, orarin dhe stafin tuaj të preferuar 24/7.");
        $heroButton = ShopTranslationService::getShopText($shop, 'hero_button', "Rezervo Takim Online ↗");
        $servicesBadge = ShopTranslationService::getShopText($shop, 'services_badge', "Çmimet & Kohëzgjatja");
        $servicesTitle = ShopTranslationService::getShopText($shop, 'services_title', "Shërbimet e Ofruara");
        $staffBadge = ShopTranslationService::getShopText($shop, 'staff_badge', "Ekipi Ynë");
        $staffTitle = ShopTranslationService::getShopText($shop, 'staff_title', "{$shop->resolved_staff_label_plural} Tanë");
        $contactPhone = ShopTranslationService::getShopText($shop, 'contact_phone', $shop->phone ?? '');
        $footerText = ShopTranslationService::getShopText($shop, 'footer_text', "© " . date('Y') . " {$shop->name} — Mundësuar nga E4ProTech Engine");
    @endphp

    <!-- Top Navigation Bar (Managed Colors & Language Switcher) -->
    <header class="sticky top-0 z-50 backdrop-blur-md bg-[#FAF8F2]/90 border-b border-stone-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="#" class="flex items-center gap-3 group">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black text-xl text-white shadow-md transition transform group-hover:scale-105"
                     style="background: linear-gradient(135deg, {{ $primaryColor }}, {{ $secondaryColor }});">
                    {{ mb_substr($shop->name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-lg font-black tracking-tight text-[#1A1D20] flex items-center gap-2">
                        {{ $shop->name }}
                        <span class="text-[10px] px-2.5 py-0.5 rounded-full font-extrabold uppercase tracking-wider"
                              style="background-color: {{ $secondaryColor }}1A; color: {{ $secondaryColor }};">
                            {{ $shop->resolved_shop_label }}
                        </span>
                    </h1>
                    <p class="text-[11px] font-extrabold flex items-center gap-1.5 mt-0.5" style="color: {{ $secondaryColor }};">
                        <span class="w-2 h-2 rounded-full animate-pulse" style="background-color: {{ $primaryColor }};"></span>
                        {{ app()->getLocale() === 'en' ? 'Open now • 09:00 - 19:00' : 'Hapur tani • 09:00 - 19:00' }}
                    </p>
                </div>
            </a>

            <div class="flex items-center gap-3">
                <!-- Language Switcher Pill -->
                <div class="flex items-center gap-1 p-1 rounded-full bg-white border border-slate-300 shadow-sm text-xs font-black">
                    <a href="{{ route('language.switch', 'sq') }}"
                       class="px-3 py-1.5 rounded-full transition {{ app()->getLocale() === 'sq' ? 'text-white shadow-sm' : 'text-slate-600 hover:text-black' }}"
                       style="{{ app()->getLocale() === 'sq' ? 'background-color: '.$primaryColor.';' : '' }}">
                        🇦🇱 SQ
                    </a>
                    <a href="{{ route('language.switch', 'en') }}"
                       class="px-3 py-1.5 rounded-full transition {{ app()->getLocale() === 'en' ? 'text-white shadow-sm' : 'text-slate-600 hover:text-black' }}"
                       style="{{ app()->getLocale() === 'en' ? 'background-color: '.$primaryColor.';' : '' }}">
                        🇬🇧 EN
                    </a>
                </div>

                @if(!empty($contactPhone))
                    <a href="tel:{{ $contactPhone }}" class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-extrabold bg-white border border-slate-300 text-[#1A1D20] hover:bg-stone-50 transition shadow-sm">
                        <span>{{ app()->getLocale() === 'en' ? 'Contact' : 'Kontakt' }}</span>
                    </a>
                @endif

                <a href="#booking-section" class="px-6 py-2.5 rounded-full text-xs font-black text-white shadow-lg transition transform active:scale-95"
                   style="background-color: {{ $primaryColor }};">
                    {!! $heroButton !!}
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section (Managed Dynamic Colors & High-End Welcome Layout) -->
    <section class="relative overflow-hidden py-16 lg:py-24 border-b border-stone-200/60 bg-[#FAF8F2]">
        <!-- Background Ambient Glow -->
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[600px] h-[300px] rounded-full blur-[120px] opacity-15"
             style="background-color: {{ $primaryColor }};"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

                <!-- Left Hero Text Column -->
                <div class="lg:col-span-7 text-left">

                    <!-- Arrow 1: Top Pill Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-black mb-6"
                         style="background-color: {{ $secondaryColor }}1A; color: {{ $secondaryColor }};">
                        <span class="w-2 h-2 rounded-full animate-pulse" style="background-color: {{ $primaryColor }};"></span>
                        {{ $heroBadge }}
                    </div>

                    <!-- Arrow 2: Hero Main Title with Managed Primary Underline Highlight -->
                    <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-[#1A1D20] tracking-tight leading-[1.08] mb-6">
                        <span class="underline decoration-4 underline-offset-4" style="text-decoration-color: {{ $primaryColor }};">{{ $heroTitle }}</span>
                    </h1>

                    <p class="text-base sm:text-lg text-slate-600 max-w-xl mb-8 font-medium leading-relaxed">
                        {{ $heroSubtitle }}
                    </p>

                    <!-- Hero Actions -->
                    <div class="flex flex-col sm:flex-row items-center gap-4 mb-8">
                        <a href="#booking-section"
                           class="w-full sm:w-auto px-8 py-4 rounded-full font-black text-sm text-white shadow-xl transition transform active:scale-95 flex items-center justify-center gap-2"
                           style="background-color: {{ $primaryColor }};">
                            <span>{!! $heroButton !!}</span>
                        </a>

                        <a href="#services"
                           class="w-full sm:w-auto px-8 py-4 rounded-full font-extrabold text-sm text-[#1A1D20] bg-white border border-slate-300 hover:bg-stone-50 transition flex items-center justify-center gap-2 shadow-sm">
                            <span>{{ app()->getLocale() === 'en' ? 'View Services & Pricing' : 'Shiko Shërbimet & Çmimet' }}</span>
                        </a>
                    </div>

                    <div class="flex items-center gap-6 text-xs font-extrabold text-slate-600">
                        <span class="flex items-center gap-1.5"><strong style="color: {{ $secondaryColor }};">✓</strong> {{ app()->getLocale() === 'en' ? '24/7 Booking' : 'Rezervim 24/7' }}</span>
                        <span class="flex items-center gap-1.5"><strong style="color: {{ $secondaryColor }};">✓</strong> {{ app()->getLocale() === 'en' ? 'SMS Confirmation' : 'Konfirmim me SMS' }}</span>
                        <span class="flex items-center gap-1.5"><strong style="color: {{ $secondaryColor }};">✓</strong> {{ app()->getLocale() === 'en' ? 'Trained Staff' : 'Staf i Trajnuar' }}</span>
                    </div>
                </div>

                <!-- Right Visual Stats & Quote Card Column -->
                <div class="lg:col-span-5 relative">
                    <div class="p-8 rounded-[2.5rem] bg-white border border-stone-200/80 shadow-2xl relative z-10">
                        <div class="flex items-center justify-between mb-6">
                            <span class="text-xs font-black uppercase tracking-wider" style="color: {{ $primaryColor }};">
                                {{ app()->getLocale() === 'en' ? 'STATISTICS & TRUST' : 'STATISTIKAT & BESUESHMËRIA' }}
                            </span>
                            <span class="text-xs px-3 py-1 rounded-full font-extrabold" style="background-color: {{ $secondaryColor }}1A; color: {{ $secondaryColor }};">
                                {{ app()->getLocale() === 'en' ? '99.4% Satisfaction' : '99.4% Kënaqësi' }}
                            </span>
                        </div>

                        <div class="space-y-4 mb-6">
                            <div class="p-4 rounded-2xl bg-[#FAF8F2] border border-stone-200/60 flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-slate-500 font-bold">{{ app()->getLocale() === 'en' ? 'Active Staff' : 'Stafi Aktiv' }}</p>
                                    <h4 class="text-xl font-black text-[#1A1D20]">{{ $staff->count() }} {{ $shop->resolved_staff_label_plural }}</h4>
                                </div>
                                <span class="w-10 h-10 rounded-xl font-black flex items-center justify-center text-lg"
                                      style="background-color: {{ $primaryColor }}15; color: {{ $primaryColor }};">✂️</span>
                            </div>

                            <div class="p-4 rounded-2xl bg-[#FAF8F2] border border-stone-200/60 flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-slate-500 font-bold">{{ app()->getLocale() === 'en' ? 'Offered Services' : 'Shërbimet e Ofruara' }}</p>
                                    <h4 class="text-xl font-black text-[#1A1D20]">{{ $services->count() }} {{ app()->getLocale() === 'en' ? 'Options' : 'Opsione' }}</h4>
                                </div>
                                <span class="w-10 h-10 rounded-xl font-black flex items-center justify-center text-lg"
                                      style="background-color: {{ $secondaryColor }}15; color: {{ $secondaryColor }};">⭐</span>
                            </div>
                        </div>

                        <!-- Arrow 3: Quote Card Box (Secondary Managed Color) -->
                        <div class="p-4 rounded-2xl text-white text-xs font-medium leading-relaxed flex items-center gap-3 shadow-md"
                             style="background-color: {{ $secondaryColor }};">
                            <span class="text-2xl">💡</span>
                            <span>
                                {{ app()->getLocale() === 'en'
                                    ? '"Book your appointment in just 30 seconds. Choose your preferred person and schedule from any device."'
                                    : '"Rezervoni takimin tuaj në vetëm 30 sekonda. Zgjidhni personin dhe orarin tuaj të preferuar nga çdo pajisje."' }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Arrow 4: The Dark Wave Section ("HOW WE WORK" / "SI FUNKSIONON" - Managed Secondary Color) -->
    <section id="how-it-works" class="relative text-white py-20 lg:py-28" style="background-color: {{ $secondaryColor }};">

        <!-- Jagged Top Wave Shape Divider -->
        <div class="absolute top-0 left-0 right-0 h-6 bg-[#FAF8F2]"
             style="clip-path: polygon(0 0, 25% 100%, 50% 0, 75% 100%, 100% 0, 100% 0, 0 0);"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pt-6">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-extrabold uppercase tracking-widest text-amber-300">
                    {{ app()->getLocale() === 'en' ? 'HOW IT WORKS' : 'SI FUNKSIONON' }}
                </span>
                <h2 class="text-3xl sm:text-5xl font-black tracking-tight text-white mt-2 mb-4">
                    {{ app()->getLocale() === 'en' ? 'Your Salon Experience, Step By Step' : 'Transformimi i Sallonit Tuaj, Hap Pas Hapi' }}
                </h2>
                <p class="text-sm text-teal-100 font-medium">
                    {{ app()->getLocale() === 'en'
                        ? "Every booking is simple, fast, and 100% secure at {$shop->name}:"
                        : "Çdo biznes është unik. Ja si funksionon rezervimi online te {$shop->name}:" }}
                </p>
            </div>

            <!-- Steps Grid (Glassmorphic Cards) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">

                <!-- Step 1 -->
                <div class="p-8 rounded-[2rem] bg-white/10 backdrop-blur-md border border-white/15">
                    <span class="text-xs font-black uppercase tracking-widest text-amber-300">
                        {{ app()->getLocale() === 'en' ? 'STEP 1' : 'HAPI 1' }}
                    </span>
                    <h3 class="text-xl font-black text-white mt-2 mb-3">
                        {{ app()->getLocale() === 'en' ? 'Discovery & Online Page' : 'Discovery & Faqja Online' }}
                    </h3>
                    <p class="text-xs text-teal-100 leading-relaxed font-medium">
                        {{ app()->getLocale() === 'en'
                            ? 'Clients find your customized public page 24/7 with all services and pricing.'
                            : 'Klientët gjejnë faqen tuaj publike të personalizuar 24/7 me të gjitha shërbimet dhe çmimet.' }}
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="p-8 rounded-[2rem] bg-white/10 backdrop-blur-md border border-white/15">
                    <span class="text-xs font-black uppercase tracking-widest text-amber-300">
                        {{ app()->getLocale() === 'en' ? 'STEP 2' : 'HAPI 2' }}
                    </span>
                    <h3 class="text-xl font-black text-white mt-2 mb-3">
                        {{ app()->getLocale() === 'en' ? '24/7 Booking' : 'Rezervime 24/7' }}
                    </h3>
                    <p class="text-xs text-teal-100 leading-relaxed font-medium">
                        {{ app()->getLocale() === 'en'
                            ? 'Clients choose their preferred barber/stylist, service, and available time.'
                            : 'Klientët zgjedhin berberin/parukieren, shërbimin dhe orarin e lirë që ju përshtatet më së miri.' }}
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="p-8 rounded-[2rem] bg-white/10 backdrop-blur-md border border-white/15">
                    <span class="text-xs font-black uppercase tracking-widest text-amber-300">
                        {{ app()->getLocale() === 'en' ? 'STEP 3' : 'HAPI 3' }}
                    </span>
                    <h3 class="text-xl font-black text-white mt-2 mb-3">
                        {{ app()->getLocale() === 'en' ? 'SMS & Reminders' : 'SMS & Rikujtesa' }}
                    </h3>
                    <p class="text-xs text-teal-100 leading-relaxed font-medium">
                        {{ app()->getLocale() === 'en'
                            ? 'Automated confirmation and SMS reminders sent for every appointment.'
                            : 'Sistemi llogarit dhe dërgon konfirmimin si dhe rikujtesën automatike me SMS për çdo takim.' }}
                    </p>
                </div>

                <!-- Step 4 -->
                <div class="p-8 rounded-[2rem] bg-white/10 backdrop-blur-md border border-white/15">
                    <span class="text-xs font-black uppercase tracking-widest text-amber-300">
                        {{ app()->getLocale() === 'en' ? 'STEP 4' : 'HAPI 4' }}
                    </span>
                    <h3 class="text-xl font-black text-white mt-2 mb-3">
                        {{ app()->getLocale() === 'en' ? 'App & Live Sync' : 'Aplikacion & Live Sync' }}
                    </h3>
                    <p class="text-xs text-teal-100 leading-relaxed font-medium">
                        {{ app()->getLocale() === 'en'
                            ? 'Appointments sync instantly with your mobile app and admin dashboard.'
                            : 'Rezervimi sinkronizohet menjëherë në aplikacionin tuaj celular dhe panelin admin.' }}
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- Staff Members Section -->
    <section id="staff" class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="flex items-center justify-between mb-10">
            <div>
                <span class="text-xs font-black uppercase tracking-widest" style="color: {{ $primaryColor }};">{{ $staffBadge }}</span>
                <h2 class="text-2xl sm:text-4xl font-black text-[#1A1D20] tracking-tight mt-1">{{ $staffTitle }}</h2>
            </div>
            <span class="text-xs px-4 py-2 rounded-full bg-white border border-stone-200 text-[#1A1D20] font-extrabold shadow-sm">
                {{ $staff->count() }} {{ $shop->resolved_staff_label_plural }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($staff as $member)
                <div class="p-6 rounded-[2rem] bg-white border border-stone-200/80 transition duration-300 group shadow-md hover:shadow-xl">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center font-black text-2xl"
                             style="background-color: {{ $secondaryColor }}15; color: {{ $secondaryColor }}; border: 1px solid {{ $secondaryColor }}30;">
                            {{ mb_substr($member->name, 0, 1) }}
                        </div>
                        <div>
                            <h4 class="font-black text-lg text-[#1A1D20] transition">{{ $member->name }}</h4>
                            <p class="text-xs font-extrabold text-slate-500 mt-0.5">{{ $shop->resolved_staff_label }}</p>
                            @if($member->phone)
                                <p class="text-xs text-slate-400 mt-1 font-bold">
                                    {{ $member->phone }}
                                </p>
                            @endif
                        </div>
                    </div>
                    @if($member->bio)
                        <p class="text-xs text-slate-600 mt-4 leading-relaxed line-clamp-2 font-medium">{{ $member->bio }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <!-- Services & Pricing Section -->
    <section id="services" class="py-16 bg-[#F5F2E9] border-y border-stone-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <span class="text-xs font-black uppercase tracking-widest" style="color: {{ $secondaryColor }};">{{ $servicesBadge }}</span>
                    <h2 class="text-2xl sm:text-4xl font-black text-[#1A1D20] tracking-tight mt-1">{{ $servicesTitle }}</h2>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($services as $service)
                    <div class="p-6 rounded-[2rem] bg-white border border-stone-200/80 flex justify-between items-center transition duration-300 shadow-sm hover:shadow-md">
                        <div class="pr-4">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] px-2.5 py-0.5 rounded-full font-extrabold uppercase tracking-wider"
                                      style="background-color: {{ $secondaryColor }}1A; color: {{ $secondaryColor }}; border: 1px solid {{ $secondaryColor }}30;">
                                    {{ $service->category ?: $shop->resolved_service_label }}
                                </span>
                                <span class="text-xs text-slate-500 font-bold">
                                    {{ $service->duration_minutes }} min
                                </span>
                            </div>
                            <h4 class="font-black text-lg text-[#1A1D20] mt-2">{{ $service->name }}</h4>
                            @if($service->description)
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed font-medium">{{ $service->description }}</p>
                            @endif
                        </div>
                        <div class="text-right whitespace-nowrap">
                            <span class="text-xl font-black block mb-2" style="color: {{ $secondaryColor }};">
                                {{ number_format($service->price, 0) }} {{ $shop->resolved_currency }}
                            </span>
                            <a href="#booking-section"
                               class="px-5 py-2 rounded-full text-xs font-black text-white shadow transition inline-block"
                               style="background-color: {{ $primaryColor }};">
                                {{ app()->getLocale() === 'en' ? 'Select ↗' : 'Zgjidh ↗' }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Interactive Livewire Online Booking Component -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 w-full">
        <livewire:public-shop-booking :shop="$shop" />
    </section>

    <!-- Footer (Managed Secondary Color) -->
    <footer class="py-10 text-white mt-auto" style="background-color: {{ $secondaryColor }};">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <span class="text-xs text-white/90 font-medium !text-white">
                {{ $footerText }}
            </span>
            <div class="flex gap-6 text-xs text-white/90 font-bold">
                <a href="#booking-section" class="hover:text-amber-300 text-white transition">{{ app()->getLocale() === 'en' ? 'Book Online' : 'Rezervo Online' }}</a>
                <a href="{{ route('dashboard') }}" class="hover:text-amber-300 text-white transition">{{ app()->getLocale() === 'en' ? 'Admin Panel' : 'Paneli Admin' }}</a>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>



