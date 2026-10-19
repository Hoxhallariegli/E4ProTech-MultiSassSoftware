<div class="p-6 space-y-6">
    @if($created)
        <div class="flex flex-col items-center text-center py-8">
            <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center mb-4">
                <x-heroicon-o-check class="w-6 h-6 text-emerald-500" />
            </div>
            <p class="font-bold text-gray-900 dark:text-white">Kërkesa juaj u dërgua me sukses!</p>
            @if($createdLabel)<p class="text-sm font-mono text-gray-500 dark:text-gray-400 mt-1">{{ $createdLabel }}</p>@endif
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 max-w-sm leading-relaxed">Ekipi ynë do t'ju kontaktojë së shpejti për konfigurimin e uebsajtit dhe domain-it tuaj.</p>
            <button type="button" wire:click="addAnother" class="mt-6 text-xs font-black uppercase tracking-widest text-blue-600 dark:text-blue-400">+ Dërgo Kërkesë Tjetër</button>
        </div>
    @else
        <form wire:submit.prevent="store" class="space-y-4">
            <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Domain-i i Dëshiruar (Uebsajti) *</label>
                <input type="text" wire:model.live="desired_domain" placeholder="p.sh: www.sallonituaj.al" class="w-full p-3 text-sm font-bold bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                @error('desired_domain') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Numri i Telefonit për Kontakt *</label>
                <input type="text" wire:model.live="phone" placeholder="p.sh: +355691234567" class="w-full p-3 text-sm font-bold bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                @error('phone') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Shënime ose Kërkesa të Veçanta</label>
                <textarea wire:model.live="notes" rows="3" placeholder="Shkruani ngjyrat apo dëshirat tuaja për uebsajtin..." class="w-full p-3 text-sm font-bold bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white"></textarea>
                @error('notes') <p class="text-xs text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
            </div>

            <div class="mt-6 flex justify-end gap-3 border-t border-stone-100 dark:border-gray-700 pt-4">
                <x-button type="submit" variant="blue" class="!px-8 !py-3 !rounded-2xl !font-black !text-xs">
                    <span wire:loading.remove>Dërgo Kërkesën</span>
                    <span wire:loading>...</span>
                </x-button>
            </div>
        </form>
    @endif
</div>
