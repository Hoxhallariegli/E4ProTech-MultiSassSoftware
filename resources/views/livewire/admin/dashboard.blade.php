<div class="space-y-8 p-4 sm:p-6">
    @php
        $isExpired = $shop ? $shop->is_expired : false;
        $currencySymbol = $shop ? $shop->resolved_currency : 'Lekë';
    @endphp

    @if($isExpired)
        <div class="bg-gradient-to-r from-red-500/10 via-amber-500/10 to-red-500/10 border-l-4 border-red-500 p-6 rounded-2xl shadow-sm border border-red-200 dark:border-red-900/50">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-red-500/20 text-red-600 dark:text-red-400 rounded-2xl text-2xl font-black shrink-0">
                    ⚠️
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-black text-red-900 dark:text-red-100 mb-1">
                        Abonimi për sallonin tuaj "{{ $shop?->name }}" ka përfunduar!
                    </h3>
                    <p class="text-xs text-red-700 dark:text-red-300 leading-relaxed mb-4">
                        Për të vazhduar marrjen e rezervimeve online dhe dërgimin e SMS-ve automatike, ju lutemi rinovoni abonimin.
                    </p>
                    <a href="{{ route('admin.subscription-renewals.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-black shadow-md transition">
                        Rinovoni Abonimin Tani ↗
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Welcome & Active Plan Summary Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card p-6 bg-white dark:bg-gray-800 shadow rounded-2xl border border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-black text-gray-900 dark:text-white">Mirëseerdhët, {{ $user->name }} 👋</h3>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1">Menaxhoni biznesin dhe rezervimet tuaja në kohë reale.</p>
            @if($shop)
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs">
                    <span class="text-gray-500 font-bold">Plani Aktiv:</span>
                    <span class="font-extrabold {{ $shop->is_expired ? 'text-red-500' : 'text-emerald-600 dark:text-emerald-400' }}">
                        {{ $shop->is_expired ? 'Abonimi Ka Skaduar' : $shop->active_plan_name }} ({{ $shop->days_left }} Ditë të Mbetura)
                    </span>
                </div>
            @endif
        </div>
    </div>

    <!-- ONBOARDING & QUICK SETUP CARD (Ultra-Informative) -->
    <div class="card p-8 sm:p-10 bg-white dark:bg-gray-800 shadow-sm rounded-[2.5rem] border border-gray-100 dark:border-gray-700 space-y-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-stone-100 dark:border-gray-700 pb-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 mb-2">
                    <x-heroicon-o-bolt class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
                    <span>Quick Onboarding Guide</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                    Onboarding &amp; Konfigurimi i Shpejtë
                </h2>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-1">
                    Ndiqni këto 4 hapa të thjeshtë për të hapur faqen tuaj publike dhe aktivizuar SMS Gateway në Android.
                </p>
            </div>

            <!-- Onboarding Progress Bar -->
            <div class="w-full sm:w-64 bg-stone-50 dark:bg-gray-900 p-4 rounded-2xl border border-stone-200 dark:border-gray-700 space-y-2">
                <div class="flex justify-between items-center text-xs font-bold">
                    <span class="text-gray-700 dark:text-gray-300">Progresi:</span>
                    <span class="text-[#7C5CFC] font-extrabold">{{ $progressPercentage }}%</span>
                </div>
                <div class="w-full bg-stone-200 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-[#7C5CFC] to-blue-600 transition-all duration-500 rounded-full"
                         style="width: {{ $progressPercentage }}%;"></div>
                </div>
                <span class="text-[10px] text-gray-400 font-medium block text-right">{{ $completedSteps }} nga 4 Hapa të Përfunduar</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            <!-- Step 1: Register Staff -->
            <div class="p-6 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-200/80 dark:border-gray-700/80 flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center font-black">
                            <x-heroicon-o-user class="w-5 h-5" />
                        </div>
                        @if($staffCount > 0)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase text-emerald-800 bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300">
                                ✓ {{ $staffCount }} Staf
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase text-amber-800 bg-amber-100 dark:bg-amber-900/40 dark:text-amber-300">
                                ⚠️ Bosh
                            </span>
                        @endif
                    </div>
                    <span class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest block">HAPI 1</span>
                    <h4 class="text-sm font-black text-gray-900 dark:text-white mt-1 mb-2">Regjistroni Stafin &amp; Oraret</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed font-medium">
                        Shtoni berberët apo parukierët tuaj dhe vendosni oraret e tyre të punës për të lejuar rezervimet online.
                    </p>
                </div>

                <a href="{{ route('admin.barbers.index') }}"
                   class="w-full py-2.5 rounded-2xl bg-gray-900 text-white dark:bg-white dark:text-gray-900 font-bold text-xs shadow-sm hover:opacity-90 transition flex items-center justify-center gap-2">
                    <span>Add Staff Members ↗</span>
                </a>
            </div>

            <!-- Step 2: Register Services -->
            <div class="p-6 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-200/80 dark:border-gray-700/80 flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center font-black">
                            <x-heroicon-o-scissors class="w-5 h-5" />
                        </div>
                        @if($servicesCount > 0)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase text-emerald-800 bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300">
                                ✓ {{ $servicesCount }} Shërbime
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase text-amber-800 bg-amber-100 dark:bg-amber-900/40 dark:text-amber-300">
                                ⚠️ Bosh
                            </span>
                        @endif
                    </div>
                    <span class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest block">HAPI 2</span>
                    <h4 class="text-sm font-black text-gray-900 dark:text-white mt-1 mb-2">Shërbimet &amp; Çmimet</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed font-medium">
                        Konfiguroni shërbimet, çmimet në <strong>{{ $currencySymbol }}</strong> dhe kohëzgjatjen në minuta.
                    </p>
                </div>

                <a href="{{ route('admin.services.index') }}"
                   class="w-full py-2.5 rounded-2xl bg-gray-900 text-white dark:bg-white dark:text-gray-900 font-bold text-xs shadow-sm hover:opacity-90 transition flex items-center justify-center gap-2">
                    <span>Add Services ↗</span>
                </a>
            </div>

            <!-- Step 3: Download APK in NEW TAB -->
            <div class="p-6 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-200/80 dark:border-gray-700/80 flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center font-black">
                            <x-heroicon-o-device-phone-mobile class="w-5 h-5" />
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase text-blue-800 bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300">
                            📲 APK v1.0.4
                        </span>
                    </div>
                    <span class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest block">HAPI 3</span>
                    <h4 class="text-sm font-black text-gray-900 dark:text-white mt-1 mb-2">Shkarko Aplikacionin APK</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed font-medium">
                        Instaloni aplikacionin zyrtar Android në telefon për të menaxhuar takimet dhe dërguar SMS automatike.
                    </p>
                </div>

                <!-- OPEN APK DOWNLOAD IN NEW TAB -->
                <a href="{{ route('app.download.apk') }}" target="_blank" rel="noopener"
                   class="w-full py-2.5 rounded-2xl bg-[#7C5CFC] hover:bg-[#6366F1] text-white font-black text-xs shadow-md transition flex items-center justify-center gap-2">
                    <span>Download APK File 🚀</span>
                    <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                </a>
            </div>

            <!-- Step 4: SMS Gateway Setup -->
            <div class="p-6 rounded-2xl bg-gray-50/80 dark:bg-gray-900/50 border border-gray-200/80 dark:border-gray-700/80 flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center font-black">
                            <x-heroicon-o-bolt class="w-5 h-5" />
                        </div>
                        @if($activeSmsTokens > 0)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase text-emerald-800 bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300">
                                ✓ SMS Aktiv
                            </span>
                        @elseif($shop?->sms_enabled)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase text-blue-800 bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300">
                                ⚡ SIM Karta OK
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase text-amber-800 bg-amber-100 dark:bg-amber-900/40 dark:text-amber-300">
                                ⚠️ Pa-aktivizuar
                            </span>
                        @endif
                    </div>
                    <span class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest block">HAPI 4</span>
                    <h4 class="text-sm font-black text-gray-900 dark:text-white mt-1 mb-2">SMS Gateway në Celular</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed font-medium">
                        Hyni te aplikacioni në celular dhe aktivizoni "Dërgo SMS nga Kjo Pajisje" për konfirmime automatike me SIM.
                    </p>
                </div>

                <a href="{{ route('admin.settings.notifications') }}"
                   class="w-full py-2.5 rounded-2xl bg-gray-900 text-white dark:bg-white dark:text-gray-900 font-bold text-xs shadow-sm hover:opacity-90 transition flex items-center justify-center gap-2">
                    <span>SMS Gateway Settings ↗</span>
                </a>
            </div>

        </div>
    </div>
</div>
