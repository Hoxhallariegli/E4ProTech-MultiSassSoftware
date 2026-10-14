@section('title', __('Rivendos Fjalëkalimin'))

<x-layouts.guest>
    <div class="w-full max-w-lg mx-auto bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] border border-stone-200/80 dark:border-gray-700 shadow-sm">
        <div class="mb-8 text-center sm:text-left">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider bg-[#0A4D44]/10 text-[#0A4D44] mb-4">
                <span class="w-2 h-2 rounded-full bg-[#7C5CFC] animate-pulse"></span>
                <span>E4ProTech Engine • Managed IT Solutions</span>
            </div>
            <h2 class="text-3xl font-black text-[#1A1D20] dark:text-white tracking-tight">
                {{ __('Rivendos Fjalëkalimin') }}
            </h2>
            <p class="text-xs font-bold text-slate-500 dark:text-gray-400 mt-2">
                {{ __('Vendosni fjalëkalimin tuaj të ri për të rikuperuar aksesin në panel.') }}
            </p>
        </div>

        @include('errors.messages')

        <form action="{{ route('password.store') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}" />

            <div>
                <label for="email" class="block mb-2 text-xs font-black uppercase tracking-wider text-slate-700 dark:text-gray-300">
                    {{ __('Adresa Email') }}
                </label>
                <input id="email" name="email" type="email" value="{{ old('email', $request->input('email')) }}" required autofocus
                       class="w-full p-4 text-sm font-bold bg-[#F8FAFC] dark:bg-gray-900 border border-stone-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-[#7C5CFC]/20 dark:text-white transition">
            </div>

            <div>
                <label for="password" class="block mb-2 text-xs font-black uppercase tracking-wider text-slate-700 dark:text-gray-300">
                    {{ __('Fjalëkalimi i Ri') }}
                </label>
                <input id="password" name="password" type="password" required
                       class="w-full p-4 text-sm font-bold bg-[#F8FAFC] dark:bg-gray-900 border border-stone-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-[#7C5CFC]/20 dark:text-white transition">
            </div>

            <div>
                <label for="password_confirmation" class="block mb-2 text-xs font-black uppercase tracking-wider text-slate-700 dark:text-gray-300">
                    {{ __('Konfirmo Fjalëkalimin') }}
                </label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                       class="w-full p-4 text-sm font-bold bg-[#F8FAFC] dark:bg-gray-900 border border-stone-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-[#7C5CFC]/20 dark:text-white transition">
            </div>

            <div>
                <button type="submit" class="w-full py-4 px-6 rounded-full font-black text-sm text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-lg shadow-[#7C5CFC]/25 transition transform active:scale-95 flex items-center justify-center gap-2">
                    <span>{{ __('Ruaj Fjalëkalimin e Ri') }}</span>
                    <span>&rarr;</span>
                </button>
            </div>
        </form>
    </div>
</x-layouts.guest>
