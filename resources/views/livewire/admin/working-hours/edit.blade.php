<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1"><div><x-h1>{{ __('working-hours.Edit WorkingHour') }}</x-h1><x-short-description class="dark:text-gray-400">{{ __('working-hours.Update info') }}</x-short-description></div><x-back-btn route="admin.working-hours.index" /></div>
    @include('errors.errors')
    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700"><form wire:submit.prevent="update" class="space-y-8"><div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8"><div>
    <div class="flex items-end gap-2">
        <div class="flex-1"><x-form.dropdown-search name="barber_id" wire:model.live="barber_id" :label="__('working-hours.Barber Id')" :data="$barbers" /></div>
        <x-modal>
            <x-slot name="trigger"><button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button></x-slot>
            <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Add New Barber</div></x-slot>
            <x-slot name="content"><livewire:admin.barbers.quick-create /></x-slot>
        </x-modal>
    </div>
</div>
<div><label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest">{{ __('working-hours.Day Of Week') }}</label><select name="day_of_week" wire:model="day_of_week" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl"><option value="">--</option><option value="Monday">Monday</option><option value="Tuesday">Tuesday</option><option value="Wednesday">Wednesday</option><option value="Thursday">Thursday</option><option value="Friday">Friday</option><option value="Saturday">Saturday</option><option value="Sunday">Sunday</option></select></div>
<div><x-form.input name="open_time" type="datetime-local" wire:model.live="open_time" :label="__('working-hours.Open Time')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="close_time" type="datetime-local" wire:model.live="close_time" :label="__('working-hours.Close Time')" class="dark:bg-gray-900" /></div>
<div><x-form.checkbox name="is_closed" wire:model="is_closed" :label="__('working-hours.Is Closed')" /></div></div><div class="mt-10 flex justify-end"><x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl">{{ __('working-hours.Update') }}</x-button></div></form></div>
</div>
