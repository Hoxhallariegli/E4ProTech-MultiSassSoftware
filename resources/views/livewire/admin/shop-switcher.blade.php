<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm leading-5 font-semibold text-gray-800 hover:border-gray-300 hover:text-gray-900 hover:shadow-xs focus:ring-3 focus:ring-gray-300/25 active:border-gray-200 active:shadow-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:text-gray-200 dark:focus:ring-gray-600/40 dark:active:border-gray-700">
        <x-heroicon-o-building-storefront class="size-5" />
        <span class="hidden sm:inline">
            @if($activeShopId)
                {{ $shops->firstWhere('id', $activeShopId)?->name ?? __('Select Shop') }}
            @else
                {{ __('Select Shop') }}
            @endif
        </span>
        <x-heroicon-o-chevron-down class="size-3" />
    </button>

    <div x-show="open" @click.away="open = false" x-cloak
         class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-700 rounded-lg shadow-xl border border-gray-100 dark:border-gray-600 z-50 overflow-hidden">
        <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-600 bg-gray-50 dark:bg-gray-800">
            <span class="text-xs font-bold uppercase text-gray-500">{{ __('Your Barber Shops') }}</span>
        </div>
        <div class="max-h-60 overflow-y-auto">
            @foreach($shops as $shop)
                <button wire:click="switchShop({{ $shop->id }})"
                        class="w-full text-left px-4 py-3 text-sm flex items-center justify-between hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors {{ $activeShopId == $shop->id ? 'bg-blue-50 dark:bg-blue-900/30 font-bold text-blue-600 dark:text-blue-400' : '' }}">
                    <span>{{ $shop->name }}</span>
                    @if($activeShopId == $shop->id)
                        <x-heroicon-o-check class="size-4" />
                    @endif
                </button>
            @endforeach
        </div>
        @if($shops->isEmpty())
            <div class="px-4 py-3 text-sm text-gray-500 italic">
                {{ __('No shops found.') }}
            </div>
        @endif
    </div>
</div>
