<!DOCTYPE html>
<html lang="sq" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $shop->name }} | {{ $shop->resolved_shop_label }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @livewireStyles
</head>
<body class="min-h-full flex flex-col justify-between selection:bg-rose-500 selection:text-white antialiased bg-slate-950">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-50 backdrop-blur-md bg-slate-950/80 border-b border-slate-800/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-extrabold text-xl shadow-lg"
                     style="background: linear-gradient(135deg, {{ $shop->primary_color }}, {{ $shop->secondary_color }}); text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                    {{ mb_substr($shop->name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-xl font-black tracking-tight text-white flex items-center gap-2">
                        {{ $shop->name }}
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider text-white/90 border border-white/20"
                              style="background-color: {{ $shop->primary_color }};">
                            {{ $shop->resolved_shop_label }}
                        </span>
                    </h1>
                    <p class="text-xs text-slate-400 font-medium">Orari: 09:00 - 19:00 | Europe/Tirane</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="tel:+355691111111" class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-900 border border-slate-800 text-slate-200 hover:bg-slate-800 transition">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    Kontakt
                </a>
                <a href="#services" class="px-5 py-2.5 rounded-xl text-xs font-black text-white shadow-lg transition transform active:scale-95"
                   style="background: linear-gradient(135deg, {{ $shop->primary_color }}, {{ $shop->secondary_color }});">
                    Rezervo Tani
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative overflow-hidden py-16 lg:py-24 border-b border-slate-800/40">
        <div class="absolute inset-0 bg-gradient-to-tr from-slate-950 via-slate-900 to-slate-950 opacity-90"></div>
        <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full blur-3xl opacity-20" style="background-color: {{ $shop->primary_color }};"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full blur-3xl opacity-20" style="background-color: {{ $shop->secondary_color }};"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold bg-slate-900/80 border border-slate-800 text-slate-300 mb-6">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Mirësevisni te {{ $shop->name }}
            </div>
            <h2 class="text-4xl sm:text-6xl font-black text-white tracking-tight max-w-4xl mx-auto leading-none mb-6">
                Eksperiencë Unike për {{ $shop->resolved_service_label }}
            </h2>
            <p class="text-base sm:text-lg text-slate-400 max-w-2xl mx-auto mb-10 font-medium">
                Rezervoni takimin tuaj online me ekipin tonë profesional. Zgjidhni shërbimin, orarin dhe {{ mb_strtolower($shop->resolved_staff_label) }}un tuaj të preferuar në pak sekonda.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="#services" class="px-8 py-4 rounded-2xl font-extrabold text-sm text-white shadow-xl hover:opacity-95 transition transform hover:-translate-y-0.5"
                   style="background: linear-gradient(135deg, {{ $shop->primary_color }}, {{ $shop->secondary_color }});">
                    Shiko Shërbimet & Çmimet
                </a>
                <a href="#staff" class="px-8 py-4 rounded-2xl font-extrabold text-sm bg-slate-900 border border-slate-800 text-slate-200 hover:bg-slate-800 transition">
                    Shiko {{ $shop->resolved_staff_label_plural }}
                </a>
            </div>
        </div>
    </section>

    <!-- Staff Members Section -->
    <section id="staff" class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h3 class="text-2xl font-black text-white tracking-tight">{{ $shop->resolved_staff_label_plural }} Tanë</h3>
                <p class="text-xs text-slate-400 mt-1 font-medium">Profesionalistët tanë të kualifikuar gati për t'ju shërbyer</p>
            </div>
            <span class="text-xs px-3 py-1 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 font-bold">
                {{ $staff->count() }} {{ $shop->resolved_staff_label_plural }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($staff as $member)
                <div class="p-6 rounded-3xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700 transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center font-black text-2xl text-white shadow-inner"
                             style="color: {{ $shop->primary_color }};">
                            {{ mb_substr($member->name, 0, 1) }}
                        </div>
                        <div>
                            <h4 class="font-extrabold text-base text-white group-hover:text-amber-400 transition">{{ $member->name }}</h4>
                            <p class="text-xs font-bold text-slate-400 mt-0.5">{{ $shop->resolved_staff_label }}</p>
                            @if($member->phone)
                                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    {{ $member->phone }}
                                </p>
                            @endif
                        </div>
                    </div>
                    @if($member->bio)
                        <p class="text-xs text-slate-400 mt-4 leading-relaxed line-clamp-2">{{ $member->bio }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <!-- Services & Pricing Section -->
    <section id="services" class="py-16 bg-slate-900/40 border-y border-slate-800/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h3 class="text-2xl font-black text-white tracking-tight">Shërbimet & Çmimet</h3>
                    <p class="text-xs text-slate-400 mt-1 font-medium">Lista e plotë e shërbimeve të ofruara nga {{ $shop->name }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($services as $service)
                    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800/80 flex justify-between items-center hover:border-slate-700 transition">
                        <div class="pr-4">
                            <div class="flex items-center gap-2">
                                <span class="text-xs px-2.5 py-0.5 rounded-md bg-slate-800 text-slate-300 font-bold uppercase tracking-wider">
                                    {{ $service->category ?: 'Shërbim' }}
                                </span>
                                <span class="text-xs text-slate-500 font-bold flex items-center gap-1">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $service->duration_minutes }} min
                                </span>
                            </div>
                            <h4 class="font-extrabold text-base text-white mt-2">{{ $service->name }}</h4>
                            @if($service->description)
                                <p class="text-xs text-slate-400 mt-1 leading-relaxed">{{ $service->description }}</p>
                            @endif
                        </div>
                        <div class="text-right whitespace-nowrap">
                            <span class="text-xl font-black text-emerald-400 block">
                                {{ number_format($service->price, 0) }} Lekë
                            </span>
                            <button class="mt-2 px-4 py-1.5 rounded-xl text-xs font-bold text-white shadow transition hover:opacity-90"
                                    style="background-color: {{ $shop->primary_color }};">
                                Zgjidh
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Public Booking Component -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">
        <livewire:public-shop-booking :shop="$shop" />
    </section>

    <!-- Footer -->
    <footer class="py-10 border-t border-slate-800/60 bg-slate-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-slate-500 font-medium">
                © {{ date('Y') }} {{ $shop->name }} — Mundësuar nga E4ProTech Engine
            </p>
            <div class="flex gap-4 text-xs text-slate-400 font-semibold">
                <a href="#" class="hover:text-white transition">Kushtet e Shërbimit</a>
                <a href="#" class="hover:text-white transition">Privatësia</a>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
