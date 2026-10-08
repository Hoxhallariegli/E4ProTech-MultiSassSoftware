<div class="space-y-6 p-6">
    @php
        $user = auth()->user();
        $shop = $user?->barberShop;
        $isExpired = $shop ? $shop->is_expired : false;
    @endphp

    @if($isExpired)
        <div class="bg-gradient-to-r from-red-500/10 via-amber-500/10 to-red-500/10 border-l-4 border-red-500 p-6 rounded-2xl shadow-sm border border-red-200 dark:border-red-900/50">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-red-500/20 text-red-600 dark:text-red-400 rounded-2xl text-2xl font-black">
                    ⚠️
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-black text-red-900 dark:text-red-100 mb-1">
                        Abonimi për sallonin tuaj "{{ $shop?->name }}" ka skaduar!
                    </h3>
                    <p class="text-xs text-red-700 dark:text-red-300 leading-relaxed mb-4">
                        Periudha e provës apo abonimi aktiv për këtë sallon ka përfunduar (0 Ditë Mbetura). Për të vazhduar pranimin e rezervimeve online dhe përdorimin e plotë të moduleve, ju lutemi renovoni abonimin tuaj.
                    </p>
                    <a href="{{ route('admin.subscriptions.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-black shadow-md transition">
                        Renovo Abonimin Tani ↗
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card p-6 bg-white dark:bg-gray-800 shadow rounded-lg">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">{{ __('Mirësevini') }}</h3>
            <p class="text-gray-500 dark:text-gray-400 mt-2">{{ __('Menaxhoni biznesin tuaj në kohë reale.') }}</p>
            @if($shop)
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs">
                    <span class="text-gray-500">Plani Aktiv:</span>
                    <span class="font-extrabold {{ $shop->is_expired ? 'text-red-500' : 'text-emerald-600' }}">{{ $shop->active_plan_name }} ({{ $shop->days_left }} Ditë Mbetura)</span>
                </div>
            @endif
        </div>
    </div>
</div>
