@section('title', __('Forgot Password'))

<x-layouts.guest>
    <div class="w-full max-w-lg mx-auto bg-white dark:bg-gray-800 p-5 sm:p-12 rounded-[2.5rem] border border-stone-200/80 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="mb-8 text-center sm:text-left">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-4 sm:py-1.5 rounded-full text-[10px] sm:text-xs font-black uppercase tracking-wider bg-[#0A4D44]/10 text-[#0A4D44] mb-4 max-w-full truncate">
                <span class="w-2 h-2 rounded-full bg-[#7C5CFC] animate-pulse shrink-0"></span>
                <span class="truncate">E4ProTech Engine • {{ __('Managed IT Solutions') }}</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-[#1A1D20] dark:text-white tracking-tight">
                {{ __('Forgot Password?') }}
            </h2>
            <p class="text-xs font-bold text-slate-500 dark:text-gray-400 mt-2 leading-relaxed">
                {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link.') }}
            </p>
        </div>

        @include('errors.messages')

        <form action="{{ route('password.email') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="email" class="block mb-2 text-xs font-black uppercase tracking-wider text-slate-700 dark:text-gray-300">
                    {{ __('Email Address') }}
                </label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                       class="w-full p-4 text-sm font-bold bg-[#F8FAFC] dark:bg-gray-900 border border-stone-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-[#7C5CFC]/20 dark:text-white transition"
                       placeholder="email@e4protech.com">
            </div>

            <div>
                <button type="submit" class="w-full py-4 px-6 rounded-full font-black text-sm text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-lg shadow-[#7C5CFC]/25 transition transform active:scale-95 flex items-center justify-center gap-2">
                    <span>{{ __('Send Password Reset Link') }}</span>
                    <span>&rarr;</span>
                </button>
            </div>
        </form>

        <div class="mt-8 pt-6 border-t border-stone-100 dark:border-gray-700 flex items-center justify-center text-xs font-bold text-slate-500 dark:text-gray-400">
            <a href="{{ route('login') }}" class="text-[#7C5CFC] dark:text-[#7C5CFC] hover:underline font-black flex items-center gap-1.5">
                <span>&larr;</span>
                <span>{{ __('Back to Login') }}</span>
            </a>
        </div>
    </div>
</x-layouts.guest>
