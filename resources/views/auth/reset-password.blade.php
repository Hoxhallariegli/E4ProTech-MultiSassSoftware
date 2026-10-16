@section('title', __('Reset Password'))

<x-layouts.guest>
    <div class="w-full max-w-lg mx-auto bg-white dark:bg-gray-800 p-5 sm:p-12 rounded-[2.5rem] border border-stone-200/80 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="mb-8 text-center sm:text-left">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-4 sm:py-1.5 rounded-full text-[10px] sm:text-xs font-black uppercase tracking-wider bg-[#0A4D44]/10 text-[#0A4D44] mb-4 max-w-full truncate">
                <span class="w-2 h-2 rounded-full bg-[#7C5CFC] animate-pulse shrink-0"></span>
                <span class="truncate">E4ProTech Engine • {{ __('Managed IT Solutions') }}</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-[#1A1D20] dark:text-white tracking-tight">
                {{ __('Reset Password') }}
            </h2>
            <p class="text-xs font-bold text-slate-500 dark:text-gray-400 mt-2">
                {{ __('Enter your new password to regain access to the panel.') }}
            </p>
        </div>

        @include('errors.messages')

        <form action="{{ route('password.store') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}" />

            <div>
                <label for="email" class="block mb-2 text-xs font-black uppercase tracking-wider text-slate-700 dark:text-gray-300">
                    {{ __('Email Address') }}
                </label>
                <input id="email" name="email" type="email" value="{{ old('email', $request->input('email')) }}" required autofocus
                       class="w-full p-4 text-sm font-bold bg-[#F8FAFC] dark:bg-gray-900 border border-stone-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-[#7C5CFC]/20 dark:text-white transition">
            </div>

            <div>
                <label for="password" class="block mb-2 text-xs font-black uppercase tracking-wider text-slate-700 dark:text-gray-300">
                    {{ __('New Password') }}
                </label>
                <input id="password" name="password" type="password" required
                       class="w-full p-4 text-sm font-bold bg-[#F8FAFC] dark:bg-gray-900 border border-stone-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-[#7C5CFC]/20 dark:text-white transition">
            </div>

            <div>
                <label for="password_confirmation" class="block mb-2 text-xs font-black uppercase tracking-wider text-slate-700 dark:text-gray-300">
                    {{ __('Confirm Password') }}
                </label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                       class="w-full p-4 text-sm font-bold bg-[#F8FAFC] dark:bg-gray-900 border border-stone-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-[#7C5CFC]/20 dark:text-white transition">
            </div>

            <div>
                <button type="submit" class="w-full py-4 px-6 rounded-full font-black text-sm text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-lg shadow-[#7C5CFC]/25 transition transform active:scale-95 flex items-center justify-center gap-2">
                    <span>{{ __('Save New Password') }}</span>
                    <span>&rarr;</span>
                </button>
            </div>
        </form>
    </div>
</x-layouts.guest>
