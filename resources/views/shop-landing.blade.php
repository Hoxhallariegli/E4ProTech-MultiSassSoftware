<!DOCTYPE html>
<html lang="sq" class="h-full bg-[#0F1117] text-slate-100">
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
<body class="min-h-full flex flex-col justify-between selection:bg-[#FF9F0A] selection:text-black antialiased bg-[#0F1117]">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-50 backdrop-blur-md bg-[#0F1117]/85 border-b border-stone-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="#" class="flex items-center gap-3 group">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black text-xl text-white shadow-xl transition transform group-hover:scale-105"
                     style="background: linear-gradient(135deg, {{ $shop->primary_color ?: '#FF9F0A' }}, {{ $shop->secondary_color ?: '#1C1C1E' }}); text-shadow: 0 2px 4px rgba(0,0,0,0.4);">
                    {{ mb_substr($shop->name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-lg font-black tracking-tight text-white flex items-center gap-2">
                        {{ $shop->name }}
                        <span class="text-[10px] px-2.5 py-0.5 rounded-full font-extrabold uppercase tracking-wider text-black bg-[#FF9F0A]">
                            {{ $shop->resolved_shop_label }}
                        </span>
                    </h1>
                    <p class="text-[11px] text-emerald-400 font-bold flex items-center gap-1.5 mt-0.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Hapur tani • 09:00 - 19:00
                    </p>
                </div>
            </a>

            <div class="flex items-center gap-3">
                @if($shop->phone)
                    <a href="tel:{{ $shop->phone }}" class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-xs font-bold bg-[#1A1D24] border border-stone-800 text-slate-200 hover:bg-stone-800 transition">
                        <span>📞 Kontakt</span>
                    </a>
                @endif
                <a href="#booking-section" class="px-6 py-2.5 rounded-full text-xs font-black text-black bg-[#FF9F0A] hover:bg-amber-400 shadow-lg shadow-[#FF9F0A]/20 transition transform active:scale-95">
                    Rezervo Tani ↗
                </a>
            </div>
        </div>
    </header>

    <!-- Luxury Barber & Beauty Hero Section -->
    <section class="relative overflow-hidden py-16 lg:py-24 border-b border-stone-800/60 bg-gradient-to-b from-[#141722] via-[#0F1117] to-[#0F1117]">
        <!-- Background Ambient Glow -->
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[600px] h-[300px] rounded-full blur-[120px] opacity-25" style="background-color: {{ $shop->primary_color ?: '#FF9F0A' }};"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">

            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-extrabold bg-[#1A1D24] border border-amber-500/30 text-[#FF9F0A] mb-6 shadow-lg">
                <span>✨</span> {{ $shop->resolved_shop_label }} Zyrtare • {{ $shop->name }}
            </div>

            <!-- Hero Main Title -->
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight leading-[1.08] max-w-4xl mx-auto mb-6">
                Eksperiencë Premium për <span class="text-[#FF9F0A]">{{ $shop->resolved_service_label }}</span> &amp; Stilim.
            </h1>

            <!-- Paragraph -->
            <p class="text-base sm:text-lg text-slate-400 max-w-2xl mx-auto mb-10 font-medium leading-relaxed">
                Rezervoni takimin tuaj online me ekipin tonë profesional në pak sekonda. Zgjidhni shërbimin, orarin dhe {{ mb_strtolower($shop->resolved_staff_label) }}un tuaj të preferuar 24/7.
            </p>

            <!-- Hero Actions -->
            <div class="flex flex-wrap justify-center gap-4">
                <a href="#booking-section"
                   class="px-8 py-4 rounded-full font-black text-sm text-black bg-[#FF9F0A] hover:bg-amber-400 shadow-xl shadow-[#FF9F0A]/25 transition transform active:scale-95 flex items-center justify-center gap-2">
                    <span>Rezervo Takim Online</span>
                    <span>↗</span>
                </a>

                <a href="#services"
                   class="px-8 py-4 rounded-full font-extrabold text-sm text-white bg-[#1A1D24] border border-stone-800 hover:bg-stone-800 transition flex items-center justify-center">
                    Shiko Shërbimet &amp; Çmimet
                </a>
            </div>
        </div>
    </section>

    <!-- Staff Members Section -->
    <section id="staff" class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="flex items-center justify-between mb-10">
            <div>
                <span class="text-xs font-extrabold uppercase tracking-widest text-[#FF9F0A]">Ekipi Ynë</span>
                <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight mt-1">{{ $shop->resolved_staff_label_plural }} Tanë</h2>
            </div>
            <span class="text-xs px-3.5 py-1.5 rounded-full bg-[#1A1D24] border border-stone-800 text-slate-300 font-extrabold">
                {{ $staff->count() }} {{ $shop->resolved_staff_label_plural }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($staff as $member)
                <div class="p-6 rounded-[2rem] bg-[#161822] border border-stone-800/80 hover:border-[#FF9F0A]/50 transition duration-300 group shadow-lg">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-[#1A1D24] border border-stone-700 flex items-center justify-center font-black text-2xl text-[#FF9F0A] shadow-inner">
                            {{ mb_substr($member->name, 0, 1) }}
                        </div>
                        <div>
                            <h4 class="font-black text-lg text-white group-hover:text-[#FF9F0A] transition">{{ $member->name }}</h4>
                            <p class="text-xs font-extrabold text-slate-400 mt-0.5">{{ $shop->resolved_staff_label }}</p>
                            @if($member->phone)
                                <p class="text-xs text-slate-500 mt-1 font-bold">
                                    📞 {{ $member->phone }}
                                </p>
                            @endif
                        </div>
                    </div>
                    @if($member->bio)
                        <p class="text-xs text-slate-400 mt-4 leading-relaxed line-clamp-2 font-medium">{{ $member->bio }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <!-- Services & Pricing Section -->
    <section id="services" class="py-16 bg-[#12141C] border-y border-stone-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <span class="text-xs font-extrabold uppercase tracking-widest text-[#FF9F0A]">Çmimet &amp; Kohëzgjatja</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight mt-1">Shërbimet e Ofruara</h2>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($services as $service)
                    <div class="p-6 rounded-[2rem] bg-[#161822] border border-stone-800/80 flex justify-between items-center hover:border-[#FF9F0A]/50 transition duration-300 shadow-md">
                        <div class="pr-4">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-[#1A1D24] text-[#FF9F0A] font-extrabold uppercase tracking-wider border border-amber-500/20">
                                    {{ $service->category ?: $shop->resolved_service_label }}
                                </span>
                                <span class="text-xs text-slate-400 font-bold">
                                    ⏱️ {{ $service->duration_minutes }} min
                                </span>
                            </div>
                            <h4 class="font-black text-lg text-white mt-2">{{ $service->name }}</h4>
                            @if($service->description)
                                <p class="text-xs text-slate-400 mt-1 leading-relaxed font-medium">{{ $service->description }}</p>
                            @endif
                        </div>
                        <div class="text-right whitespace-nowrap">
                            <span class="text-xl font-black text-emerald-400 block mb-2">
                                {{ number_format($service->price, 0) }} Lekë
                            </span>
                            <a href="#booking-section"
                               class="px-4 py-2 rounded-full text-xs font-black text-black bg-[#FF9F0A] hover:bg-amber-400 shadow transition inline-block">
                                Zgjidh ↗
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Interactive Livewire Online Booking Component -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 w-full">
        <livewire:public-shop-booking :shop="$shop" />
    </section>

    <!-- Footer -->
    <footer class="py-10 border-t border-stone-800/80 bg-[#0B0C10] mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-slate-500 font-medium">
                © {{ date('Y') }} {{ $shop->name }} — Mundësuar nga E4ProTech Engine
            </p>
            <div class="flex gap-4 text-xs text-slate-400 font-bold">
                <a href="#booking-section" class="hover:text-[#FF9F0A] transition">Rezervo Online</a>
                <a href="{{ route('admin.settings') }}" class="hover:text-[#FF9F0A] transition">Paneli Admin</a>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
