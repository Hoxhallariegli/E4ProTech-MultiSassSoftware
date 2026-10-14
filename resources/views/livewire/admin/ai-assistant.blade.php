@php
    $isEn = app()->getLocale() === 'en';
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <x-h1>🤖 {{ $isEn ? 'AI System Assistant' : 'Asistenti Inteligjent AI' }}</x-h1>
            <x-short-description class="dark:text-gray-300">
                {{ $isEn
                    ? 'Give instructions in natural language. The AI will automatically execute actions in the database and Audit Trails.'
                    : 'Jepni udhëzime në gjuhë natyrale dhe AI do të ekzekutojë automatikisht veprimet te baza e të dhënave dhe te Audit Trails.' }}
            </x-short-description>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.settings.audit-trails.index') }}" target="_blank"
               class="px-5 py-2.5 rounded-2xl bg-[#1A1D20] text-white hover:bg-black font-black text-xs transition shadow-sm flex items-center gap-2">
                <span>{{ $isEn ? 'View Audit Trails' : 'Shiko Audit Trails' }}</span>
                <span>↗</span>
            </a>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Input & History Column -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Prompt Input Card -->
            <div class="card p-6 sm:p-8 bg-white dark:bg-gray-800 shadow rounded-2xl border border-gray-200 dark:border-gray-700 space-y-4">
                <form wire:submit.prevent="process" class="space-y-4">
                    <label class="block text-xs font-black uppercase tracking-wider text-[#7C5CFC] dark:text-purple-400">
                        💬 {{ $isEn ? 'Write Your Instruction for AI Assistant' : 'Shkruani Udhëzimin tuaj për AI Assistant' }}
                    </label>

                    <textarea
                        wire:model="prompt"
                        class="w-full h-36 p-4 text-sm font-extrabold bg-stone-50 dark:bg-gray-900 border border-stone-300 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-[#7C5CFC]/30 transition outline-none text-gray-900 dark:text-white placeholder:text-gray-400"
                        placeholder="{{ $isEn
                            ? 'e.g: Create 5 services: Haircut 1000Lek 30min, Beard 500Lek 15min, Wash 300Lek 10min...'
                            : 'p.sh: Më krijo 5 shërbime: Prerje 1000Lekë 30min, Mjekërr 500Lekë 15min, Larje 300Lekë 10min...' }}"
                    ></textarea>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pt-2">
                        <div class="flex items-center gap-2 text-xs text-gray-800 dark:text-gray-200 font-extrabold">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>{{ $isEn ? 'Expert for Bookings, Services, Staff & Audit Trails' : 'Ekspert për Rezervime, Shërbime, Staf & Audit Trails' }}</span>
                        </div>

                        <button type="submit" wire:loading.attr="disabled"
                                class="w-full sm:w-auto px-8 py-3.5 rounded-full font-black text-xs text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-lg shadow-[#7C5CFC]/25 transition transform active:scale-95 flex items-center justify-center gap-2">
                            <span wire:loading.remove>{{ $isEn ? 'Execute with AI 🚀' : 'Ekzekuto nga AI 🚀' }}</span>
                            <span wire:loading>{{ $isEn ? 'Processing request...' : 'Duke ekzekutuar kërkesën...' }}</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- ANIMATED REAL-TIME PROCESSING STEPS (Shown during wire:loading) -->
            <div wire:loading class="w-full card p-6 bg-gradient-to-r from-purple-500/10 via-blue-500/10 to-emerald-500/10 border-2 border-[#7C5CFC] rounded-2xl space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-4 h-4 rounded-full bg-[#7C5CFC] animate-ping"></span>
                    <h4 class="text-sm font-black text-gray-900 dark:text-white">
                        {{ $isEn ? 'AI Processing Steps in Progress...' : 'Ecuria e Ekzekutimit nga AI në Kohë Reale...' }}
                    </h4>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs font-extrabold">
                    <div class="p-3 rounded-xl bg-white dark:bg-gray-800 border border-purple-200 dark:border-purple-900 text-purple-700 dark:text-purple-300 flex items-center gap-2 shadow-xs">
                        <span class="animate-spin text-base">⏳</span>
                        <span>{{ $isEn ? '1. Request Received & Analyzed' : '1. Kërkesa u Mor me Sukses' }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-white dark:bg-gray-800 border border-blue-200 dark:border-blue-900 text-blue-700 dark:text-blue-300 flex items-center gap-2 shadow-xs">
                        <span class="animate-pulse text-base">⚙️</span>
                        <span>{{ $isEn ? '2. Inserting Data into DB' : '2. Po Hidhen Të Dhënat në DB' }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-white dark:bg-gray-800 border border-emerald-200 dark:border-emerald-900 text-emerald-700 dark:text-emerald-300 flex items-center gap-2 shadow-xs">
                        <span class="text-base">📋</span>
                        <span>{{ $isEn ? '3. Logging Audit Trails' : '3. Po Regjistrohet te Audit Trails' }}</span>
                    </div>
                </div>
            </div>

            <!-- Execution Results History -->
            @if(count($history) > 0)
                <div class="space-y-4">
                    <h3 class="text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300 ml-1">
                        {{ $isEn ? 'AI Execution History' : 'Histori e Ekzekutimeve të AI' }}
                    </h3>

                    @foreach(array_reverse($history) as $log)
                        <div class="card p-6 bg-white dark:bg-gray-800 shadow rounded-2xl border {{ $log['type'] === 'success' ? 'border-emerald-300 dark:border-emerald-800' : 'border-rose-300 dark:border-rose-800' }} space-y-4">

                            <!-- Prompt Header -->
                            <div class="flex items-start justify-between gap-4 pb-3 border-b border-gray-200 dark:border-gray-700">
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        {{ $isEn ? 'Executed Command:' : 'Komanda e Dërguar:' }}
                                    </span>
                                    <p class="text-sm font-black text-gray-900 dark:text-white mt-0.5">"{{ $log['user_prompt'] }}"</p>
                                </div>
                                <span class="text-[11px] font-mono font-bold text-gray-700 dark:text-gray-300 bg-stone-100 dark:bg-gray-900 px-3 py-1 rounded-lg shrink-0 border border-stone-200 dark:border-gray-700">
                                    {{ $log['time'] }}
                                </span>
                            </div>

                            @if($log['type'] === 'success' && isset($log['results']))
                                <div class="space-y-4">
                                    @foreach($log['results'] as $res)
                                        <div class="p-5 rounded-2xl bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 space-y-3">
                                            <div class="flex items-center justify-between">
                                                <h4 class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-2">
                                                    <span>{{ $res['icon'] }}</span>
                                                    <span>{{ $res['title'] }}</span>
                                                </h4>
                                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-[#7C5CFC] text-white">
                                                    {{ $res['badge'] }}
                                                </span>
                                            </div>

                                            <div class="space-y-2">
                                                @foreach($res['items'] as $itemStr)
                                                    <div class="p-3 rounded-xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 text-xs font-black text-gray-900 dark:text-gray-100 flex items-center gap-2 font-mono">
                                                        <span class="text-emerald-600 font-black text-sm">✓</span>
                                                        <span>{{ $itemStr }}</span>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="pt-1 text-[11px] text-emerald-700 dark:text-emerald-400 font-black flex items-center gap-1.5">
                                                <span>📋</span>
                                                <span>{{ $res['audit'] }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-900/40 text-rose-800 dark:text-rose-200 text-xs font-black border border-rose-200 dark:border-rose-800">
                                    ⚠️ {{ $log['message'] }}
                                </div>
                            @endif

                        </div>
                    @endforeach
                </div>
            @endif

        </div>

        <!-- Right Prompt Presets & Examples Column -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Examples Preset 1: Services -->
            <div class="card p-6 bg-white dark:bg-gray-800 shadow rounded-2xl border border-stone-200 dark:border-gray-700 space-y-3">
                <span class="text-xs font-black uppercase text-blue-700 dark:text-blue-400 tracking-wider block">
                    💡 {{ $isEn ? 'Example: Create Services' : 'Shembull: Krijim Shërbimesh' }}
                </span>
                <p class="text-xs text-gray-900 dark:text-gray-100 leading-relaxed font-extrabold bg-stone-50 dark:bg-gray-900 p-3 rounded-xl border border-stone-200 dark:border-gray-700">
                    "{{ $isEn ? 'Create 5 services: Haircut 1000Lek 30min, Beard 500Lek 15min, Wash 300Lek 10min, Styling 800Lek 20min, Treatment 1500Lek 40min' : 'Më krijo 5 shërbime: Prerje 1000Lekë 30min, Mjekërr 500Lekë 15min, Larje 300Lekë 10min, Modelim 800Lekë 20min, Trajtim 1500Lekë 40min' }}"
                </p>
                <button type="button"
                        wire:click="$set('prompt', 'Më krijo 5 shërbime: Prerje 1000Lekë 30min, Mjekërr 500Lekë 15min, Larje 300Lekë 10min, Modelim 800Lekë 20min, Trajtim 1500Lekë 40min')"
                        class="w-full py-3 rounded-xl bg-[#1A1D20] text-white hover:bg-black font-black text-xs shadow-md transition flex items-center justify-center gap-2">
                    <span>{{ $isEn ? 'Use This Instruction 🚀' : 'Përdor Këtë Udhëzim 🚀' }}</span>
                </button>
            </div>

            <!-- Examples Preset 2: Walk-In Bookings -->
            <div class="card p-6 bg-white dark:bg-gray-800 shadow rounded-2xl border border-stone-200 dark:border-gray-700 space-y-3">
                <span class="text-xs font-black uppercase text-amber-700 dark:text-amber-400 tracking-wider block">
                    📅 {{ $isEn ? 'Example: Walk-In Bookings (No SMS)' : 'Shembull: Rezervime Walk-In (Pa SMS)' }}
                </span>
                <p class="text-xs text-gray-900 dark:text-gray-100 leading-relaxed font-extrabold bg-stone-50 dark:bg-gray-900 p-3 rounded-xl border border-stone-200 dark:border-gray-700">
                    "{{ $isEn ? 'Create 5 walk-in bookings with random customers for today from 13:00 to 17:00 without sending SMS messages' : 'Më krijo 5 rezervime walk-in me klientë rastësorë për sot nga ora 13:00 deri 17:00 pa dërguar mesazhe SMS' }}"
                </p>
                <button type="button"
                        wire:click="$set('prompt', 'Më krijo 5 rezervime walk-in me klientë rastësorë për sot nga ora 13:00 deri 17:00 pa dërguar mesazhe SMS')"
                        class="w-full py-3 rounded-xl bg-[#1A1D20] text-white hover:bg-black font-black text-xs shadow-md transition flex items-center justify-center gap-2">
                    <span>{{ $isEn ? 'Use This Instruction 🚀' : 'Përdor Këtë Udhëzim 🚀' }}</span>
                </button>
            </div>

            <!-- Examples Preset 3: Staff & Customers -->
            <div class="card p-6 bg-white dark:bg-gray-800 shadow rounded-2xl border border-stone-200 dark:border-gray-700 space-y-3">
                <span class="text-xs font-black uppercase text-emerald-700 dark:text-emerald-400 tracking-wider block">
                    ✂️ {{ $isEn ? 'Example: Add Staff / Customers' : 'Shembull: Shtim Stafi / Klientësh' }}
                </span>
                <p class="text-xs text-gray-900 dark:text-gray-100 leading-relaxed font-extrabold bg-stone-50 dark:bg-gray-900 p-3 rounded-xl border border-stone-200 dark:border-gray-700">
                    "{{ $isEn ? 'Add 3 staff members: Ardit Barber, Klara Stylist, Egli Hoxha' : 'Më shto 3 pjesëtarë stafi: Ardit Berber, Klara Stylist, Egli Hoxha' }}"
                </p>
                <button type="button"
                        wire:click="$set('prompt', 'Më shto 3 pjesëtarë stafi: Ardit Berber, Klara Stylist, Egli Hoxha')"
                        class="w-full py-3 rounded-xl bg-[#1A1D20] text-white hover:bg-black font-black text-xs shadow-md transition flex items-center justify-center gap-2">
                    <span>{{ $isEn ? 'Use This Instruction 🚀' : 'Përdor Këtë Udhëzim 🚀' }}</span>
                </button>
            </div>

            <div class="card p-6 bg-[#0A4D44] text-white rounded-2xl space-y-3 shadow-lg border border-[#0A4D44]">
                <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-amber-300">
                    <span>💡</span> {{ $isEn ? 'Full Integration with Audit Trails' : 'Integrimi i Plotë me Audit Trails' }}
                </div>
                <p class="text-xs text-white leading-relaxed font-extrabold">
                    {{ $isEn
                        ? 'Every action executed by AI automatically passes through system models and is recorded in Audit Trails with exact user and timestamp!'
                        : 'Çdo veprim i ekzekutuar nga AI kalon automatikisht nëpërmjet modeleve të sistemit dhe regjistrohet menjëherë te Audit Trails me përdoruesin dhe kohën e saktë!' }}
                </p>
            </div>

        </div>

    </div>
</div>
