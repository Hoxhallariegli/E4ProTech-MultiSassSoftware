@section('title', __('Identifikohu në Panel'))

<x-layouts.guest>
    <div class="w-full max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">

        <!-- Left Side: Login Form -->
        <div class="lg:col-span-6 bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="mb-8">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 mb-4">
                    <span>⚡ E4ProTech Engine</span> • <span>Managed IT Solutions</span>
                </div>
                <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                    Paneli i Kontrollit
                </h2>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-2">
                    Identifikohuni me llogarinë tuaj për të menaxhuar takimet, stafi dhe xhiron e sallonit tuaj.
                </p>
            </div>

            @include('errors.messages')

            <form action="{{ route('login') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="block mb-2 text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        {{ __('Adresa Email') }}
                    </label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                           class="w-full p-3.5 text-sm font-bold bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-indigo-500/20 dark:text-white transition"
                           placeholder="magazinier@e4protech.com">
                </div>

                <div>
                    <label for="password" class="block mb-2 text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        {{ __('Fjalëkalimi') }}
                    </label>
                    <input id="password" name="password" type="password" required
                           class="w-full p-3.5 text-sm font-bold bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-indigo-500/20 dark:text-white transition"
                           placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between text-xs font-bold">
                    <label class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span>Lëmë të kyçur</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                            {{ __('Harruat fjalëkalimin?') }}
                        </a>
                    @endif
                </div>

                <div>
                    <button type="submit" class="w-full py-4 px-6 rounded-2xl font-black text-sm text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-600/30 transition transform active:scale-95 flex items-center justify-center gap-2">
                        <span>Identifikohu në Panel</span>
                        <span>&rarr;</span>
                    </button>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs font-bold text-gray-500 dark:text-gray-400">
                <span>S'keni ende llogari?</span>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-black">
                        Krijo Llogari të Re
                    </a>
                @endif
            </div>
        </div>

        <!-- Right Side: Feature Highlight Box matching Welcome Page -->
        <div class="lg:col-span-6 space-y-6">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] border border-gray-200 dark:border-gray-700 shadow-sm space-y-6">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400">STATISTICS &amp; GROWTH</span>
                    <span class="px-3 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                        98.8% Kënaqësi
                    </span>
                </div>

                <div class="space-y-4">
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/60 rounded-2xl border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Rezervime Sot</span>
                            <span class="text-xl font-black text-gray-900 dark:text-white mt-0.5 block">24 Takime Online</span>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300 flex items-center justify-center font-bold">
                            📈
                        </div>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-900/60 rounded-2xl border border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Statusi i SMS-ve</span>
                            <span class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5 block">100% Dërguar</span>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-300 flex items-center justify-center font-bold">
                            📲
                        </div>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-[#064E3B] text-white text-xs font-bold space-y-2">
                    <p class="text-emerald-300 font-black flex items-center gap-1.5">
                        <span>💡</span>
                        <span>Përfitimet e Platformës</span>
                    </p>
                    <p class="leading-relaxed opacity-90">
                        "Klientët rezervojnë vetë 24/7. Faqja juaj e personalizuar punon edhe ndërsa ju jeni në punë apo pushim."
                    </p>
                </div>
            </div>
        </div>

    </div>
</x-layouts.guest>
