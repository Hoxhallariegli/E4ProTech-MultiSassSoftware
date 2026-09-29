<!DOCTYPE html>
<html lang="sq" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E4ProTech Engine | Multi-SaaS Platforma e Salloneve</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full flex flex-col justify-between antialiased bg-slate-950 selection:bg-amber-500 selection:text-black">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-50 backdrop-blur-md bg-slate-950/80 border-b border-slate-800/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-500 to-rose-500 flex items-center justify-center font-black text-lg text-black shadow-lg shadow-amber-500/20">
                    E4
                </div>
                <div>
                    <h1 class="text-lg font-black tracking-tight text-white leading-none">
                        E4ProTech Engine
                    </h1>
                    <p class="text-[10px] text-slate-400 font-medium">Multi-SaaS Platforma e Salloneve & Qendrave të Bukurisë</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.settings') }}" class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-extrabold bg-slate-900 border border-slate-800 text-slate-200 hover:bg-slate-800 transition">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Paneli Admin
                </a>
                <a href="{{ route('app.download.apk') }}" class="px-5 py-2.5 rounded-xl text-xs font-black text-black bg-gradient-to-r from-amber-400 to-rose-400 hover:opacity-95 shadow-lg transition transform active:scale-95 flex items-center gap-2">
                    <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 002 3h12a3 3 0 002-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Shkarko APK-në
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Header -->
    <section class="relative overflow-hidden py-16 lg:py-20 border-b border-slate-800/40">
        <div class="absolute inset-0 bg-gradient-to-b from-amber-500/10 via-transparent to-transparent opacity-50"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-extrabold bg-slate-900 border border-slate-800 text-amber-400 mb-6 shadow-xl">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                E4ProTech SaaS Directory
            </div>
            <h2 class="text-4xl sm:text-6xl font-black text-white tracking-tight max-w-4xl mx-auto leading-none mb-6">
                Sallonet & Qendrat e Bukurisë në Platformën Tonë
            </h2>
            <p class="text-base sm:text-lg text-slate-400 max-w-2xl mx-auto mb-10 font-medium">
                Zgjidhni një nga sallonet partnere për të parë shërbimet, stafin dhe për të kryer rezervim online në kohë reale.
            </p>
        </div>
    </section>

    <!-- Active Salon Landing Pages Grid -->
    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h3 class="text-2xl font-black text-white tracking-tight">Sallonet & Dyqanet Active</h3>
                <p class="text-xs text-slate-400 mt-1 font-medium">Kliko mbi cilindo sallon për të parë faqen e tij publike të rezervimeve</p>
            </div>
            <span class="text-xs px-3.5 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-amber-400 font-extrabold">
                {{ $shops->count() }} Sallone Active
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($shops as $shop)
                <div class="p-8 rounded-[2.5rem] bg-slate-900/80 border border-slate-800 hover:border-slate-700 transition duration-300 flex flex-col justify-between group shadow-xl hover:shadow-2xl hover:-translate-y-1 transform">
                    <div>
                        <!-- Header & Badge -->
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-black text-2xl text-white shadow-lg"
                                 style="background: linear-gradient(135deg, {{ $shop->primary_color }}, {{ $shop->secondary_color }}); text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                                {{ mb_substr($shop->name, 0, 1) }}
                            </div>
                            <span class="text-[11px] px-3 py-1 rounded-full font-extrabold uppercase tracking-wider text-white border border-white/20 shadow-sm"
                                  style="background-color: {{ $shop->primary_color }};">
                                {{ $shop->resolved_shop_label }}
                            </span>
                        </div>

                        <!-- Shop Info -->
                        <h4 class="text-xl font-black text-white group-hover:text-amber-400 transition mb-2">
                            {{ $shop->name }}
                        </h4>
                        <p class="text-xs text-slate-400 font-medium leading-relaxed mb-6">
                            Lloji i biznesit: <strong class="text-slate-200">{{ mb_strtoupper($shop->business_type ?? 'general') }}</strong>. Stafi: <strong class="text-slate-200">{{ $shop->resolved_staff_label_plural }}</strong>.
                        </p>
                    </div>

                    <!-- CTA Link -->
                    <div class="pt-6 border-t border-slate-800/80">
                        <a href="{{ route('shop.landing', $shop->slug) }}"
                           class="w-full py-3.5 rounded-2xl font-black text-xs text-white flex items-center justify-center gap-2 shadow-lg transition transform active:scale-95 group-hover:brightness-110"
                           style="background: linear-gradient(135deg, {{ $shop->primary_color }}, {{ $shop->secondary_color }});">
                            <span>Visito Faqen & Rezervo Online</span>
                            <svg class="w-4 h-4 text-white group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-10 border-t border-slate-800/60 bg-slate-950 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-slate-500 font-medium">
                © {{ date('Y') }} E4ProTech Engine — Multi-SaaS Platforma e Salloneve
            </p>
            <div class="flex gap-6 text-xs text-slate-400 font-bold">
                <a href="{{ route('admin.settings') }}" class="hover:text-amber-400 transition">Paneli Admin</a>
                <a href="{{ route('app.download.apk') }}" class="hover:text-amber-400 transition">Shkarko APK-në</a>
            </div>
        </div>
    </footer>

</body>
</html>
