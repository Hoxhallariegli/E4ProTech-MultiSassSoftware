<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1">
        <div>
            <x-h1>{{ __('subscription-renewals.New Renewal Request') }}</x-h1>
            <x-short-description class="dark:text-gray-400">{{ __('subscription-renewals.Fill in details and upload bank transfer receipt') }}</x-short-description>
        </div>
        <x-back-btn route="admin.subscription-renewals.index" />
    </div>

    @include('errors.errors')

    <!-- Bank Transfer Account Details Card -->
    <div class="p-6 rounded-[2.5rem] bg-gray-50 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-700 space-y-3">
        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-gray-100 flex items-center gap-2">
            <span>🏦</span> {{ __('subscription-renewals.Bank Transfer Details') }}
        </h4>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-bold text-gray-700 dark:text-gray-300">
            <div class="p-3 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700">
                <span class="text-[10px] text-gray-400 block font-semibold">Banka</span>
                <span class="text-gray-900 dark:text-white font-black">BKT (Banka Kombëtare Tregtare)</span>
            </div>
            <div class="p-3 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700">
                <span class="text-[10px] text-gray-400 block font-semibold">IBAN / Numri i Llogarisë</span>
                <span class="text-blue-600 dark:text-blue-400 font-mono font-black">AL89205111000000000012345678</span>
            </div>
            <div class="p-3 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700">
                <span class="text-[10px] text-gray-400 block font-semibold">Marrësi</span>
                <span class="text-gray-900 dark:text-white font-black">E4ProTech Engine SH.P.K</span>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700">
        <form wire:submit.prevent="store" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                <div>
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <x-form.dropdown-search name="plan_id" wire:model.live="plan_id" :label="__('subscription-renewals.Filter by Plan')" :data="$plans" placeholder="Zgjidh Planin..." />
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300">{{ __('subscription-renewals.Payment Method Label') }}</label>
                    <select name="payment_method" wire:model="payment_method" class="w-full p-3.5 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                        <option value="bank_transfer">{{ __('subscription-renewals.Bank Transfer') }}</option>
                        <option value="cash">{{ __('subscription-renewals.Cash') }}</option>
                        <option value="card">{{ __('subscription-renewals.Credit/Debit Card') }}</option>
                        <option value="online">{{ __('subscription-renewals.Online Payment') }}</option>
                    </select>
                </div>

                <div>
                    <x-file-upload name="transfer_document" wire:model="transfer_document" :label="__('subscription-renewals.Upload Receipt')" id="transfer_document" :isEditing="false" />
                </div>

                <div>
                    <x-form.input name="amount" type="number" step="0.01" min="0" inputmode="decimal" oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*)\./g,'$1').replace(/(\.\d{2}).*/g,'$1')" wire:model.live="amount" :label="__('subscription-renewals.Amount')" class="dark:bg-gray-900" placeholder="19.99" />
                </div>

                <div class="md:col-span-2">
                    <x-form.textarea name="notes" wire:model="notes" :label="__('subscription-renewals.Notes')" class="dark:bg-gray-900" placeholder="Shkruani numrin e faturës ose shënime për transfertën..." />
                </div>

                @if(auth()->user()->is_global_admin)
                    <div>
                        <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300">{{ __('subscription-renewals.Approval Status Label') }}</label>
                        <select name="status" wire:model="status" class="w-full p-3.5 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                            <option value="pending">{{ __('subscription-renewals.Pending') }}</option>
                            <option value="approved">{{ __('subscription-renewals.Approved') }}</option>
                            <option value="rejected">{{ __('subscription-renewals.Rejected') }}</option>
                        </select>
                    </div>
                @endif
            </div>

            <div class="mt-10 flex justify-end">
                <x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl font-black">
                    {{ __('subscription-renewals.Submit Renewal Request') }}
                </x-button>
            </div>
        </form>
    </div>
</div>
