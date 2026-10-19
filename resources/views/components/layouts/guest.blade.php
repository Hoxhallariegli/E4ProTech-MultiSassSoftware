<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#FAF8F2] text-[#1A1D20] overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') — {{ config('app.name', 'E4ProTech Engine') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col justify-between bg-[#FAF8F2] text-[#1A1D20] antialiased selection:bg-[#7C5CFC] selection:text-white overflow-x-hidden max-w-full">

    <!-- Top Navigation Bar matching Welcome Page exactly -->
    <header class="sticky top-0 z-50 backdrop-blur-md bg-[#FAF8F2]/90 border-b border-stone-200/60 w-full overflow-hidden">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-2 sm:gap-4">
            <a href="/" class="flex items-center gap-2 group shrink-0">
                <img src="{{ asset('E4ProTech.webp') }}" alt="E4ProTech Engine" class="h-16 sm:h-20 w-auto max-w-[200px] sm:max-w-[280px] -my-3 sm:-my-4 object-contain transition transform group-hover:scale-105" onerror="this.onerror=null; this.src='{{ asset('E4ProTech-Engine.png') }}';">
            </a>

            <!-- Nav Links -->
            <nav class="hidden md:flex items-center gap-6 lg:gap-8 text-xs font-bold text-slate-700">
                <a href="/" class="hover:text-[#7C5CFC] transition">{{ __('guest.Home') }}</a>
                <a href="/#how-it-works" class="hover:text-[#7C5CFC] transition">{{ __('guest.How It Works') }}</a>
                <a href="/#services" class="hover:text-[#7C5CFC] transition">{{ __('guest.Services') }}</a>
                <a href="/#sallonet" class="hover:text-[#7C5CFC] transition">{{ __('guest.Salons') }}</a>
                <a href="/#faq" class="hover:text-[#7C5CFC] transition">{{ __('guest.FAQ') }}</a>
            </nav>

            <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
                <div class="flex items-center gap-0.5 p-0.5 sm:p-1 rounded-xl bg-stone-200/60 text-[10px] sm:text-xs font-bold">
                    <a href="{{ route('language.switch', 'sq') }}" class="px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg transition {{ app()->getLocale() === 'sq' ? 'bg-[#0A4D44] text-white' : 'text-slate-600 hover:text-black' }}">
                        SQ
                    </a>
                    <a href="{{ route('language.switch', 'en') }}" class="px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg transition {{ app()->getLocale() === 'en' ? 'bg-[#0A4D44] text-white' : 'text-slate-600 hover:text-black' }}">
                        EN
                    </a>
                </div>

                @if(!request()->routeIs('login') && !request()->routeIs('password.*'))
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1 px-2.5 sm:px-4 py-1.5 sm:py-2.5 rounded-full text-[10px] sm:text-xs font-black bg-[#1A1D20] text-white hover:bg-black transition shadow-sm whitespace-nowrap">
                        <span>{{ __('guest.Login') }}</span>
                        <span>↗</span>
                    </a>
                @endif

                <a href="{{ route('krijo-sallonin') }}" class="px-2.5 sm:px-5 py-1.5 sm:py-2.5 rounded-full text-[10px] sm:text-xs font-black text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-md transition transform active:scale-95 flex items-center gap-1 whitespace-nowrap">
                    <span>{{ __('guest.Create') }}</span>
                    <span class="hidden sm:inline">{{ __('guest.Salon') }}</span>
                    <span>↗</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 flex items-center justify-center py-8 sm:py-12 px-3 sm:px-6 lg:px-8 bg-[#FAF8F2] w-full max-w-full overflow-hidden">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-[#FAF8F2] border-t border-stone-200/60 py-6 text-center text-xs font-medium text-slate-500 w-full overflow-hidden">
        &copy; {{ date('Y') }} E4ProTech Engine Multi-SaaS — {{ __('guest.Powered by E4ProTech') }}
    </footer>

</body>
</html>
