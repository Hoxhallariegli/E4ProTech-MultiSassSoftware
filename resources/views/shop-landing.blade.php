<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full bg-[#FAF8F2] text-[#1A1D20] light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        document.documentElement.classList.remove('dark');
        document.documentElement.classList.add('light');
    </script>
    <title>{{ $shop->name }} | {{ $shop->resolved_shop_label }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        html, body {
            color-scheme: light !important;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @livewireStyles
</head>
<body class="min-h-full flex flex-col justify-between selection:bg-[#7C5CFC] selection:text-white antialiased bg-[#FAF8F2] text-[#1A1D20]">

    @php
        $isEn = app()->getLocale() === 'en';

        // Dynamic Primary & Secondary Colors from barber_shops table
        $primaryColor = !empty($shop->primary_color) ? $shop->primary_color : '#7C5CFC';
        $secondaryColor = !empty($shop->secondary_color) ? $shop->secondary_color : '#0A4D44';

        // Fallback for neutral legacy dark hexes
        if (in_array(strtolower($secondaryColor), ['#1c1c1e', '#1c1c1f', '#000000', '#111827'])) {
            $secondaryColor = '#0A4D44';
        }

        $heroBadge = __('public_booking.Hero Badge', ['shop' => $shop->name]);
        $heroTitle = __('public_booking.Hero Title');
        $heroSubtitle = __('public_booking.Hero Subtitle');
        $heroButton = __('public_booking.Book Appointment Online');
        $contactPhone = $shop->phone ?? '';
        $footerText = "© " . date('Y') . " {$shop->name} — " . ($isEn ? "Powered by E4ProTech Engine" : "Mundësuar nga E4ProTech Engine");
    @endphp

    <!-- Top Navigation Bar (Managed Colors & Language Switcher) -->
    <header class="sticky top-0 z-50 backdrop-blur-md bg-[#FAF8F2]/95 border-b border-stone-200/60">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-2.5 sm:py-0 sm:h-20 flex items-center justify-between gap-2">
            <!-- Brand Logo & Name -->
            <a href="#" class="flex items-center gap-2 sm:gap-3 min-w-0 group">
                @if(!empty($shop->logo))
                    <img src="{{ asset(ltrim($shop->logo, '/')) }}" alt="{{ $shop->name }}" class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl object-cover shadow-md border border-stone-200/60 transition transform group-hover:scale-105 shrink-0">
                @else
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl flex items-center justify-center font-black text-lg sm:text-xl text-white shadow-md transition transform group-hover:scale-105 shrink-0"
                         style="background: linear-gradient(135deg, {{ $primaryColor }}, {{ $secondaryColor }});">
                        {{ mb_substr($shop->name, 0, 1) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <h1 class="text-sm sm:text-lg font-black tracking-tight text-[#1A1D20] truncate flex items-center gap-1.5">
                        <span class="truncate">{{ $shop->name }}</span>
                        <span class="hidden md:inline-block text-[10px] px-2.5 py-0.5 rounded-full font-extrabold uppercase tracking-wider shrink-0"
                              style="background-color: {{ $secondaryColor }}1A; color: {{ $secondaryColor }};">
                            {{ $shop->resolved_shop_label }}
                        </span>
                    </h1>
                    <p class="text-[10px] sm:text-[11px] font-extrabold flex items-center gap-1 mt-0.5 truncate" style="color: {{ $secondaryColor }};">
                        <span class="w-1.5 h-1.5 rounded-full animate-pulse shrink-0" style="background-color: {{ $primaryColor }};"></span>
                        <span class="truncate">{{ app()->getLocale() === 'en' ? 'Open now • 09:00 - 19:00' : 'Hapur tani • 09:00 - 19:00' }}</span>
                    </p>
                </div>
            </a>

            <!-- Right Actions (Language Switcher & CTA Button) -->
            <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                <!-- Language Switcher Pill -->
                <div class="flex items-center gap-0.5 p-0.5 sm:p-1 rounded-full bg-white border border-slate-300 shadow-sm text-[11px] sm:text-xs font-black">
                    <a href="{{ route('language.switch', 'sq') }}"
                       class="px-2 sm:px-3 py-1 sm:py-1.5 rounded-full transition {{ app()->getLocale() === 'sq' ? 'text-white shadow-sm' : 'text-slate-600 hover:text-black' }}"
                       style="{{ app()->getLocale() === 'sq' ? 'background-color: '.$primaryColor.';' : '' }}">
                        SQ
                    </a>
                    <a href="{{ route('language.switch', 'en') }}"
                       class="px-2 sm:px-3 py-1 sm:py-1.5 rounded-full transition {{ app()->getLocale() === 'en' ? 'text-white shadow-sm' : 'text-slate-600 hover:text-black' }}"
                       style="{{ app()->getLocale() === 'en' ? 'background-color: '.$primaryColor.';' : '' }}">
                        EN
                    </a>
                </div>

                @if(!empty($contactPhone))
                    <a href="tel:{{ $contactPhone }}" class="hidden md:inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-extrabold bg-white border border-slate-300 text-[#1A1D20] hover:bg-stone-50 transition shadow-sm">
                        <span>{{ app()->getLocale() === 'en' ? 'Contact' : 'Kontakt' }}</span>
                    </a>
                @endif

                <a href="#booking-section"
                   class="px-3.5 sm:px-6 py-2 sm:py-2.5 rounded-full text-[11px] sm:text-xs font-black text-white shadow-md transition transform active:scale-95 whitespace-nowrap"
                   style="background-color: {{ $primaryColor }};">
                    <span class="hidden sm:inline">{!! $heroButton !!}</span>
                    <span class="sm:hidden">{{ app()->getLocale() === 'en' ? 'Book ↗' : 'Rezervo ↗' }}</span>
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
                                    <h4 class="text-xl font-black text-[#1A1D20]">{{ $shop->getStaffCountLabel($staff->count()) }}</h4>
                                </div>
                                <span class="w-10 h-10 rounded-xl font-black flex items-center justify-center text-lg"
                                      style="background-color: {{ $primaryColor }}15; color: {{ $primaryColor }};">
                                    <x-heroicon-o-scissors class="w-5 h-5" />
                                </span>
                            </div>

                            <div class="p-4 rounded-2xl bg-[#FAF8F2] border border-stone-200/60 flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-slate-500 font-bold">{{ app()->getLocale() === 'en' ? 'Offered Services' : 'Shërbimet e Ofruara' }}</p>
                                    <h4 class="text-xl font-black text-[#1A1D20]">{{ $services->count() }} {{ app()->getLocale() === 'en' ? 'Options' : 'Opsione' }}</h4>
                                </div>
                                <span class="w-10 h-10 rounded-xl font-black flex items-center justify-center text-lg"
                                      style="background-color: {{ $secondaryColor }}15; color: {{ $secondaryColor }};">
                                    <x-heroicon-o-sparkles class="w-5 h-5" />
                                </span>
                            </div>
                        </div>

                        <!-- Arrow 3: Quote Card Box (Secondary Managed Color) -->
                        <div class="p-4 rounded-2xl text-white text-xs font-medium leading-relaxed flex items-center gap-3 shadow-md"
                             style="background-color: {{ $secondaryColor }};">
                            <span class="text-2xl">💡</span>
                            <span>{{ __('public_booking.Hero Quote') }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Interactive Livewire Online Booking Component -->
    <section id="booking-section" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 w-full scroll-mt-6">
        <livewire:public-shop-booking :shop="$shop" />
    </section>

    <!-- Footer (Managed Secondary Color) -->
    <footer class="py-10 text-white mt-auto" style="background-color: {{ $secondaryColor }}">
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

    <script>
        function selectServiceAndScroll(serviceId) {
            if (window.Livewire) {
                window.Livewire.dispatch('preselect-service', { serviceId: serviceId });
            }
            const elem = document.getElementById('booking-section');
            if (elem) {
                elem.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    </script>

    @livewireScripts
</body>
</html>



