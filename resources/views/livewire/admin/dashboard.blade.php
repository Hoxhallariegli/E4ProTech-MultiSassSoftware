<div class="space-y-6 p-6">
    @php
        $user = auth()->user();
        $isExpired = false;
        $shop = null;
        if (!$user->hasRole('admin')) {
            $shop = $user->barberShop;
            if ($shop && $shop->expires_at && $shop->expires_at->isPast()) {
                $isExpired = true;
            }
        }
    @endphp

    @if($isExpired)
        <div class="bg-red-50 border-l-4 border-red-400 p-4 dark:bg-red-900/20">
            <div class="flex">
                <div class="flex-shrink-0">
                    <x-dynamic-component component="heroicon-o-exclamation-triangle" class="h-5 w-5 text-red-400" />
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700 dark:text-red-200">
                        {{ __('Abonimi për dyqanin tuaj') }} <strong>{{ $shop->name }}</strong> {{ __('ka skaduar më') }}
                        @if($shop->expires_at)
                            <span class="font-bold">{{ $shop->expires_at->format('d/m/Y') }}</span>.
                        @else
                            <span class="font-bold">{{ __('data e pacaktuar') }}</span>.
                        @endif
                    </p>
                    <p class="mt-2 text-sm text-red-700 dark:text-red-200">
                        {{ __('Për të vazhduar përdorimin e moduleve, ju lutem renovoni abonimin tuaj ose kontaktoni administratorin.') }}
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('admin.subscriptions.index') }}" class="text-sm font-medium text-red-700 hover:text-red-600 underline dark:text-red-200">
                            {{ __('Shko te Abonimet') }} &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="card p-6 bg-white dark:bg-gray-800 shadow rounded-lg">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">{{ __('Mirësevini') }}</h3>
                <p class="text-gray-500 dark:text-gray-400 mt-2">{{ __('Menaxhoni biznesin tuaj në kohë reale.') }}</p>
            </div>
        </div>
    @endif
</div>
