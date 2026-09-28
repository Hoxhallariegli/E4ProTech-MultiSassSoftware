<div class="card p-6 bg-white dark:bg-gray-800 shadow rounded-lg mt-6 border border-gray-100 dark:border-gray-700">
    <div class="flex items-center gap-3 mb-6">
        <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
            <x-heroicon-o-shield-check class="size-6 text-blue-600 dark:text-blue-400" />
        </div>
        <div>
            <h3 class="text-lg font-black uppercase tracking-tighter text-gray-900 dark:text-white">{{ __('Menaxhimi i Aksesit sipas Dyqanit') }}</h3>
            <p class="text-xs text-gray-500 italic">{{ __('Cakto rolet specifike që ky user ka në çdo biznes.') }}</p>
        </div>
    </div>

    <!-- Matrix Table -->
    <div class="overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700 mb-6">
        <table class="w-full text-left">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500">{{ __('Barber Shop') }}</th>
                    <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500">{{ __('Rolet e Atribuara') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($allShops as $shop)
                    <tr wire:key="shop-row-{{ $shop->id }}" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-gray-700 dark:text-gray-300">{{ $shop->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex flex-wrap gap-3">
                                @foreach($availableRoles as $role)
                                    <label wire:key="role-label-{{ $shop->id }}-{{ $role->id }}" class="flex items-center gap-2 cursor-pointer group">
                                        <input type="checkbox"
                                               wire:model="shopRoles.{{ $shop->id }}"
                                               value="{{ $role->id }}"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 size-4">
                                        <span class="text-xs font-bold text-gray-500 group-hover:text-blue-600 transition-colors uppercase tracking-tighter">{{ $role->label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-700">
        <x-button wire:click="updateAccess" class="!bg-black !text-white !px-10 !py-3 !rounded-xl !text-xs !font-black !uppercase !tracking-widest hover:!scale-105 transition-transform">
            {{ __('Ruaj Matrix-in e Aksesit') }}
        </x-button>
    </div>
</div>
