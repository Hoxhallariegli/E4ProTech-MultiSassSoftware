<div class="min-h-screen flex flex-col justify-between bg-[#FAF8F2] dark:bg-[#FAF8F2] text-[#1A1D20] dark:text-[#1A1D20] antialiased selection:bg-[#7C5CFC] selection:text-white">

    @php $isEn = app()->getLocale() === 'en'; @endphp

    <!-- Top Navigation Bar (E4ProTech Style & Clean Language Switcher) -->
    <header class="sticky top-0 z-50 backdrop-blur-md bg-[#FAF8F2]/90 dark:bg-[#FAF8F2]/90 border-b border-stone-200/60">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-2 sm:gap-4">
            <a href="/" class="flex items-center gap-2 sm:gap-3 group shrink">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-[#0A4D44] flex items-center justify-center font-black text-base sm:text-lg text-white shadow-md transition transform group-hover:scale-105 shrink-0">
                    E4
                </div>
                <div class="truncate">
                    <h1 class="text-base sm:text-xl font-black tracking-tight text-[#1A1D20] dark:text-[#1A1D20] leading-none truncate">
                        E4PROTECH
                    </h1>
                    <p class="hidden sm:block text-[10px] text-[#0A4D44] font-extrabold tracking-wider uppercase">Engine Multi-SaaS</p>
                </div>
            </a>

            <!-- Nav Links -->
            <nav class="hidden lg:flex items-center gap-8 text-xs font-bold text-slate-700 dark:text-slate-700">
                <a href="/" class="hover:text-[#7C5CFC] transition">{{ $isEn ? 'Home' : 'Kreu' }}</a>
                <a href="/#how-it-works" class="hover:text-[#7C5CFC] transition">{{ $isEn ? 'How It Works' : 'Si Funksionon' }}</a>
                <a href="/#services" class="hover:text-[#7C5CFC] transition">{{ $isEn ? 'Services' : 'Shërbimet' }}</a>
                <a href="/#sallonet" class="hover:text-[#7C5CFC] transition">{{ $isEn ? 'Salons' : 'Sallonet' }}</a>
            </nav>

            <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                <!-- Language Switcher Pill -->
                <div class="flex items-center gap-0.5 p-0.5 sm:p-1 rounded-full bg-white dark:bg-white border border-slate-300 shadow-sm text-[11px] sm:text-xs font-black">
                    <a href="{{ route('language.switch', 'sq') }}"
                       class="px-2 sm:px-3 py-1 sm:py-1.5 rounded-full transition {{ !$isEn ? 'bg-[#7C5CFC] text-white shadow-sm' : 'text-slate-600 hover:text-black' }}">
                        <span class="hidden sm:inline">🇦🇱 </span>SQ
                    </a>
                    <a href="{{ route('language.switch', 'en') }}"
                       class="px-2 sm:px-3 py-1 sm:py-1.5 rounded-full transition {{ $isEn ? 'bg-[#7C5CFC] text-white shadow-sm' : 'text-slate-600 hover:text-black' }}">
                        <span class="hidden sm:inline">🇬🇧 </span>EN
                    </a>
                </div>

                <a href="{{ route('dashboard') }}" class="hidden md:inline-flex items-center gap-2 px-4 sm:px-5 py-2 sm:py-2.5 rounded-full text-xs font-extrabold bg-[#1A1D20] text-white hover:bg-black transition shadow-sm">
                    {{ $isEn ? 'Admin Panel ↗' : 'Paneli Admin ↗' }}
                </a>
                <a href="{{ route('login') }}" class="px-3.5 sm:px-6 py-2 sm:py-2.5 rounded-full text-[11px] sm:text-xs font-black text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-lg shadow-[#7C5CFC]/25 transition transform active:scale-95 flex items-center gap-1 whitespace-nowrap">
                    <span>{{ $isEn ? 'Login' : 'Hyr' }}</span>
                    <span>↗</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container Area -->
    <main class="py-12 lg:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full flex-grow">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            <!-- Left Info & Trust Hero Column -->
            <div class="lg:col-span-6 text-left">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-black bg-[#0A4D44]/10 text-[#0A4D44] mb-6">
                    <span class="w-2 h-2 rounded-full bg-[#7C5CFC] animate-pulse"></span>
                    ✨ {{ $isEn ? 'Your salon, ready in 5 minutes' : 'Saloni juaj, gati në 5 minuta' }}
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-[#1A1D20] dark:text-[#1A1D20] tracking-tight leading-[1.1] mb-6">
                    {!! $isEn
                        ? 'Create Online Booking Page for <span class="underline decoration-[#7C5CFC] decoration-4 underline-offset-4">Your Salon.</span>'
                        : 'Krijo Faqen e Rezervimeve për <span class="underline decoration-[#7C5CFC] decoration-4 underline-offset-4">Sallonin Tuaj.</span>' !!}
                </h1>

                <p class="text-base text-slate-700 dark:text-slate-700 max-w-lg mb-8 font-extrabold leading-relaxed">
                    {{ $isEn
                        ? 'Create and test free for 30 days. Pay only when going live — no credit card required.'
                        : 'Krijojeni dhe provojeni falas për 30 ditë. Paguani vetëm kur bëhet publik — nuk kërkohet asnjë kartë krediti.' }}
                </p>

                <!-- Value Props Badges Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-10">
                    <div class="p-4 rounded-2xl bg-white dark:bg-white border border-stone-200/80 shadow-sm flex items-start gap-3">
                        <span class="w-8 h-8 rounded-xl bg-[#0A4D44]/10 text-[#0A4D44] font-black flex items-center justify-center text-sm shrink-0">✓</span>
                        <div>
                            <h4 class="font-black text-xs text-[#1A1D20] dark:text-[#1A1D20]">{{ $isEn ? '30 Days Free Trial' : '30 Ditë Provë Falas' }}</h4>
                            <p class="text-[11px] text-slate-600 dark:text-slate-600 font-extrabold">{{ $isEn ? 'Full access to all features' : 'Qasje e plotë në të gjitha funksionet' }}</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-white dark:bg-white border border-stone-200/80 shadow-sm flex items-start gap-3">
                        <span class="w-8 h-8 rounded-xl bg-[#0A4D44]/10 text-[#0A4D44] font-black flex items-center justify-center text-sm shrink-0">✓</span>
                        <div>
                            <h4 class="font-black text-xs text-[#1A1D20] dark:text-[#1A1D20]">{{ $isEn ? '24/7 Bookings' : 'Rezervime 24/7' }}</h4>
                            <p class="text-[11px] text-slate-600 dark:text-slate-600 font-extrabold">{{ $isEn ? 'Clients book on their own' : 'Klientët tuaj rezervojnë vetë' }}</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-white dark:bg-white border border-stone-200/80 shadow-sm flex items-start gap-3">
                        <span class="w-8 h-8 rounded-xl bg-[#0A4D44]/10 text-[#0A4D44] font-black flex items-center justify-center text-sm shrink-0">✓</span>
                        <div>
                            <h4 class="font-black text-xs text-[#1A1D20] dark:text-[#1A1D20]">{{ $isEn ? 'SMS Notifications' : 'SMS Notifikime' }}</h4>
                            <p class="text-[11px] text-slate-600 dark:text-slate-600 font-extrabold">{{ $isEn ? 'Confirmations & SMS reminders' : 'Konfirmime & rikujtesa me SMS' }}</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-white dark:bg-white border border-stone-200/80 shadow-sm flex items-start gap-3">
                        <span class="w-8 h-8 rounded-xl bg-[#0A4D44]/10 text-[#0A4D44] font-black flex items-center justify-center text-sm shrink-0">✓</span>
                        <div>
                            <h4 class="font-black text-xs text-[#1A1D20] dark:text-[#1A1D20]">{{ $isEn ? 'No Credit Card' : 'Pa Kartë Krediti' }}</h4>
                            <p class="text-[11px] text-slate-600 dark:text-slate-600 font-extrabold">{{ $isEn ? 'Start risk-free today' : 'Startoni pa asnjë risk apo parapagesë' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Teaser Card -->
                <div class="p-6 rounded-[2rem] bg-white dark:bg-white border border-stone-200/80 shadow-xl relative hidden sm:block">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-black uppercase tracking-wider text-[#7C5CFC]">
                            {{ $isEn ? 'STATISTICS & GROWTH' : 'Statistikat & Zgjerimi' }}
                        </span>
                        <span class="text-xs px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-extrabold">
                            {{ $isEn ? '98.8% Satisfaction' : '98.8% Kënaqësi' }}
                        </span>
                    </div>

                    <div class="p-4 rounded-2xl bg-[#0A4D44] text-white text-xs font-medium leading-relaxed flex items-center gap-3">
                        <span class="text-2xl">💡</span>
                        <span>
                            {{ $isEn
                                ? '"Clients book 24/7 on their own. Your page works even while you are working or resting."'
                                : '"Klientët rezervojnë vetë 24/7. Faqja juaj punon edhe ndërsa ju jeni në punë apo pushim."' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right Column - Wizard Card Form -->
            <div class="lg:col-span-6">
                <div class="w-full p-8 sm:p-10 rounded-[2.5rem] bg-white dark:bg-white border border-stone-200/80 shadow-2xl relative overflow-hidden">

                    @if($step === 1)
                        <!-- Step 1 Form -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-black uppercase text-[#7C5CFC] tracking-wider">
                                    {{ $isEn ? 'STEP 1 OF 3' : 'HAPI 1 NGA 3' }}
                                </span>
                                <span class="text-xs font-extrabold text-slate-600 dark:text-slate-600">
                                    {{ $isEn ? 'Salon Information' : 'Informacioni i Sallonit' }}
                                </span>
                            </div>

                            <!-- Progress Bar -->
                            <div class="w-full h-2 bg-slate-100 rounded-full mb-8 overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-[#7C5CFC] to-[#6366F1] w-1/3 transition-all duration-300"></div>
                            </div>

                            <form wire:submit.prevent="nextStep" class="space-y-6">
                                <div>
                                    <label class="block text-xs font-extrabold text-[#1A1D20] dark:text-[#1A1D20] uppercase tracking-wider mb-2">
                                        {{ $isEn ? 'Salon Name *' : 'Emri i sallonit *' }}
                                    </label>
                                    <input type="text" wire:model.live="salonName" placeholder="p.sh: Elegance Beauty Salon"
                                           class="w-full px-5 py-4 rounded-2xl bg-[#FAF8F2] dark:bg-[#FAF8F2] border border-slate-300 text-[#1A1D20] dark:text-[#1A1D20] text-sm font-bold focus:border-[#7C5CFC] focus:ring-2 focus:ring-[#7C5CFC]/20 focus:outline-none transition">
                                    @error('salonName') <p class="text-xs text-rose-500 mt-1.5 font-bold">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-extrabold text-[#1A1D20] dark:text-[#1A1D20] uppercase tracking-wider mb-2">
                                        {{ $isEn ? 'Web Address Link *' : 'Adresa e faqeve *' }}
                                    </label>
                                    <div class="flex items-center rounded-2xl bg-[#FAF8F2] dark:bg-[#FAF8F2] border border-slate-300 overflow-hidden px-5 py-4 focus-within:border-[#7C5CFC] focus-within:ring-2 focus-within:ring-[#7C5CFC]/20 transition">
                                        <span class="text-xs text-slate-600 font-extrabold mr-1">app.e4protech.com/s/</span>
                                        <input type="text" wire:model="salonSlug" placeholder="elegance-beauty"
                                               class="w-full bg-transparent text-[#1A1D20] dark:text-[#1A1D20] text-sm font-bold focus:outline-none">
                                    </div>
                                    @error('salonSlug') <p class="text-xs text-rose-500 mt-1.5 font-bold">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-extrabold text-[#1A1D20] dark:text-[#1A1D20] uppercase tracking-wider mb-2">
                                        {{ $isEn ? 'Business Type' : 'Lloji i biznesit' }}
                                    </label>
                                    <select wire:model="businessType"
                                            class="w-full px-5 py-4 rounded-2xl bg-[#FAF8F2] dark:bg-[#FAF8F2] border border-slate-300 text-[#1A1D20] dark:text-[#1A1D20] text-sm font-bold focus:border-[#7C5CFC] focus:ring-2 focus:ring-[#7C5CFC]/20 focus:outline-none">
                                        <option value="barber">💈 Barber Shop / Sallon Qethje</option>
                                        <option value="beauty">💇‍♀️ Beauty Salon / Parukeri</option>
                                        <option value="nails">💅 Nail Studio / Qendër Thonjsh</option>
                                        <option value="spa">🌸 Spa &amp; Massage</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-extrabold text-[#1A1D20] dark:text-[#1A1D20] uppercase tracking-wider mb-2">
                                        {{ $isEn ? 'Official Currency' : 'Monedha Zyrtare' }} *
                                    </label>
                                    <select wire:model="currency"
                                            class="w-full px-5 py-4 rounded-2xl bg-[#FAF8F2] dark:bg-[#FAF8F2] border border-slate-300 text-[#1A1D20] dark:text-[#1A1D20] text-sm font-bold focus:border-[#7C5CFC] focus:ring-2 focus:ring-[#7C5CFC]/20 focus:outline-none">
                                        <option value="Lekë">🇦🇱 Lekë (ALL)</option>
                                        <option value="€">🇪🇺 Euro (€)</option>
                                        <option value="$">🇺🇸 US Dollar ($)</option>
                                        <option value="CHF">🇨🇭 Swiss Franc (CHF)</option>
                                        <option value="£">🇬🇧 British Pound (£)</option>
                                    </select>
                                </div>

                                <button type="submit"
                                        class="w-full py-4 rounded-full font-black text-sm text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-xl shadow-[#7C5CFC]/25 transition transform active:scale-95 flex items-center justify-center gap-2">
                                    <span>{{ $isEn ? 'Continue' : 'Vazhdo' }}</span>
                                    <span class="text-lg">↗</span>
                                </button>
                            </form>
                        </div>

                    @elseif($step === 2)
                        <!-- Step 2 Form -->
                        <div>
                            <input type="hidden" wire:model="salonName">
                            <input type="hidden" wire:model="salonSlug">
                            <input type="hidden" wire:model="businessType">

                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-black uppercase text-[#7C5CFC] tracking-wider">
                                    {{ $isEn ? 'STEP 2 OF 3' : 'HAPI 2 NGA 3' }}
                                </span>
                                <span class="text-xs font-extrabold text-slate-600 dark:text-slate-600">
                                    {{ $isEn ? 'Owner Details' : 'Të Dhënat e Pronarit' }}
                                </span>
                            </div>

                            <!-- Progress Bar -->
                            <div class="w-full h-2 bg-slate-100 rounded-full mb-8 overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-[#7C5CFC] to-[#6366F1] w-2/3 transition-all duration-300"></div>
                            </div>

                            <form wire:submit.prevent="nextStep" class="space-y-4">
                                <div>
                                    <label class="block text-xs font-extrabold text-[#1A1D20] dark:text-[#1A1D20] uppercase tracking-wider mb-2">
                                        {{ $isEn ? 'Your Name & Surname *' : 'Emri & Mbiemri Juaj *' }}
                                    </label>
                                    <input type="text" wire:model="ownerName" placeholder="p.sh: Albano Hoxha"
                                           class="w-full px-5 py-3.5 rounded-2xl bg-[#FAF8F2] dark:bg-[#FAF8F2] border border-slate-300 text-[#1A1D20] dark:text-[#1A1D20] text-sm font-bold focus:border-[#7C5CFC] focus:ring-2 focus:ring-[#7C5CFC]/20 focus:outline-none transition">
                                    @error('ownerName') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-extrabold text-[#1A1D20] dark:text-[#1A1D20] uppercase tracking-wider mb-2">
                                        {{ $isEn ? 'Phone Number *' : 'Numri i Telefonit *' }}
                                    </label>
                                    <input type="text" wire:model="ownerPhone" placeholder="p.sh: +355691234567"
                                           class="w-full px-5 py-3.5 rounded-2xl bg-[#FAF8F2] dark:bg-[#FAF8F2] border border-slate-300 text-[#1A1D20] dark:text-[#1A1D20] text-sm font-bold focus:border-[#7C5CFC] focus:ring-2 focus:ring-[#7C5CFC]/20 focus:outline-none transition">
                                    @error('ownerPhone') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-extrabold text-[#1A1D20] dark:text-[#1A1D20] uppercase tracking-wider mb-2">
                                        {{ $isEn ? 'Email Address *' : 'Adresa e E-mailit *' }}
                                    </label>
                                    <input type="email" wire:model="email" placeholder="p.sh: pronari@salloni.al"
                                           class="w-full px-5 py-3.5 rounded-2xl bg-[#FAF8F2] dark:bg-[#FAF8F2] border border-slate-300 text-[#1A1D20] dark:text-[#1A1D20] text-sm font-bold focus:border-[#7C5CFC] focus:ring-2 focus:ring-[#7C5CFC]/20 focus:outline-none transition">
                                    @error('email') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-extrabold text-[#1A1D20] dark:text-[#1A1D20] uppercase tracking-wider mb-2">
                                        {{ $isEn ? 'Password *' : 'Fjalëkalimi *' }}
                                    </label>
                                    <input type="password" wire:model="password" placeholder="••••••••"
                                           class="w-full px-5 py-3.5 rounded-2xl bg-[#FAF8F2] dark:bg-[#FAF8F2] border border-slate-300 text-[#1A1D20] dark:text-[#1A1D20] text-sm font-bold focus:border-[#7C5CFC] focus:ring-2 focus:ring-[#7C5CFC]/20 focus:outline-none transition">
                                    @error('password') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                                </div>

                                <div class="flex gap-3 pt-2">
                                    <button type="button" wire:click="previousStep"
                                            class="w-1/3 py-3.5 rounded-full font-extrabold text-xs text-[#1A1D20] dark:text-[#1A1D20] bg-slate-100 hover:bg-slate-200 transition">
                                        {{ $isEn ? 'Back' : 'Mbrapsht' }}
                                    </button>
                                    <button type="submit" wire:loading.attr="disabled"
                                            class="w-2/3 py-3.5 rounded-full font-black text-xs text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-xl shadow-[#7C5CFC]/20 transition transform active:scale-95 flex items-center justify-center gap-2">
                                        <span wire:loading.remove>{{ $isEn ? 'Create My Salon 🚀' : 'Krijo Sallonin Tim 🚀' }}</span>
                                        <span wire:loading>{{ $isEn ? 'Creating...' : 'Duke krijuar...' }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                    @elseif($step === 3)
                        <!-- Step 3 Email Verification Notice -->
                        <div class="text-center py-6">
                            <div class="w-16 h-16 rounded-3xl bg-[#7C5CFC]/10 border border-[#7C5CFC]/30 text-[#7C5CFC] flex items-center justify-center mx-auto mb-6 text-3xl font-black shadow-lg">
                                📩
                            </div>
                            <h2 class="text-2xl font-black text-[#1A1D20] dark:text-[#1A1D20] mb-2">
                                {{ $isEn ? 'Check your email!' : 'Kontrolloni emailin tuaj!' }}
                            </h2>
                            <p class="text-xs text-slate-700 dark:text-slate-700 leading-relaxed max-w-sm mx-auto mb-6 font-bold">
                                {!! $isEn
                                    ? 'We sent a verification link to <strong class="text-[#7C5CFC] font-bold">'.$email.'</strong>.<br>Click the link to activate your account &amp; salon, then login to open your panel.'
                                    : 'Dërguam një lidhje konfirmimi te <strong class="text-[#7C5CFC] font-bold">'.$email.'</strong>.<br>Klikojeni lidhjen për të aktivizuar llogarinë dhe sallonin tuaj, pastaj hyni për të hapur panelin tuaj.' !!}
                            </p>

                            <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-800 text-xs font-bold text-left mb-6">
                                💡 {{ $isEn ? 'Don\'t see the email? Please check your Spam / Junk folder.' : 'Nuk e shihni emailin? Ju lutemi kontrolloni edhe dosjen e Spam / Junk.' }}
                            </div>

                            <a href="{{ route('login') }}"
                               class="w-full py-4 rounded-full font-black text-sm text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-xl shadow-[#7C5CFC]/20 transition block text-center">
                                {{ $isEn ? 'Go to Login ↗' : 'Shko te Hyrja ↗' }}
                            </a>
                        </div>
                    @endif

                </div>
            </div>

        </div>

    </main>

    <!-- Footer (E4ProTech Dark Teal `#0A4D44`) -->
    <footer class="py-10 bg-[#0A4D44] text-white mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-3 text-teal-100 font-bold">
                <div class="w-8 h-8 rounded-xl bg-white text-[#0A4D44] font-black flex items-center justify-center text-sm">
                    E4
                </div>
                <p class="text-xs text-teal-100 font-medium">
                    © {{ date('Y') }} E4ProTech Engine — Managed IT &amp; SaaS Solutions
                </p>
            </div>

            <div class="flex gap-6 text-xs text-teal-100 font-bold">
                <a href="/" class="hover:text-amber-300 transition">{{ $isEn ? 'Home' : 'Kreu' }}</a>
                <a href="/#how-it-works" class="hover:text-amber-300 transition">{{ $isEn ? 'How It Works' : 'Si Funksionon' }}</a>
                <a href="/#services" class="hover:text-amber-300 transition">{{ $isEn ? 'Services' : 'Shërbimet' }}</a>
                <a href="{{ route('dashboard') }}" class="hover:text-amber-300 transition">{{ $isEn ? 'Admin Panel' : 'Paneli Admin' }}</a>
            </div>
        </div>
    </footer>

</div>
