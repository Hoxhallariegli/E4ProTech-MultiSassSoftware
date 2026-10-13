<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50 dark:bg-gray-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') — {{ config('app.name', 'E4ProTech Engine') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col justify-between bg-[#F8FAFC] dark:bg-gray-900 text-gray-900 dark:text-gray-100 antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Top Navigation Bar matching SaaS Landing Page -->
    <header class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md border-b border-gray-200/80 dark:border-gray-700/80 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white font-black text-lg flex items-center justify-center shadow-lg shadow-indigo-600/30">
                    E4
                </div>
                <div>
                    <h1 class="text-base font-black tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                        E4PROTECH
                        <span class="text-[9px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider text-indigo-700 bg-indigo-50 dark:bg-indigo-900/50 dark:text-indigo-300">
                            Engine Multi-SaaS
                        </span>
                    </h1>
                </div>
            </a>

            <div class="flex items-center gap-3">
                <a href="/" class="px-5 py-2.5 rounded-2xl text-xs font-black bg-gray-900 text-white dark:bg-white dark:text-gray-900 hover:bg-gray-800 transition">
                    Kreu &rarr;
                </a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-2xl text-xs font-black text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition">
                        Krijo Salonin &nearr;
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-6 text-center text-xs font-medium text-gray-500 dark:text-gray-400">
        &copy; {{ date('Y') }} E4ProTech Engine Multi-SaaS. All rights reserved.
    </footer>

</body>
</html>
