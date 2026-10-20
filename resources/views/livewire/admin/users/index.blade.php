<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <x-h1>{{ __('users.Users') }}</x-h1>
            <x-short-description class="dark:text-gray-400">Menaxhoni përdoruesit dhe anëtarët e ekipit tuaj</x-short-description>
        </div>
        <div>
            @can('add_users')
                <livewire:admin.users.invite/>
            @endcan
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 border border-gray-100 dark:border-gray-700 shadow-sm space-y-6">

        <!-- Search & Filter Controls -->
        <div class="space-y-4">
            <div class="max-w-xl">
                <x-form.input type="search" name="name" wire:model.live="name" label="none" :placeholder="__('users.Search Users')" class="dark:bg-gray-900" />
            </div>

            <div x-data="{ isOpen: @if($openFilter || request('openFilter')) true @else false @endif }">
                <div class="flex items-center gap-3">
                    <button type="button" @click="isOpen = !isOpen" class="inline-flex items-center gap-2 px-4 py-2.5 border border-gray-200 dark:border-gray-700 text-xs font-extrabold rounded-2xl text-gray-700 bg-gray-50 hover:bg-gray-100 dark:bg-gray-900 dark:text-gray-200 transition shadow-xs">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-500 dark:text-gray-300" />
                        <span>{{ __('users.Advanced Search') }}</span>
                    </button>

                    <button type="button" wire:click="resetFilters" @click="isOpen = false" class="inline-flex items-center gap-2 px-4 py-2.5 border border-gray-200 dark:border-gray-700 text-xs font-extrabold rounded-2xl text-gray-700 bg-gray-50 hover:bg-gray-100 dark:bg-gray-900 dark:text-gray-200 transition shadow-xs">
                        <x-heroicon-o-arrow-path class="w-4 h-4 text-gray-500 dark:text-gray-300" />
                        <span>{{ __('users.Reset form') }}</span>
                    </button>
                </div>

                <div x-show="isOpen" x-cloak x-transition class="mt-4 p-5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl" wire:ignore.self>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        <x-form.input type="email" id="email" name="email" :label="__('users.Email')" wire:model.live="email" class="dark:bg-gray-800" />
                        <x-form.daterange id="joined" name="joined" :label="__('users.Joined Date Range')" wire:model.blur="joined" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700/80">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-100/70 dark:bg-gray-700/70 hidden lg:table-header-group border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            <a href="#" wire:click="sortBy('name')" class="flex items-center gap-1">{{ __('users.Name') }}</a>
                        </th>
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            <a href="#" wire:click="sortBy('email')" class="flex items-center gap-1">{{ __('users.Email') }}</a>
                        </th>
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            <a href="#" wire:click="sortBy('email_verified_at')" class="flex items-center gap-1">{{ __('Verifikimi i Llogarisë') }}</a>
                        </th>
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Shops') }}</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('users.Joined') }}</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('users.Roles') }}</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('users.Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700/80">
                @foreach($this->users() as $user)
                    <!-- Mobile & Tablet Compact Card Row (< lg) -->
                    <tr wire:key="m-{{ $user->id }}" class="lg:hidden border-b border-gray-200 dark:border-gray-700/80 last:border-none hover:bg-gray-50/80 dark:hover:bg-gray-900/60 transition-colors">
                        <td colspan="100" class="p-4 space-y-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    @if (storage_exists($user->image))
                                        <img src="{{ storage_url($user->image) }}" alt="{{ $user->name }}" class="h-10 w-10 rounded-full object-cover shrink-0 border border-gray-200 dark:border-gray-700">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300 font-extrabold text-sm flex items-center justify-center shrink-0 border border-blue-100 dark:border-blue-800">
                                            {{ mb_substr($user->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-extrabold text-xs text-gray-900 dark:text-white">{{ $user->name }}</div>
                                        <div class="text-[11px] text-gray-500 font-mono">{{ $user->email }}</div>
                                    </div>
                                </div>

                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase shrink-0 {{ $user->email_verified_at ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' }}">
                                    {{ $user->email_verified_at ? 'Verifikuar' : 'Pa verifikuar' }}
                                </span>
                            </div>

                            <div class="flex items-center gap-1.5 flex-wrap pt-1">
                                @foreach($user->barberShops as $shop)
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $shop->name }}
                                    </span>
                                @endforeach
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800 text-[10px]">
                                <span class="text-gray-400 font-mono">Pranuar: {{ $user->created_at ? date('d/m/Y', strtotime((string)$user->created_at)) : '-' }}</span>
                                <div class="flex items-center gap-2">
                                    @can('view_users_profiles')
                                        <x-a href="{{ route('admin.users.show', $user) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-3 !py-1 !text-[10px] !font-black !uppercase !border-none">{{ __('users.Profile') }}</x-a>
                                    @endcan
                                    @if(can('edit_users') || (auth()->id() === $user->id && can('edit_own_account')))
                                        <x-a href="{{ route('admin.users.edit', $user) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-3 !py-1 !text-[10px] !font-black !uppercase !border-none">{{ __('admin.Edit') }}</x-a>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- Desktop Table Row (>= lg) -->
                    <tr wire:key="d-{{ $user->id }}" class="hidden lg:table-row border-b border-gray-200 dark:border-gray-700/80 last:border-none hover:bg-gray-50/80 dark:hover:bg-gray-900/60 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if (storage_exists($user->image))
                                    <img src="{{ storage_url($user->image) }}" alt="{{ $user->name }}" class="h-9 w-9 rounded-full object-cover shrink-0 border border-gray-200 dark:border-gray-700">
                                @else
                                    <div class="h-9 w-9 rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300 font-black text-xs flex items-center justify-center shrink-0 border border-blue-100 dark:border-blue-800">
                                        {{ mb_substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                                <span class="font-extrabold text-xs text-gray-900 dark:text-white">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs font-mono text-gray-600 dark:text-gray-300">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            @if ($user->email_verified_at)
                                <div class="flex flex-col gap-0.5">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/40 px-2.5 py-0.5 rounded-md w-max">
                                        ✓ Verifikuar
                                    </span>
                                    <span class="text-[11px] font-mono text-gray-500 dark:text-gray-400">
                                        {{ date('d/m/Y H:i', strtotime((string)$user->email_verified_at)) }}
                                    </span>
                                </div>
                            @else
                                <div class="flex flex-col gap-0.5">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/40 px-2.5 py-0.5 rounded-md w-max">
                                        Pa Verifikuar
                                    </span>
                                    <span class="text-[10px] text-gray-400 italic">
                                        Në pritje konfirmimi
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($user->barberShops as $shop)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $shop->name }}
                                    </span>
                                @endforeach
                                @if($user->barberShops->isEmpty())
                                    <span class="text-gray-400 text-xs italic">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">
                            @if (! empty($user->invite_token))
                                <span class="dark:text-gray-300 block font-bold">{{ __('users.Invited by') }} {{ $user->invite?->name ?? 'System' }}</span>
                                <span class="text-gray-400 font-mono text-[11px]">{{ date('jS M Y H:i', strtotime($user->invited_at)) }}</span>
                            @else
                                <span class="font-mono">{{ $user->created_at ? date('jS M Y', strtotime((string)$user->created_at)) : '-' }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @php
                                    $allRoles = \Illuminate\Support\Facades\DB::table('model_has_roles')
                                        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                                        ->leftJoin('barber_shops', 'model_has_roles.barber_shop_id', '=', 'barber_shops.id')
                                        ->where('model_id', $user->id)
                                        ->select('roles.label', 'barber_shops.name as shop_name', 'model_has_roles.barber_shop_id')
                                        ->get();
                                @endphp

                                @foreach($allRoles as $role)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 mr-1 mb-1">
                                        {{ $role->label }}
                                    </span>
                                @endforeach

                                @if($allRoles->isEmpty())
                                    <span class="text-gray-400 text-xs italic">No roles</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @can('view_users_profiles')
                                    <x-a href="{{ route('admin.users.show', $user) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-3 !py-1 !text-[10px] !font-black !uppercase !border-none">{{ __('users.Profile') }}</x-a>
                                @endcan

                                @if(can('edit_users') || (auth()->id() === $user->id && can('edit_own_account')))
                                    <x-a href="{{ route('admin.users.edit', $user) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-3 !py-1 !text-[10px] !font-black !uppercase !border-none">{{ __('admin.Edit') }}</x-a>
                                @endif

                                @if(can('add_users') && !empty($user->invite_token))
                                    <x-modal>
                                        <x-slot name="trigger">
                                            <a href="#" @click="on = true" class="text-[10px] font-black uppercase text-blue-600 hover:underline">{{ __('users.Resend Invite') }}</a>
                                        </x-slot>

                                        @if($sentEmail === false)
                                            <x-slot name="modalTitle">Send {{ $user->name }} {{ __('users.another invite email') }}.</x-slot>
                                            <x-slot name="content"></x-slot>
                                            <x-slot name="footer">
                                                <x-button variant="gray" @click="on = false">{{ __('users.Cancel') }}</x-button>
                                                <x-button wire:click="resendInvite('{{ $user->id }}')">{{ __('users.Yes, Send Email') }}</x-button>
                                            </x-slot>
                                        @else
                                            <x-slot name="modalTitle">{{ __('users.Invite email sent') }}</x-slot>
                                            <x-slot name="content"></x-slot>
                                            <x-slot name="footer">
                                                <x-button variant="gray" @click="on = false">{{ __('users.Close') }}</x-button>
                                            </x-slot>
                                        @endif
                                    </x-modal>
                                @endif

                                @if(can('delete_users') && auth()->id() !== $user->id)
                                    <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                                        <x-modal>
                                            <x-slot name="trigger">
                                                <button @click="on = true" class="text-[10px] font-black uppercase text-red-500 hover:text-red-700">{{ __('admin.Delete') }}</button>
                                            </x-slot>

                                            <x-slot name="modalTitle">
                                                <div class="py-2 text-lg font-black uppercase tracking-tighter text-gray-900 dark:text-white whitespace-normal text-left">
                                                    {{ __('users.Are you sure you want to delete') }}: <span class="text-red-600">{{ $user->name }}</span>?
                                                </div>
                                            </x-slot>

                                            <x-slot name="content">
                                                <div class="space-y-4 text-left whitespace-normal">
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                                        {{ __('users.Delete Warning') }}
                                                    </p>
                                                    <div class="flex flex-col gap-2">
                                                        <div class="text-xs text-gray-600 dark:text-gray-400">{{ __('users.Type the name') }} <span class="font-bold text-red-600">"{{ $user->name }}"</span> {{ __('users.to confirm') }}</div>
                                                        <input x-model="confirmation" class="px-3 py-2 text-sm border border-slate-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white w-full focus:ring-2 focus:ring-red-500 outline-none">
                                                    </div>
                                                </div>
                                            </x-slot>

                                            <x-slot name="footer">
                                                <x-button variant="gray" @click="on = false">{{ __('admin.Cancel') }}</x-button>
                                                <x-button variant="red" x-bind:disabled="confirmation !== '{{ $user->name }}'" wire:click="deleteUser('{{ $user->id }}')">
                                                    {{ __('admin.Delete') }}
                                                </x-button>
                                            </x-slot>
                                        </x-modal>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $this->users()->links() }}
        </div>
    </div>
</div>
