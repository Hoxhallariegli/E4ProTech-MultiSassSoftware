<div>
    <div class="card">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <x-h1>{{ __('audit.Audit Trails') }}</x-h1>
                <x-short-description class="dark:text-gray-400">Gjurmët e auditimit dhe historiku i të gjitha veprimeve në sistem (Si Ishte ➔ Si U Bë)</x-short-description>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" wire:click="resetFilters" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-2xl transition">
                    <span>{{ __('audit.Reset form') }}</span>
                </button>
                <button type="button" @click="isOpen = !isOpen" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-600 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-sm transition">
                    <span>{{ __('audit.Advanced Search') }}</span>
                </button>
            </div>
        </div>

        <div class="mb-5" x-data="{ isOpen: @if($openFilter || request('openFilter')) true @else false @endif }">
            <div
                x-show="isOpen"
                x-cloak
                class="bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 rounded-2xl p-6 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">

                    <x-form.select id="user_id" name="user_id" :label="__('audit.User')" wire:model.live="user_id" class="dark:bg-gray-800">
                        <option value="">Gjithë Përdoruesit</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </x-form.select>

                    <x-form.select id="section" name="section" :label="__('audit.Section')" wire:model.live="section" class="dark:bg-gray-800">
                        <option value="">Gjithë Seksionet</option>
                        @foreach($sections as $section)
                            <option value="{{ $section }}">{{ $section }}</option>
                        @endforeach
                    </x-form.select>

                    <x-form.select id="type" name="type" :label="__('audit.Type')" wire:model.live="type" class="dark:bg-gray-800">
                        <option value="">Gjithë Llojet</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </x-form.select>

                    <div>
                        <label class="block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">Kërko Titullin</label>
                        <input type="search" id="title" name="title" wire:model.live.debounce.300ms="title" placeholder="Kërko me veprim..." class="w-full p-3 text-sm font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl dark:text-white">
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto border-t border-gray-100 dark:border-gray-700">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-100/50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">
                            <a href="#" wire:click.prevent="sortBy('user_id')" role="button">{{ __('audit.User') }}</a>
                        </th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">
                            <a href="#" wire:click.prevent="sortBy('title')" role="button">{{ __('audit.Action') }}</a>
                        </th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">
                            <a href="#" wire:click.prevent="sortBy('section')" role="button">{{ __('audit.Section') }}</a>
                        </th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">
                            <a href="#" wire:click.prevent="sortBy('type')" role="button">{{ __('audit.Type') }}</a>
                        </th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">
                            {{ __('audit.View') }}
                        </th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">
                            <a href="#" wire:click.prevent="sortBy('created_at')" role="button">{{ __('audit.Created At') }}</a>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    @foreach($this->userlogs() as $log)
                        <tr wire:key="{{ $log->id }}" class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition">
                            <td class="px-6 py-4 font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                {{ $log->user->name ?? 'Sistemi / Publik' }}
                            </td>
                            <td class="px-6 py-4 font-bold text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                {{ $log->title }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 text-xs font-bold text-blue-700 bg-blue-50 dark:bg-blue-900/40 dark:text-blue-300 rounded-lg">
                                    {{ $log->section }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 text-xs font-bold uppercase rounded-lg {{ $log->type === 'create' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : ($log->type === 'update' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300') }}">
                                    {{ $log->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div x-data="{ open: false }" class="inline-block">
                                    <button @click="open = true" class="px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-black text-xs hover:bg-indigo-100 transition flex items-center gap-1">
                                        <span>Shiko Detajet</span>
                                        <span>↗</span>
                                    </button>

                                    <x-modal>
                                        <x-slot name="trigger"></x-slot>
                                        <x-slot name="modalTitle">
                                            <div class="dark:text-white font-black text-left flex items-center gap-2">
                                                <span>📋 {{ $log->title }}</span>
                                                @if($log->reference_id)
                                                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-700 font-bold">#{{ $log->reference_id }}</span>
                                                @endif
                                            </div>
                                        </x-slot>
                                        <x-slot name="content">
                                            <div class="text-left space-y-4 max-h-[60vh] overflow-y-auto">
                                                <div class="p-4 bg-gray-50 dark:bg-gray-800/80 rounded-2xl text-xs space-y-2 border border-gray-100 dark:border-gray-700">
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-400 font-bold">Kryer nga:</span>
                                                        <span class="font-bold text-gray-900 dark:text-white">{{ $log->user->name ?? 'Sistemi / Publik' }}</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-400 font-bold">Seksioni / Lloji:</span>
                                                        <span class="font-bold text-gray-900 dark:text-white">{{ $log->section }} ({{ $log->type }})</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-400 font-bold">Koha:</span>
                                                        <span class="font-bold text-gray-900 dark:text-white">{{ date('d/m/Y H:i:s', strtotime($log->created_at)) }}</span>
                                                    </div>
                                                </div>

                                                @php
                                                    $displayNewValues = $log->new_values;
                                                    if (empty($displayNewValues) && $log->reference_id && $log->section) {
                                                        $modelClass = match($log->section) {
                                                            'Bookings' => \App\Models\Booking::class,
                                                            'Payments' => \App\Models\Payment::class,
                                                            'BarberShops' => \App\Models\BarberShop::class,
                                                            'Barbers' => \App\Models\Barber::class,
                                                            'Services' => \App\Models\Service::class,
                                                            'Customers' => \App\Models\Customer::class,
                                                            'MessageQueues' => \App\Models\MessageQueue::class,
                                                            'MessageLogs' => \App\Models\MessageLog::class,
                                                            'Users' => \App\Models\User::class,
                                                            default => null,
                                                        };

                                                        if ($modelClass && class_exists($modelClass)) {
                                                            try {
                                                                $record = $modelClass::find($log->reference_id);
                                                                if ($record) {
                                                                    $displayNewValues = $record->toArray();
                                                                }
                                                            } catch (\Throwable $e) {}
                                                        }
                                                    }
                                                @endphp

                                                @if($log->type === 'update' && !empty($log->old_values))
                                                    <div class="space-y-2">
                                                        <h4 class="text-xs font-black uppercase text-gray-400 tracking-wider">Ndryshimet te Fushat (Si Ishte ➔ Si U Bë)</h4>
                                                        <div class="border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden divide-y divide-gray-100 dark:divide-gray-700 text-xs">
                                                            @foreach($displayNewValues ?? [] as $field => $newVal)
                                                                @php $oldVal = $log->old_values[$field] ?? '-'; @endphp
                                                                <div class="p-3 bg-white dark:bg-gray-900 grid grid-cols-3 gap-2 items-center">
                                                                    <span class="font-bold text-gray-700 dark:text-gray-300 capitalize">{{ str_replace('_', ' ', $field) }}</span>
                                                                    <span class="px-2.5 py-1 bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300 rounded-lg text-[11px] font-mono truncate" title="{{ is_array($oldVal) ? json_encode($oldVal) : $oldVal }}">
                                                                        {{ is_array($oldVal) ? json_encode($oldVal) : ($oldVal ?: '-') }}
                                                                    </span>
                                                                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 rounded-lg text-[11px] font-mono truncate" title="{{ is_array($newVal) ? json_encode($newVal) : $newVal }}">
                                                                        {{ is_array($newVal) ? json_encode($newVal) : ($newVal ?: '-') }}
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @elseif(!empty($displayNewValues))
                                                    <div class="space-y-2">
                                                        <h4 class="text-xs font-black uppercase text-gray-400 tracking-wider">Detajet e Të Dhënave (New Values)</h4>
                                                        <div class="border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden divide-y divide-gray-100 dark:divide-gray-700 text-xs max-h-60 overflow-y-auto">
                                                            @foreach($displayNewValues as $fKey => $fVal)
                                                                <div class="p-3 bg-white dark:bg-gray-900 flex justify-between items-center gap-4">
                                                                    <span class="font-bold text-gray-500 capitalize">{{ str_replace('_', ' ', $fKey) }}</span>
                                                                    <span class="font-mono text-gray-900 dark:text-white truncate max-w-[200px]" title="{{ is_array($fVal) ? json_encode($fVal) : $fVal }}">
                                                                        {{ is_array($fVal) ? json_encode($fVal) : ($fVal ?? '-') }}
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl text-xs text-gray-400 font-medium">
                                                        Nuk ka detaje shtesë për këtë veprim. ID e referencës: #{{ $log->reference_id ?? 'N/A' }}
                                                    </div>
                                                @endif
                                            </div>
                                        </x-slot>
                                        <x-slot name="footer">
                                            <x-button variant="gray" @click="on = false">Mbyll</x-button>
                                        </x-slot>
                                    </x-modal>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-mono">
                                {{ date('d/m/Y H:i:s', strtotime($log->created_at)) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-50 dark:border-gray-700/50">
            {{ $this->userlogs()->links() }}
        </div>
    </div>
</div>
