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
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-2 sm:gap-4">
            <a href="/" class="flex items-center gap-2 sm:gap-3 group shrink">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-[#0A4D44] flex items-center justify-center font-black text-base sm:text-lg text-white shadow-md transition transform group-hover:scale-105 shrink-0">
                    E4
                </div>
                <div class="truncate">
                    <h1 class="text-base sm:text-xl font-black tracking-tight text-[#1A1D20] leading-none truncate">
                        E4PROTECH
                    </h1>
                    <p class="hidden sm:block text-[10px] text-[#0A4D44] font-extrabold tracking-wider uppercase">Engine Multi-SaaS</p>
                </div>
            </a>

            <!-- Nav Links -->
            <nav class="hidden md:flex items-center gap-8 text-xs font-bold text-slate-700">
                <a href="/" class="hover:text-[#7C5CFC] transition">{{ __('Kreu') }}</a>
                <a href="/#how-it-works" class="hover:text-[#7C5CFC] transition">{{ __('Si Funksionon') }}</a>
                <a href="/#services" class="hover:text-[#7C5CFC] transition">{{ __('Shërbimet') }}</a>
                <a href="/#sallonet" class="hover:text-[#7C5CFC] transition">{{ __('Sallonet') }}</a>
                <a href="/#faq" class="hover:text-[#7C5CFC] transition">{{ __('Pyetjet') }}</a>
            </nav>

            <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                <div class="flex items-center gap-1 bg-stone-200/60 p-1 rounded-xl text-xs font-bold">
                    <a href="{{ route('language.switch', 'sq') }}" class="px-2.5 py-1 rounded-lg transition {{ app()->getLocale() == 'sq' ? 'bg-[#0A4D44] text-white' : 'text-slate-600 hover:text-black' }}">
                        SQ
                    </a>
                    <a href="{{ route('language.switch', 'en') }}" class="px-2.5 py-1 rounded-lg transition {{ app()->getLocale() == 'en' ? 'bg-[#0A4D44] text-white' : 'text-slate-600 hover:text-black' }}">
                        EN
                    </a>
                </div>
                <a href="{{ route('krijo-sallonin') }}" class="px-3.5 sm:px-6 py-2 sm:py-2.5 rounded-full text-[11px] sm:text-xs font-black text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-lg shadow-[#7C5CFC]/25 transition transform active:scale-95 flex items-center gap-1 whitespace-nowrap">
                    <span>{{ __('Krijo') }}</span>
                    <span class="hidden sm:inline">{{ __('Salonin') }}</span>
                    <span>↗</span>
                </a>
            </div>
        </div>
    </header>
                    {{ __('Krijo Salonin') }} ↗
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-[#FAF8F2]">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-[#FAF8F2] border-t border-stone-200/60 py-6 text-center text-xs font-medium text-slate-500">
        &copy; {{ date('Y') }} E4ProTech Engine Multi-SaaS — {{ __('Mundësuar nga E4ProTech.') }}
    </footer>

</body>
</html>
