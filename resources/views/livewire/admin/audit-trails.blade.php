<div x-data="{ isOpen: @entangle('openFilter') }">
    <div class="card">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <x-h1>{{ __('audit.Audit Trails') }}</x-h1>
                <x-short-description class="dark:text-gray-400">Gjurmët e auditimit dhe historiku i të gjitha veprimeve në sistem (Si Ishte ➔ Si U Bë)</x-short-description>
            </div>
            <div class="flex items-center gap-3">
                @if($title || $user_id || $section || $type || $created_at || $openFilter)
                    <button type="button" wire:click="resetFilters" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-2xl transition">
                        <span>{{ __('audit.Reset form') }}</span>
                    </button>
                @endif
                <button type="button" @click="isOpen = !isOpen" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-600 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-sm transition">
                    <span>{{ __('audit.Advanced Search') }}</span>
                </button>
            </div>
        </div>

        <div class="mb-5">
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
                                            $displayNewValues = $record->attributesToArray();
                                        }
                                    } catch (\Throwable $e) {}
                                }
                            }

                            if (is_array($displayNewValues)) {
                                $displayNewValues = array_filter($displayNewValues, fn($val) => !is_array($val));
                            }
                            $displayOldValues = is_array($log->old_values) ? array_filter($log->old_values, fn($val) => !is_array($val)) : $log->old_values;

                            $jsonFormatted = json_encode([
                                'old_values' => $displayOldValues,
                                'new_values' => $displayNewValues,
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                        @endphp

                        <tr wire:key="{{ $log->id }}" x-data="{ showJson: false }" class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition">
                            <td class="px-6 py-4 font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                {{ $log->user->name ?? 'Sistemi / Publik' }}
                            </td>
                            <td class="px-6 py-4 font-bold text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                @if(str_contains($log->title, '[AI Assistant]'))
                                    <span class="px-2 py-0.5 text-[10px] font-black uppercase text-purple-700 bg-purple-100 dark:bg-purple-900/50 dark:text-purple-300 rounded-md mr-1">
                                        🤖 AI
                                    </span>
                                @endif
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
                                <button @click="showJson = true"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition text-xs font-bold font-mono">
                                    <x-heroicon-o-code-bracket class="w-4 h-4 text-indigo-500" />
                                    <span>JSON Diff</span>
                                </button>

                                <!-- Fixed Centered Modal Overlay -->
                                <template x-teleport="body">
                                    <div x-show="showJson" x-cloak
                                         @keydown.escape.window="showJson = false"
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-150"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">

                                        <div @click.away="showJson = false"
                                             class="w-full max-w-2xl bg-gray-900 text-emerald-400 border border-gray-700 rounded-3xl shadow-2xl overflow-hidden font-mono text-xs text-left">

                                            <div class="flex items-center justify-between px-6 py-4 bg-gray-800/80 border-b border-gray-700">
                                                <div class="flex items-center gap-3">
                                                    <span class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse"></span>
                                                    <span class="text-white font-extrabold text-sm">JSON Payload (#{{ $log->reference_id ?? 'N/A' }})</span>
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider text-amber-300 bg-amber-500/20">
                                                        {{ $log->type }}
                                                    </span>
                                                </div>
                                                <button @click="showJson = false" class="text-gray-400 hover:text-white font-bold text-lg px-2 py-1 rounded-lg transition">
                                                    ✕
                                                </button>
                                            </div>

                                            <div class="p-6 max-h-[65vh] overflow-y-auto leading-relaxed">
                                                <pre class="whitespace-pre-wrap break-all">{{ $jsonFormatted }}</pre>
                                            </div>

                                            <div class="flex items-center justify-between px-6 py-3 bg-gray-800/50 border-t border-gray-800">
                                                <span class="text-[11px] text-gray-400 font-sans">Aksioni: <strong>{{ $log->title }}</strong></span>
                                                <button @click="showJson = false" class="px-5 py-2 rounded-xl bg-gray-700 hover:bg-gray-600 text-white font-bold text-xs transition">
                                                    Mbyll (ESC)
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
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
