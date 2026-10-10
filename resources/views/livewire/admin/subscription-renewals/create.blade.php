<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1">
        <div>
            <x-h1>Kërkesë e Re për Renovim</x-h1>
            <x-short-description class="dark:text-gray-400">Plotësoni të dhënat dhe ngarkoni faturën e transfertës bankare</x-short-description>
        </div>
        <x-back-btn route="admin.subscription-renewals.index" />
    </div>

    @include('errors.errors')

    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700">
        <form wire:submit.prevent="store" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                <div>
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <x-form.dropdown-search name="plan_id" wire:model.live="plan_id" label="Zgjidh Planin e Abonimit" :data="$plans" placeholder="Zgjidh Planin..." />
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300">Mënyra e Pagesës</label>
                    <select name="payment_method" wire:model="payment_method" class="w-full p-3.5 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                        <option value="bank_transfer">🏦 Transfertë Bankare (Bank Transfer)</option>
                        <option value="cash">💵 Cash (Në Dorë)</option>
                        <option value="card">💳 Kartë Kreditit/Debitit</option>
                        <option value="online">🌐 Pagesë Online</option>
                    </select>
                </div>

                <div>
                    <x-file-upload name="transfer_document" wire:model="transfer_document" label="Fatura / Mandat-Pagesa Bankare" id="transfer_document" :isEditing="false" />
                </div>

                <div>
                    <x-form.input name="amount" type="number" step="0.01" min="0" inputmode="decimal" oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*)\./g,'$1').replace(/(\.\d{2}).*/g,'$1')" wire:model.live="amount" label="Shuma e Pagesës (€)" class="dark:bg-gray-900" placeholder="19.99" />
                </div>

                <div class="md:col-span-2">
                    <x-form.textarea name="notes" wire:model="notes" label="Shënime / Detaje Shtesë për Faturën" class="dark:bg-gray-900" placeholder="Shkruani numrin e faturës ose shënime për transfertën..." />
                </div>

                @if(auth()->user()->is_global_admin)
                    <div>
                        <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300">Statusi i Miratimit (Admin Only)</label>
                        <select name="status" wire:model="status" class="w-full p-3.5 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                            <option value="pending">⏳ Në Pritje (Pending)</option>
                            <option value="approved">✅ I Miratuar (Approved)</option>
                            <option value="rejected">❌ I Refuzuar (Rejected)</option>
                        </select>
                    </div>
                @endif
            </div>

            <div class="mt-10 flex justify-end">
                <x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl font-black">
                    Dërgo Kërkesën për Renovim 🚀
                </x-button>
            </div>
        </form>
    </div>
</div>
