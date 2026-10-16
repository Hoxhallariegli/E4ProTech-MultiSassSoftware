@php
    $isEn = app()->getLocale() === 'en';
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <x-h1>{{ $isEn ? 'AI System Copilot' : 'Asistenti Inteligjent AI' }}</x-h1>
            <x-short-description class="dark:text-gray-400">
                {{ $isEn
                    ? 'Bilingual AI Operational Assistant. Converse in natural language to manage bookings, services, staff, and analytics.'
                    : 'Bisedoni në gjuhë natyrale me AI për të menaxhuar rezervimet, shërbimet, stafin dhe statistikat në kohë reale.' }}
            </x-short-description>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: AI Conversation Stream & Chat Box -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Chat Stream Card -->
            <div class="card p-6 bg-white dark:bg-gray-800 shadow-sm rounded-2xl border border-stone-200/80 dark:border-gray-700 space-y-6">

                <!-- Chat Messages Stream -->
                <div x-data="{
                        scrollToBottom() {
                            $nextTick(() => {
                                $el.scrollTop = $el.scrollHeight;
                            });
                        }
                    }"
                    x-init="scrollToBottom()"
                    x-on:scroll-bottom.window="scrollToBottom()"
                    class="space-y-6 min-h-[300px] max-h-[600px] overflow-y-auto p-2 scrollbar-thin">

                    <!-- Load More Previous Conversations Button -->
                    @if($totalHistoryCount > count($history))
                        <div class="text-center py-2">
                            <button type="button" wire:click="loadMoreHistory"
                                    class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-stone-100 dark:bg-gray-700 hover:bg-stone-200 dark:hover:bg-gray-600 text-xs font-bold text-gray-800 dark:text-gray-200 transition shadow-xs">
                                <x-heroicon-o-arrow-up class="w-3.5 h-3.5" />
                                <span>{{ $isEn ? 'Load More Previous Conversations' : 'Ngarko Më Parë Biseda të Vjetra' }} ({{ $totalHistoryCount - count($history) }})</span>
                            </button>
                        </div>
                    @endif

                    <!-- Welcome AI Message -->
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-[#1A1D20] text-white flex items-center justify-center font-black shrink-0 shadow-sm">
                            <x-heroicon-o-cpu-chip class="w-5 h-5 text-white" />
                        </div>
                        <div class="max-w-2xl p-5 rounded-2xl bg-stone-50 dark:bg-gray-900 border border-stone-200/80 dark:border-gray-700 space-y-2 text-xs font-semibold text-gray-800 dark:text-gray-200 leading-relaxed">
                            <p class="font-extrabold text-sm text-gray-900 dark:text-white">
                                {{ $isEn ? 'Hello ' . auth()->user()->name . '! How can I help you today?' : 'Përshëndetje ' . auth()->user()->name . '! Si mund t\'ju ndihmoj sot?' }}
                            </p>
                            <p>
                                {{ $isEn
                                    ? 'I can create or manage services, walk-in bookings without SMS, staff, and run live analytics for your salon. Try an example instruction below.'
                                    : 'Mund të krijoj apo menaxhoj shërbimet, rezervimet walk-in pa SMS, stafin dhe të nxjerr statistikat e sallonit tuaj. Provojeni me një udhëzim më poshtë.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Conversation History Turns -->
                    @foreach($history as $log)
                        <!-- User Prompt Bubble -->
                        <div class="flex justify-end">
                            <div class="max-w-xl p-4 rounded-2xl bg-[#1A1D20] text-white shadow-sm font-semibold text-xs leading-relaxed space-y-1">
                                <div class="flex items-center justify-between gap-4 text-[10px] opacity-75 border-b border-white/20 pb-1">
                                    <span>{{ auth()->user()->name }}</span>
                                    <span>{{ $log['time'] }}</span>
                                </div>
                                <p class="pt-1 font-bold">"{{ $log['user_prompt'] }}"</p>
                            </div>
                        </div>

                        <!-- AI Response Turn -->
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-[#1A1D20] text-white flex items-center justify-center font-black shrink-0 shadow-sm">
                                <x-heroicon-o-cpu-chip class="w-5 h-5 text-white" />
                            </div>
                            <div class="max-w-2xl w-full space-y-3">
                                @if($log['type'] === 'success' && isset($log['results']))
                                    @foreach($log['results'] as $res)
                                        <div class="p-5 rounded-2xl bg-stone-50 dark:bg-gray-900 border border-stone-200/80 dark:border-gray-700 space-y-3">
                                            <div class="flex items-center justify-between">
                                                <h4 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                                                    <span>{{ $res['title'] }}</span>
                                                </h4>
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-[#1A1D20] text-white">
                                                    {{ $res['badge'] }}
                                                </span>
                                            </div>

                                            <div class="space-y-1.5">
                                                @foreach($res['items'] as $itemStr)
                                                    <div class="p-2.5 rounded-xl bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2 font-mono">
                                                        <span class="text-emerald-600 font-black">✓</span>
                                                        <span>{{ $itemStr }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                @elseif($log['type'] === 'info')
                                    <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-xs font-bold">
                                        {{ $log['message'] }}
                                    </div>
                                @else
                                    <div class="p-4 rounded-xl bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 text-xs font-bold">
                                        {{ $log['message'] }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <!-- CONFIRMATION CARD / DIALOG (Inside Conversation Stream at the BOTTOM) -->
                    @if($showConfirmation)
                        <!-- User Pending Prompt Bubble -->
                        <div class="flex justify-end">
                            <div class="max-w-xl p-4 rounded-2xl bg-[#1A1D20] text-white shadow-sm font-semibold text-xs leading-relaxed space-y-1">
                                <div class="flex items-center justify-between gap-4 text-[10px] opacity-75 border-b border-white/20 pb-1">
                                    <span>{{ auth()->user()->name }}</span>
                                    <span>{{ now()->format('H:i:s') }}</span>
                                </div>
                                <p class="pt-1 font-bold">"{{ $pendingPrompt }}"</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                                <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                            </div>
                            <div class="max-w-2xl w-full p-6 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 space-y-4">
                                <div>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500 text-white">
                                        {{ $isEn ? 'Confirmation Required' : 'Kërkohet Konfirmimi Juaj' }}
                                    </span>
                                    <h3 class="text-base font-black text-gray-900 dark:text-white mt-2">
                                        {{ $isEn ? 'Hello ' . auth()->user()->name . '! Would you like to proceed with these actions?' : 'Përshëndetje ' . auth()->user()->name . '! A dëshironi të procedojmë me kryerjen e këtyre veprimeve?' }}
                                    </h3>
                                    <p class="text-xs font-bold text-gray-600 dark:text-gray-300 mt-1">
                                        {{ $isEn ? 'AI interpreted:' : 'AI interpretoi udhëzimin tuaj:' }} <span class="text-[#1A1D20] dark:text-white font-extrabold">"{{ $pendingPrompt }}"</span>
                                    </p>
                                </div>

                                <div class="space-y-3">
                                    @foreach($previewItems as $preview)
                                        <div class="p-4 rounded-xl bg-white dark:bg-gray-800 border border-amber-200 dark:border-amber-900/50 space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2">
                                                    <span>{{ $preview['title'] }}</span>
                                                </span>
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-[#1A1D20] text-white">
                                                    {{ $preview['badge'] }}
                                                </span>
                                            </div>

                                            <div class="space-y-1">
                                                @foreach($preview['items'] as $pItem)
                                                    <div class="text-xs font-bold text-gray-800 dark:text-gray-200 font-mono flex items-center gap-2">
                                                        <span class="text-emerald-600 font-black">➜</span>
                                                        <span>{{ $pItem }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-2">
                                    <button type="button" wire:click="cancelExecution"
                                            class="w-full sm:w-auto px-5 py-2.5 rounded-full font-extrabold text-xs text-stone-800 dark:text-gray-200 bg-stone-200 dark:bg-gray-700 hover:bg-stone-300 transition">
                                        {{ $isEn ? 'No, Cancel Request' : 'Jo, Anulo Kërkesën' }}
                                    </button>

                                    <button type="button" wire:click="confirmExecution"
                                            class="w-full sm:w-auto px-6 py-2.5 rounded-full font-black text-xs text-white bg-[#1A1D20] hover:bg-black shadow-md transition">
                                        {{ $isEn ? 'Yes, Confirm & Execute' : 'Po, Konfirmo & Ekzekuto' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>

                <!-- Smooth Auto-expanding Chat Input Bar -->
                <form wire:submit.prevent="process" class="pt-4 border-t border-stone-200/80 dark:border-gray-700 space-y-3">
                    <div class="relative">
                        <textarea
                            wire:model="prompt"
                            x-data="{
                                autoResize() {
                                    $el.style.height = 'auto';
                                    $el.style.height = ($el.scrollHeight) + 'px';
                                }
                            }"
                            x-init="
                                autoResize();
                                $watch('$wire.prompt', () => $nextTick(() => autoResize()));
                            "
                            @input="autoResize()"
                            @keydown.enter.exact.prevent="$wire.process()"
                            rows="1"
                            style="overflow: hidden; resize: none;"
                            class="w-full p-4 pr-32 text-sm font-semibold bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-slate-900/10 transition-all outline-none dark:text-white placeholder:text-gray-400 min-h-[56px] leading-relaxed"
                            placeholder="{{ $isEn
                                ? 'Write your command (e.g. Delete 5 recent bookings, or Create 5 services...)'
                                : 'Shkruani udhëzimin tuaj (p.sh: Më fshi 5 rezervimet e fundit, apo Më krijo 5 shërbime...)' }}"
                        ></textarea>

                        <div class="absolute right-3 bottom-2.5 flex items-center gap-2">
                            <button type="submit" wire:loading.attr="disabled"
                                    class="px-6 py-2.5 rounded-full font-black text-xs text-white bg-[#1A1D20] hover:bg-black shadow-md transition flex items-center gap-2">
                                <span wire:loading.remove>{{ $isEn ? 'Send' : 'Ekzekuto' }}</span>
                                <span wire:loading>...</span>
                            </button>
                        </div>
                    </div>
                </form>

            </div>

        </div>

        <!-- Right Column: Unified Quick Guide & Examples Card -->
        <div class="lg:col-span-4">
            <div class="card p-6 sm:p-8 bg-white dark:bg-gray-800 shadow-sm rounded-2xl border border-stone-200/80 dark:border-gray-700 space-y-6">

                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-stone-100 text-stone-800 dark:bg-gray-700 dark:text-gray-200 mb-2">
                        <x-heroicon-o-light-bulb class="w-3.5 h-3.5 text-stone-700 dark:text-gray-300" />
                        <span>{{ $isEn ? 'Quick Prompts' : 'Shembuj & Udhëzime' }}</span>
                    </div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">
                        {{ $isEn ? 'Quick Examples' : 'Udhëzime të Shpejta' }}
                    </h3>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1">
                        {{ $isEn ? 'Click any example to insert it into the chat input box.' : 'Klikoni mbi ndonjë shembull për ta futur te fusha e shkrimit.' }}
                    </p>
                </div>

                <div class="space-y-4">
                    <!-- Example 1: Delete Recent -->
                    <div class="p-4 rounded-xl bg-stone-50 dark:bg-gray-900/50 border border-stone-200/80 dark:border-gray-700/80 space-y-3">
                        <div class="flex items-center gap-2 font-black text-xs text-gray-900 dark:text-white">
                            <x-heroicon-o-trash class="w-4 h-4 text-red-500" />
                            <span>{{ $isEn ? '1. Delete Recent Bookings' : '1. Fshi Rezervimet e Fundit' }}</span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-300 font-medium leading-relaxed">
                            "{{ $isEn ? 'Delete the 5 recent bookings that you just created' : 'Më fshi 5 rezervimet e fundit që sapo krijove' }}"
                        </p>
                        <button type="button"
                                wire:click="$set('prompt', 'Më fshi 5 rezervimet e fundit që sapo krijove')"
                                class="w-full py-2.5 rounded-full bg-[#1A1D20] text-white hover:bg-black font-black text-xs transition shadow-sm">
                            {{ $isEn ? 'Use This Instruction ↗' : 'Përdor Këtë Udhëzim ↗' }}
                        </button>
                    </div>

                    <!-- Example 2: Services -->
                    <div class="p-4 rounded-xl bg-stone-50 dark:bg-gray-900/50 border border-stone-200/80 dark:border-gray-700/80 space-y-3">
                        <div class="flex items-center gap-2 font-black text-xs text-gray-900 dark:text-white">
                            <x-heroicon-o-pencil-square class="w-4 h-4 text-blue-500" />
                            <span>{{ $isEn ? '2. Create Services' : '2. Krijim Shërbimesh' }}</span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-300 font-medium leading-relaxed">
                            "{{ $isEn ? 'Create 5 services: Haircut 1000Lek 30min, Beard 500Lek 15min, Wash 300Lek 10min, Styling 800Lek 20min, Treatment 1500Lek 40min' : 'Më krijo 5 shërbime: Prerje 1000Lekë 30min, Mjekërr 500Lekë 15min, Larje 300Lekë 10min, Modelim 800Lekë 20min, Trajtim 1500Lekë 40min' }}"
                        </p>
                        <button type="button"
                                wire:click="$set('prompt', 'Më krijo 5 shërbime: Prerje 1000Lekë 30min, Mjekërr 500Lekë 15min, Larje 300Lekë 10min, Modelim 800Lekë 20min, Trajtim 1500Lekë 40min')"
                                class="w-full py-2.5 rounded-full bg-[#1A1D20] text-white hover:bg-black font-black text-xs transition shadow-sm">
                            {{ $isEn ? 'Use This Instruction ↗' : 'Përdor Këtë Udhëzim ↗' }}
                        </button>
                    </div>

                    <!-- Example 3: Walk-In Bookings -->
                    <div class="p-4 rounded-xl bg-stone-50 dark:bg-gray-900/50 border border-stone-200/80 dark:border-gray-700/80 space-y-3">
                        <div class="flex items-center gap-2 font-black text-xs text-gray-900 dark:text-white">
                            <x-heroicon-o-chart-bar class="w-4 h-4 text-emerald-500" />
                            <span>{{ $isEn ? '3. Walk-In Bookings (No SMS)' : '3. Rezervime Walk-In (Pa SMS)' }}</span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-300 font-medium leading-relaxed">
                            "{{ $isEn ? 'Create 5 walk-in bookings with random customers for today from 13:00 to 17:00 without sending SMS' : 'Më krijo 5 rezervime walk-in me klientë rastësorë për sot nga ora 13:00 deri 17:00 pa dërguar mesazhe SMS' }}"
                        </p>
                        <button type="button"
                                wire:click="$set('prompt', 'Më krijo 5 rezervime walk-in me klientë rastësorë për sot nga ora 13:00 deri 17:00 pa dërguar mesazhe SMS')"
                                class="w-full py-2.5 rounded-full bg-[#1A1D20] text-white hover:bg-black font-black text-xs transition shadow-sm">
                            {{ $isEn ? 'Use This Instruction ↗' : 'Përdor Këtë Udhëzim ↗' }}
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
