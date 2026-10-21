<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <x-h1>{{ __('roles.Roles') }}</x-h1>
            <x-short-description class="dark:text-gray-400">{{ __('roles.description') }}</x-short-description>
        </div>
        <div>
            @can('add_roles')
                <livewire:admin.roles.create @added="$refresh"/>
            @endcan
        </div>
    </div>

    @include('errors.messages')

    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 border border-gray-100 dark:border-gray-700 shadow-sm space-y-6">

        <x-alert>
            <x-heroicon-c-information-circle class="size-6 sm:size-5 mr-2 sm:mr-1.5 flex-shrink-0" />
            <span class="flex-1">
                {{ __("roles.By default, only admin roles have full access. Additional roles will need permissions applied by editing the roles below.") }}
            </span>
        </x-alert>

        <div class="max-w-xl">
            <x-form.input type="search" id="roles" name="name" wire:model.live="name" label="none" :placeholder="__('roles.Search Roles')" class="dark:bg-gray-900" />
        </div>

        <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700/80">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-100/70 dark:bg-gray-700/70 hidden lg:table-header-group border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        <a class="link" href="#" wire:click.prevent="sortBy('label')">{{ __('roles.Name') }}</a>
                    </th>
                    <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ __('roles.Team') ?? __('Team') }}
                    </th>
                    <th class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ __('roles.Permissions') }}
                    </th>
                    <th class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ __('roles.Guard') }}
                    </th>
                    <th class="px-6 py-4 text-right text-[10px] font-black uppercase tracking-wider text-gray-400">
                        {{ __('roles.Action') }}
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700/80">
                @foreach($this->roles() as $role)
                    <livewire:admin.roles.row :$role :key="$role->id" @delete="deleteRole('{{ $role->id }}')" />
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $this->roles()->links() }}
        </div>

    </div>

</div>
