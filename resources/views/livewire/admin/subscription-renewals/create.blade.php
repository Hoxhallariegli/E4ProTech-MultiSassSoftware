<div class="space-y-8">
    <div class="flex items-center justify-between gap-4 px-1">
        <div>
            <x-h1>{{ __('subscription-renewals.New Renewal Request') }}</x-h1>
            <x-short-description class="dark:text-gray-400">{{ __('subscription-renewals.Fill in details and upload bank transfer receipt') }}</x-short-description>
        </div>
        <x-back-btn route="admin.subscription-renewals.index" />
    </div>

    @include('errors.errors')

    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700">
        <form wire:submit.prevent="store" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                <div>
                    <x-form.dropdown-search name="plan_id" wire:model.live="plan_id" :label="__('subscription-renewals.Selected Plan')" :data="$plans" placeholder="{{ __('subscription-renewals.Select Plan') }}" />
                </div>

                <div>
                    <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-700 dark:text-gray-300">{{ __('subscription-renewals.Payment Method Label') }}</label>
                    <select name="payment_method" wire:model="payment_method" class="w-full p-3.5 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                        <option value="cash">{{ __('subscription-renewals.Cash') }}</option>
                        <option value="online">{{ __('subscription-renewals.Online Payment') }}</option>
                        <option value="bank_transfer">{{ __('subscription-renewals.Bank Transfer') }}</option>
                    </select>
                </div>

                <div>
                    <x-form.input name="amount" type="number" step="0.01" min="0" inputmode="decimal"
                                  wire:model.live="amount"
                                  :label="__('subscription-renewals.Amount')"
                                  class="dark:bg-gray-900 font-bold {{ !auth()->user()->is_global_admin ? 'bg-gray-100 dark:bg-gray-800/80 cursor-not-allowed' : '' }}"
                                  :readonly="!auth()->user()->is_global_admin"
                                  placeholder="0.00" />
                </div>

                <div>
                    <x-file-upload name="transfer_document" wire:model="transfer_document" :label="__('subscription-renewals.Upload Receipt')" id="transfer_document" :isEditing="false" />
                </div>

                <div class="md:col-span-2">
                    <x-form.textarea name="notes" wire:model="notes" :label="__('subscription-renewals.Notes')" class="dark:bg-gray-900" placeholder="{{ __('subscription-renewals.Notes Placeholder') }}" />
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
