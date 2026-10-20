@section('title', __('Control Panel'))

<x-layouts.guest>
    <div class="w-full max-w-lg mx-auto bg-white p-6 sm:p-12 rounded-[2.5rem] border border-stone-200/80 shadow-xl overflow-hidden transition-colors">
        <div class="mb-8 text-center sm:text-left">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-[10px] sm:text-xs font-black uppercase tracking-wider bg-[#0A4D44]/10 text-[#0A4D44] mb-4 max-w-full truncate">
                <span class="w-2 h-2 rounded-full bg-[#7C5CFC] animate-pulse shrink-0"></span>
                <span class="truncate">E4ProTech Engine • {{ __('Managed IT Solutions') }}</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                {{ __('Control Panel') }}
            </h2>
            <p class="text-xs font-bold text-slate-600 mt-2">
                {{ __('Sign in with your account to manage appointments, staff, and salon revenue.') }}
            </p>
        </div>

        @include('errors.messages')

        <form action="{{ route('login') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="email" class="block mb-2 text-xs font-black uppercase tracking-wider text-slate-800">
                    {{ __('Email Address') }}
                </label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                       class="w-full p-4 text-sm font-bold bg-[#F8FAFC] border border-stone-200 rounded-2xl focus:ring-2 focus:ring-[#7C5CFC]/30 text-slate-900 placeholder:text-slate-400 transition"
                       placeholder="admin@e4protech.com">
            </div>

            <div>
                <label for="password" class="block mb-2 text-xs font-black uppercase tracking-wider text-slate-800">
                    {{ __('Password') }}
                </label>
                <input id="password" name="password" type="password" required
                       class="w-full p-4 text-sm font-bold bg-[#F8FAFC] border border-stone-200 rounded-2xl focus:ring-2 focus:ring-[#7C5CFC]/30 text-slate-900 placeholder:text-slate-400 transition"
                       placeholder="••••••••">
            </div>

            <div class="flex items-center justify-between text-xs font-bold gap-2">
                <label class="flex items-center gap-2 text-slate-700 shrink-0 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-[#7C5CFC] focus:ring-[#7C5CFC]">
                    <span>{{ __('Remember me') }}</span>
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-[#7C5CFC] hover:underline font-extrabold text-right">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <div>
                <button type="submit" class="w-full py-4 px-6 rounded-full font-black text-sm text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-lg shadow-[#7C5CFC]/25 transition transform active:scale-95 flex items-center justify-center gap-2">
                    <span>{{ __('Sign In to Panel') }}</span>
                    <span>&rarr;</span>
                </button>
            </div>
        </form>
    </div>
</x-layouts.guest>
