<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1"><div><x-h1>{{ __('test-modules.Add TestModule') }}</x-h1><x-short-description class="dark:text-gray-400">{{ __('test-modules.New record') }}</x-short-description></div><x-back-btn route="admin.test-modules.index" /></div>
    @include('errors.errors')
    
    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700"><form wire:submit.prevent="store" class="space-y-8 "><div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8"><div><x-form.input name="name" type="text" wire:model.live="name" :label="__('test-modules.Name')" class="dark:bg-gray-900" /></div>
<div class="md:col-span-2"><x-form.textarea name="description" wire:model="description" :label="__('test-modules.Description')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="qty" type="number" step="0.01" min="0" inputmode="decimal" oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*)\./g,'$1').replace(/(\.\d{2}).*/g,'$1')" wire:model.live="qty" :label="__('test-modules.Qty')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="price" type="number" step="0.01" min="0" inputmode="decimal" oninput="this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\..*)\./g,'$1').replace(/(\.\d{2}).*/g,'$1')" wire:model.live="price" :label="__('test-modules.Price')" class="dark:bg-gray-900" /></div>
<div><x-form.checkbox name="is_active" wire:model="is_active" :label="__('test-modules.Is Active')" /></div>
<div><x-form.input name="due_date" type="date" wire:model.live="due_date" :label="__('test-modules.Due Date')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="event_at" type="datetime-local" wire:model.live="event_at" :label="__('test-modules.Event At')" class="dark:bg-gray-900" /></div>
<div>
    <div class="flex items-end gap-2">
        <div class="flex-1"><x-form.dropdown-search name="user_id" wire:model.live="user_id" :label="__('test-modules.User Id')" :data="$users" /></div>
        <x-modal>
            <x-slot name="trigger"><button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button></x-slot>
            <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">Add New User</div></x-slot>
            <x-slot name="content"><livewire:admin.users.quick-create /></x-slot>
        </x-modal>
    </div>
</div>
<div><label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest">{{ __('test-modules.Priority') }}</label><select name="priority" wire:model="priority" class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl"><option value="">--</option><option value="Low">Low</option><option value="Medium">Medium</option><option value="High">High</option></select></div>
<div><x-file-upload name="image" wire:model="image" :label="__('test-modules.Image')" id="image" :isEditing="false" /></div>
<div><x-file-upload name="cover_photo" wire:model="cover_photo" :label="__('test-modules.Cover Photo')" id="cover_photo" :isEditing="false" /></div></div><div class="mt-10 flex justify-end"><x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl">{{ __('test-modules.Save') }}</x-button></div></form></div>
</div>