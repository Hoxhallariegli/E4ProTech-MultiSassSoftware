<div class="space-y-8">
    <div class="flex items-center justify-between gap-4 px-1">
        <div>
            <x-h1>Ndrysho Kërkesën për Uebsajt #{{ $item->id }}</x-h1>
            <x-short-description class="dark:text-gray-400">Ndrysho detajet apo statusin e kërkesës për domain dhe uebsajt të personalizuar</x-short-description>
        </div>
        <x-back-btn route="admin.website-offer-requests.index" />
    </div>

    @include('errors.errors')

    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700">
        <form wire:submit.prevent="update" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Salloni *</label>
                    <select wire:model.live="barber_shop_id" class="w-full px-4 py-3 rounded-2xl bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 text-sm font-bold text-gray-900 dark:text-white">
                        <option value="">Zgjidh Sallonin</option>
                        @foreach($shops as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Domain-i i Dëshiruar *</label>
                    <input type="text" wire:model.live="desired_domain" placeholder="p.sh: www.sallonituaj.al" class="w-full px-4 py-3 rounded-2xl bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 text-sm font-bold text-gray-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Telefon *</label>
                    <input type="text" wire:model.live="phone" placeholder="p.sh: +355691234567" class="w-full px-4 py-3 rounded-2xl bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 text-sm font-bold text-gray-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Statusi *</label>
                    <select wire:model.live="status" class="w-full px-4 py-3 rounded-2xl bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 text-sm font-bold text-gray-900 dark:text-white">
                        <option value="pending">Në Pritje (Pending)</option>
                        <option value="contacted">U Kontaktua (Contacted)</option>
                        <option value="completed">E Përfunduar (Completed)</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Shënime</label>
                    <textarea wire:model.live="notes" rows="4" placeholder="Kërkesa apo detaje të veçanta për uebsajtin..." class="w-full px-4 py-3 rounded-2xl bg-stone-50 dark:bg-gray-900 border border-stone-200 dark:border-gray-700 text-sm font-bold text-gray-900 dark:text-white"></textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <x-button type="submit" variant="blue" class="w-full sm:w-auto !px-10 !py-3.5 !rounded-2xl !font-black">Përditëso Kërkesën 🚀</x-button>
            </div>
        </form>
    </div>
</div>
