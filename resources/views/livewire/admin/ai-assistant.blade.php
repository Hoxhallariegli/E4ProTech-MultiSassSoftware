<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <x-h1>{{ __('ai.AI System Copilot') }}</x-h1>
            <x-short-description class="dark:text-gray-400">
                {{ app()->getLocale() === 'en'
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
                                <span>{{ __('ai.Load More Conversations') }} ({{ $totalHistoryCount - count($history) }})</span>
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
                                {{ __('ai.Hello User', ['name' => auth()->user()->name]) }}
                            </p>
                        </div>
                    </div>

                    <!-- Render Historical & Active Messages -->
                    @foreach($history as $msg)
                        @if($msg->role === 'user')
                            <div class="flex items-start justify-end gap-3">
                                <div class="max-w-2xl p-4 rounded-2xl bg-[#1A1D20] text-white space-y-1 text-xs font-bold shadow-md">
                                    <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">{{ auth()->user()->name }}</p>
                                    <p class="leading-relaxed">{{ $msg->content }}</p>
                                </div>
                                <div class="w-10 h-10 rounded-2xl bg-stone-200 dark:bg-gray-700 text-gray-800 dark:text-white flex items-center justify-center font-black shrink-0 shadow-sm">
                                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                                </div>
                            </div>
                        @else
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-[#1A1D20] text-white flex items-center justify-center font-black shrink-0 shadow-sm">
                                    <x-heroicon-o-cpu-chip class="w-5 h-5 text-white" />
                                </div>
                                <div class="max-w-2xl p-5 rounded-2xl bg-stone-50 dark:bg-gray-900 border border-stone-200/80 dark:border-gray-700 space-y-2 text-xs font-semibold text-gray-800 dark:text-gray-200 leading-relaxed shadow-xs">
                                    <p class="text-[10px] text-purple-600 dark:text-purple-400 font-extrabold uppercase tracking-wider">AI Copilot</p>
                                    <div class="prose dark:prose-invert prose-xs max-w-none leading-relaxed">
                                        {!! nl2br(e($msg->content)) !!}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach

                    <!-- Confirmation Request Box -->
                    @if($showConfirmation && !empty($previewItems))
                        <div class="flex items-start justify-end gap-3">
                            <div class="max-w-2xl p-4 rounded-2xl bg-[#1A1D20] text-white text-xs font-bold shadow-md">
                                <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">{{ auth()->user()->name }}</p>
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
                                        {{ __('ai.Confirmation Required') }}
                                    </span>
                                    <h3 class="text-base font-black text-gray-900 dark:text-white mt-2">
                                        {{ __('ai.Hello User Confirm', ['name' => auth()->user()->name]) }}
                                    </h3>
                                    <p class="text-xs font-bold text-gray-600 dark:text-gray-300 mt-1">
                                        {{ __('ai.AI interpreted:') }} <span class="text-[#1A1D20] dark:text-white font-extrabold">"{{ $pendingPrompt }}"</span>
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
                                        {{ __('ai.No Cancel Request') }}
                                    </button>

                                    <button type="button" wire:click="confirmExecution"
                                            class="w-full sm:w-auto px-6 py-2.5 rounded-full font-black text-xs text-white bg-[#1A1D20] hover:bg-black shadow-md transition">
                                        {{ __('ai.Yes Confirm Execute') }}
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
                            placeholder="{{ app()->getLocale() === 'en'
                                ? 'Write your command (e.g. Delete 5 recent bookings, or Create 5 services...)'
                                : 'Shkruani udhëzimin tuaj (p.sh: Më fshi 5 rezervimet e fundit, apo Më krijo 5 shërbime...)' }}"
                        ></textarea>

                        <div class="absolute right-3 bottom-2.5 flex items-center gap-2">
                            <button type="submit" wire:loading.attr="disabled"
                                    class="px-6 py-2.5 rounded-full font-black text-xs text-white bg-[#1A1D20] hover:bg-black shadow-md transition flex items-center gap-2">
                                <span wire:loading.remove>{{ __('ai.Send') }}</span>
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
                        <span>{{ __('ai.Quick Prompts') }}</span>
                    </div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">
                        {{ __('ai.Quick Examples') }}
                    </h3>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('ai.Click Example') }}
                    </p>
                </div>

                <div class="space-y-4">
                    <!-- Example 1: Delete Recent -->
                    <div class="p-4 rounded-xl bg-stone-50 dark:bg-gray-900/50 border border-stone-200/80 dark:border-gray-700/80 space-y-3">
                        <div class="flex items-center gap-2 font-black text-xs text-gray-900 dark:text-white">
                            <x-heroicon-o-trash class="w-4 h-4 text-red-500" />
                            <span>{{ __('ai.Delete Recent Bookings') }}</span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-300 font-medium leading-relaxed">
                            "{{ __('ai.Delete Recent Bookings Prompt') }}"
                        </p>
                        <button type="button"
                                wire:click="$set('prompt', 'Më fshi 5 rezervimet e fundit që sapo krijove')"
                                class="w-full py-2.5 rounded-full bg-[#1A1D20] text-white hover:bg-black font-black text-xs transition shadow-sm">
                            {{ __('ai.Use This Instruction') }}
                        </button>
                    </div>

                    <!-- Example 2: Services -->
                    <div class="p-4 rounded-xl bg-stone-50 dark:bg-gray-900/50 border border-stone-200/80 dark:border-gray-700/80 space-y-3">
                        <div class="flex items-center gap-2 font-black text-xs text-gray-900 dark:text-white">
                            <x-heroicon-o-pencil-square class="w-4 h-4 text-blue-500" />
                            <span>{{ __('ai.Create Services') }}</span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-300 font-medium leading-relaxed">
                            "{{ __('ai.Create Services Prompt') }}"
                        </p>
                        <button type="button"
                                wire:click="$set('prompt', 'Më krijo 5 shërbime: Prerje 1000Lekë 30min, Mjekërr 500Lekë 15min, Larje 300Lekë 10min, Modelim 800Lekë 20min, Trajtim 1500Lekë 40min')"
                                class="w-full py-2.5 rounded-full bg-[#1A1D20] text-white hover:bg-black font-black text-xs transition shadow-sm">
                            {{ __('ai.Use This Instruction') }}
                        </button>
                    </div>

                    <!-- Example 3: Walk-In Bookings -->
                    <div class="p-4 rounded-xl bg-stone-50 dark:bg-gray-900/50 border border-stone-200/80 dark:border-gray-700/80 space-y-3">
                        <div class="flex items-center gap-2 font-black text-xs text-gray-900 dark:text-white">
                            <x-heroicon-o-chart-bar class="w-4 h-4 text-emerald-500" />
                            <span>{{ __('ai.Walk-In Bookings') }}</span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-300 font-medium leading-relaxed">
                            "{{ __('ai.Walk-In Bookings Prompt') }}"
                        </p>
                        <button type="button"
                                wire:click="$set('prompt', 'Më krijo 5 rezervime walk-in me klientë rastësorë për sot nga ora 13:00 deri 17:00 pa dërguar mesazhe SMS')"
                                class="w-full py-2.5 rounded-full bg-[#1A1D20] text-white hover:bg-black font-black text-xs transition shadow-sm">
                            {{ __('ai.Use This Instruction') }}
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
