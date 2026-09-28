@use(App\Models\Permission)
<div>
    <p class="mb-5">
        <x-a class="link" href="{{ route('admin.settings.roles.index') }}">{{ __('roles.Roles') }}</x-a>
        <span class="dark:text-gray-200">- {{ __('roles.Edit Role') }}</span>
    </p>

    <div class="float-right">
        <span class="error">*</span>
        <span class="dark:text-gray-200"> = {{ __('users.required') }}</span>
    </div>

    <div class="clearfix"></div>

    <x-form wire:submit="update" method="put">

        <div class="row">

            <div class="md:w-1/2">
                @if ($role->name === 'admin')
                    <x-form.input wire:model="label" :label="__('roles.Role')" name='label' disabled />
                @else
                    <x-form.input wire:model="label" :label="__('roles.Role')" name='label' required />
                @endif
            </div>

        </div>

        @if ($role->name !== 'admin')

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($modules as $module)
                @php
                    $modulePermissions = \App\Models\Permission::where('module', $module)->orderBy('name')->get();
                    if (!auth()->user()->is_global_admin) {
                        $userPerms = auth()->user()->getAllPermissions()->pluck('name')->toArray();
                        $modulePermissions = $modulePermissions->filter(fn($p) => in_array($p->name, $userPerms));
                    }
                @endphp

                @if($modulePermissions->isNotEmpty())
                    <div class="card relative">
                        <h3>{{ str_replace('_', ' ', $module) }}</h3>
                        @foreach ($modulePermissions as $perm)
                            <label class="block cursor-pointer">
                                <div class="flex gap-2">
                                <input
                                    type="checkbox"
                                    class="module-checkbox"
                                    wire:model="permissions"
                                    value="{{ $perm->name }}"
                                >
                                    {{ $perm->label }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif
            @endforeach
            </div>

        @endif

        <x-button class="mt-5">{{ __('roles.Update Role') }}</x-button>

    </x-form>

</div>

