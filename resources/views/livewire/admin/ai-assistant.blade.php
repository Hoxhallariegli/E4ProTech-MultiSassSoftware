<div class="space-y-8 p-4 sm:p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] border border-stone-200 dark:border-gray-700 shadow-sm">
        <div>
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-black bg-purple-500/10 text-[#7C5CFC] mb-2">
                <span>🤖</span> Operational Business Copilot
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                AI System Assistant
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">
                Jepni udhëzime në gjuhë natyrale dhe AI do të ekzekutojë automatikisht veprimet te baza e të dhënave dhe te **Audit Trails**!
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.settings.audit-trails.index') }}" target="_blank"
               class="px-6 py-3.5 rounded-2xl bg-stone-100 hover:bg-stone-200 dark:bg-gray-700 text-gray-900 dark:text-white font-black text-xs transition flex items-center gap-2">
                <span>Shiko Audit Trails</span>
                <span>↗</span>
            </a>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- Left Input & History Column -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Prompt Input Box -->
            <div class="bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] border border-stone-200 dark:border-gray-700 shadow-sm space-y-4">
                <form wire:submit.prevent="process" class="space-y-4">
                    <label class="block text-xs font-black uppercase tracking-wider text-[#7C5CFC]">
                        💬 Shkruani Udhëzimin tuaj për AI Assistant
                    </label>

                    <textarea
                        wire:model="prompt"
                        class="w-full h-36 p-5 text-sm font-bold bg-[#FAF8F2] dark:bg-gray-900 border border-stone-200 dark:border-gray-700 rounded-3xl shadow-inner focus:ring-2 focus:ring-[#7C5CFC]/30 transition outline-none dark:text-white"
                        placeholder="p.sh: Më krijo 5 shërbime: Prerje 1000Lekë 30min, Mjekërr 500Lekë 15min, Larje 300Lekë 10min, Modelim 800Lekë 20min, Trajtim 1500Lekë 40min..."
                    ></textarea>

                    <div class="flex items-center justify-between gap-4 pt-2">
                        <div class="flex items-center gap-2 text-xs text-gray-400 font-medium">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Ekspert për Rezervime, Shërbime, Staf &amp; Audit Trails</span>
                        </div>

                        <button type="submit" wire:loading.attr="disabled"
                                class="px-8 py-3.5 rounded-full font-black text-xs text-white bg-[#7C5CFC] hover:bg-[#6366F1] shadow-xl shadow-[#7C5CFC]/20 transition transform active:scale-95 flex items-center gap-2">
                            <span wire:loading.remove>Ekzekuto nga AI 🚀</span>
                            <span wire:loading>Duke ekzekutuar kërkesën...</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Execution Results History -->
            @if(count($history) > 0)
                <div class="space-y-6">
                    <h3 class="text-xs font-black uppercase tracking-wider text-gray-400 ml-2">Histori e Ekzekutimeve të AI</h3>

                    @foreach(array_reverse($history) as $log)
                        <div class="bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] border {{ $log['type'] === 'success' ? 'border-emerald-200 dark:border-emerald-900/40' : 'border-rose-200 dark:border-rose-900/40' }} shadow-sm space-y-6">

                            <!-- Prompt Header -->
                            <div class="flex items-start justify-between gap-4 pb-4 border-b border-stone-100 dark:border-gray-700">
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">Komanda e Dërguar:</span>
                                    <p class="text-sm font-extrabold text-gray-900 dark:text-white mt-0.5">"{{ $log['user_prompt'] }}"</p>
                                </div>
                                <span class="text-[10px] font-mono text-gray-400 bg-stone-100 dark:bg-gray-900 px-3 py-1 rounded-full shrink-0">
                                    {{ $log['time'] }}
                                </span>
                            </div>

                            @if($log['type'] === 'success' && isset($log['results']))
                                <div class="space-y-6">
                                    @foreach($log['results'] as $res)
                                        <div class="p-6 rounded-2xl bg-[#FAF8F2] dark:bg-gray-900 border border-stone-200 dark:border-gray-700 space-y-4">
                                            <div class="flex items-center justify-between">
                                                <h4 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                                                    <span>{{ $res['icon'] }}</span>
                                                    <span>{{ $res['title'] }}</span>
                                                </h4>
                                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-[#7C5CFC]/10 text-[#7C5CFC]">
                                                    {{ $res['badge'] }}
                                                </span>
                                            </div>

                                            <div class="space-y-2">
                                                @foreach($res['items'] as $itemStr)
                                                    <div class="p-3 rounded-xl bg-white dark:bg-gray-800 border border-stone-200/60 dark:border-gray-700 text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2 font-mono">
                                                        <span class="text-emerald-500 font-black">✓</span>
                                                        <span>{{ $itemStr }}</span>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="pt-2 text-[11px] text-emerald-700 dark:text-emerald-400 font-bold flex items-center gap-1.5">
                                                <span>📋</span>
                                                <span>{{ $res['audit'] }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs font-bold">
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
            <div class="bg-white dark:bg-gray-800 p-6 rounded-[2.5rem] border border-stone-200 dark:border-gray-700 shadow-sm space-y-3">
                <span class="text-xs font-black uppercase text-[#7C5CFC] tracking-wider block">💡 Shembull: Krijim Shërbimesh</span>
                <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed font-medium">
                    "Më krijo 5 shërbime: Prerje 1000Lekë 30min, Mjekërr 500Lekë 15min, Larje 300Lekë 10min, Modelim 800Lekë 20min, Trajtim 1500Lekë 40min"
                </p>
                <button type="button"
                        wire:click="$set('prompt', 'Më krijo 5 shërbime: Prerje 1000Lekë 30min, Mjekërr 500Lekë 15min, Larje 300Lekë 10min, Modelim 800Lekë 20min, Trajtim 1500Lekë 40min')"
                        class="w-full py-2.5 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-[#7C5CFC] font-black text-xs hover:bg-purple-100 transition">
                    Përdor Këtë Udhëzim ↗
                </button>
            </div>

            <!-- Examples Preset 2: Walk-In Bookings without SMS -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-[2.5rem] border border-stone-200 dark:border-gray-700 shadow-sm space-y-3">
                <span class="text-xs font-black uppercase text-amber-600 tracking-wider block">📅 Shembull: Rezervime Walk-In (Pa SMS)</span>
                <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed font-medium">
                    "Më krijo 5 rezervime walk-in me klientë rastësorë për sot nga ora 13:00 deri 17:00 pa dërguar mesazhe SMS"
                </p>
                <button type="button"
                        wire:click="$set('prompt', 'Më krijo 5 rezervime walk-in me klientë rastësorë për sot nga ora 13:00 deri 17:00 pa dërguar mesazhe SMS')"
                        class="w-full py-2.5 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 font-black text-xs hover:bg-amber-100 transition">
                    Përdor Këtë Udhëzim ↗
                </button>
            </div>

            <!-- Examples Preset 3: Staff & Customers -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-[2.5rem] border border-stone-200 dark:border-gray-700 shadow-sm space-y-3">
                <span class="text-xs font-black uppercase text-emerald-600 tracking-wider block">✂️ Shembull: Shtim Stafi / Klientësh</span>
                <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed font-medium">
                    "Më shto 3 pjesëtarë stafi: Ardit Berber, Klara Stylist, Egli Hoxha"
                </p>
                <button type="button"
                        wire:click="$set('prompt', 'Më shto 3 pjesëtarë stafi: Ardit Berber, Klara Stylist, Egli Hoxha')"
                        class="w-full py-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-black text-xs hover:bg-emerald-100 transition">
                    Përdor Këtë Udhëzim ↗
                </button>
            </div>

            <div class="p-6 rounded-[2.5rem] bg-[#0A4D44] text-white space-y-3 shadow-lg">
                <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-amber-300">
                    <span>💡</span> Integrimi i Plotë me Audit Trails
                </div>
                <p class="text-xs text-teal-100 leading-relaxed font-medium">
                    Çdo veprim i ekzekutuar nga AI kalon automatikisht nëpërmjet modeleve të sistemit dhe regjistrohet menjëherë te <strong>Audit Trails</strong> me përdoruesin dhe kohën e saktë!
                </p>
            </div>

        </div>

    </div>
</div>
