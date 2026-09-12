@props([
    'label' => '',
    'id' => 'file-upload',
    'isEditing' => false,
])

<div class="space-y-2">
    @if($label)
        <label for="{{ $id }}" class="block mb-2 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100">
            {{ $label }}
        </label>
    @endif

    <div
        x-data="{ isUploading: false, progress: 0 }"
        x-on:livewire-upload-start="isUploading = true"
        x-on:livewire-upload-finish="isUploading = false"
        x-on:livewire-upload-error="isUploading = false"
        x-on:livewire-upload-progress="progress = $event.detail.progress"
        class="relative"
    >
        <label class="flex flex-col items-center justify-center w-full h-32 sm:h-40 border-2 border-dashed {{ $errors->has($attributes->get('wire:model')) ? 'border-red-300 bg-red-50/30' : 'border-gray-200' }} dark:border-gray-700 rounded-[2rem] cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all group">
            <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4">
                <div class="p-2 sm:p-3 rounded-full bg-blue-50 dark:bg-blue-900/20 text-blue-500 mb-2 group-hover:scale-110 transition-transform">
                    <x-heroicon-o-cloud-arrow-up class="w-6 h-6 sm:w-8 h-8" />
                </div>
                <p class="text-[10px] sm:text-xs text-gray-700 dark:text-gray-300 font-bold">
                    {{ $isEditing ? __('admin.Click to replace current file') : __('admin.Click or Drag') }}
                </p>
                <p class="text-[8px] sm:text-[9px] text-gray-400 uppercase mt-1">Max 15MB</p>
            </div>
            <input type="file" {{ $attributes }} class="hidden" id="{{ $id }}" />
        </label>

        @error($attributes->get('wire:model')) <p class="text-[9px] text-red-500 font-bold uppercase mt-2">{{ $message }}</p> @enderror

        <!-- Progress Bar -->
        <div x-show="isUploading" class="mt-4 px-2">
            <div class="flex items-center justify-between mb-1">
                <span class="text-[9px] font-black text-blue-600 uppercase">{{ __('admin.Uploading...') }}</span>
                <span class="text-[9px] font-black text-blue-600" x-text="progress + '%'"></span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-1.5 dark:bg-gray-700">
                <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-300 shadow-sm shadow-blue-500/50" :style="'width: ' + progress + '%'"></div>
            </div>
        </div>
    </div>

    {{ $slot }}
</div>
