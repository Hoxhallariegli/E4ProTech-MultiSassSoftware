<div class="space-y-6">
    <div class="card p-6 bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <x-h1>Menaxhimi i Orareve</x-h1>
                <x-short-description class="dark:text-gray-400">Përcaktoni orarin javor të punës për çdo berber.</x-short-description>
            </div>
            <div class="w-full sm:w-64">
                <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-400">Zgjidh Berberin</label>
                <x-form.dropdown-search name="barber_id" wire:model.live="barber_id" label="none" :data="$barbers" placeholder="Zgjidh berberin..." />
            </div>
        </div>
    </div>

    @include('errors.messages')

    @if($barber_id)
        <div class="card p-8 bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm">
            <form wire:submit.prevent="save" class="space-y-6">
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($daysOfWeek as $day)
                        <div class="py-4 flex flex-col md:flex-row md:items-center justify-between gap-6" wire:key="day-row-{{ $day }}">
                            <!-- Day Name -->
                            <div class="w-32">
                                <span class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">
                                    {{ __($day) }}
                                </span>
                            </div>

                            <!-- Time Inputs -->
                            <div class="flex-1 grid grid-cols-2 lg:grid-cols-4 gap-3" x-data="{ isClosed: @entangle('schedule.' . $day . '.is_closed') }">
                                <div>
                                    <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Ora e Hapjes</label>
                                    <input type="time"
                                           wire:model.live="schedule.{{ $day }}.open_time"
                                           ::disabled="isClosed"
                                           class="w-full p-2.5 text-xs font-bold bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl disabled:opacity-40 dark:text-white">
                                </div>
                                <div>
                                    <label class="block mb-1 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Ora e Mbylljes</label>
                                    <input type="time"
                                           wire:model.live="schedule.{{ $day }}.close_time"
                                           ::disabled="isClosed"
                                           class="w-full p-2.5 text-xs font-bold bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl disabled:opacity-40 dark:text-white">
                                </div>
                                <div>
                                    <label class="block mb-1 text-[10px] font-bold text-amber-500 uppercase tracking-widest">Dreka Nga</label>
                                    <input type="time"
                                           wire:model.live="schedule.{{ $day }}.lunch_start"
                                           ::disabled="isClosed"
                                           class="w-full p-2.5 text-xs font-bold bg-amber-50/50 dark:bg-gray-900 border border-amber-200/60 dark:border-amber-900/40 rounded-2xl disabled:opacity-40 dark:text-white">
                                </div>
                                <div>
                                    <label class="block mb-1 text-[10px] font-bold text-amber-500 uppercase tracking-widest">Dreka Deri</label>
                                    <input type="time"
                                           wire:model.live="schedule.{{ $day }}.lunch_end"
                                           ::disabled="isClosed"
                                           class="w-full p-2.5 text-xs font-bold bg-amber-50/50 dark:bg-gray-900 border border-amber-200/60 dark:border-amber-900/40 rounded-2xl disabled:opacity-40 dark:text-white">
                                </div>
                            </div>

                            <!-- Is Closed Toggle -->
                            <div class="w-32 flex items-center md:justify-end">
                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" wire:model.live="schedule.{{ $day }}.is_closed" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                                    <span class="ms-3 text-xs font-bold text-gray-600 dark:text-gray-300">Pushim</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 flex justify-end pt-4 border-t border-gray-100 dark:border-gray-700">
                    <x-button type="submit" variant="blue" class="!px-12 !py-4 !rounded-2xl font-black text-sm">
                        Ruaj Orarin Javor
                    </x-button>
                </div>
            </form>
        </div>
    @else
        <div class="card p-12 text-center bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm text-gray-400">
            <x-heroicon-o-user class="w-12 h-12 mx-auto mb-4 text-gray-300" />
            Ju lutem zgjidhni një berber nga menyja e mësipërme për të menaxhuar orarin javor.
        </div>
    @endif
</div>
