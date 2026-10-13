<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#FAF8F2] text-[#1A1D20]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') — {{ config('app.name', 'E4ProTech Engine') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col justify-between bg-[#FAF8F2] text-[#1A1D20] antialiased selection:bg-[#7C5CFC] selection:text-white">

    <!-- Top Navigation Bar matching Welcome Page exactly -->
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
                <a href="/" class="hover:text-[#7C5CFC] transition">Kreu</a>
                <a href="/#how-it-works" class="hover:text-[#7C5CFC] transition">Si Funksionon</a>
                <a href="/#services" class="hover:text-[#7C5CFC] transition">Shërbimet</a>
                <a href="/#sallonet" class="hover:text-[#7C5CFC] transition">Sallonet</a>
                <a href="/#faq" class="hover:text-[#7C5CFC] transition">Pyetjet</a>
            </nav>

            <div class="flex items-center gap-3">
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="px-6 py-2.5 rounded-full text-xs font-black text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-lg shadow-[#7C5CFC]/25 transition transform active:scale-95 flex items-center gap-2">
                        Krijo Salonin ↗
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-[#FAF8F2]">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-[#FAF8F2] border-t border-stone-200/60 py-6 text-center text-xs font-medium text-slate-500">
        &copy; {{ date('Y') }} E4ProTech Engine Multi-SaaS — Mundësuar nga E4ProTech.
    </footer>

</body>
</html>
