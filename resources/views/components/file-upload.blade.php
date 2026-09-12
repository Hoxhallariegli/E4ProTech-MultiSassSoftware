@props([
    'label' => '',
    'id' => 'file-upload',
    'isEditing' => false,
    'preview' => null,
])

<div class="space-y-2.5" x-cloak>
    @if($label)
        <label
            for="{{ $id }}"
            class="block ml-1 text-[10px] font-black uppercase tracking-[0.18em] text-gray-400"
        >
            {{ $label }}
        </label>
    @endif

    <div
        x-data="{ isUploading: false, progress: 0 }"
        x-on:livewire-upload-start="isUploading = true"
        x-on:livewire-upload-finish="isUploading = false"
        x-on:livewire-upload-error="isUploading = false"
        x-on:livewire-upload-progress="progress = $event.detail.progress"
        class="flex items-center gap-3"
    >
        @php
            $modelName = $attributes->wire('model')->value();
            $tempFile = data_get($this, $modelName);

            $hasPreview = false;
            $previewUrl = '';

            // Temporary uploaded file
            if (
                $tempFile &&
                !is_string($tempFile) &&
                !is_array($tempFile) &&
                method_exists($tempFile, 'temporaryUrl')
            ) {
                try {
                    $previewUrl = $tempFile->temporaryUrl();
                    $hasPreview = true;
                } catch (\Throwable $e) {
                    $hasPreview = false;
                }
            }

            // Existing image
            elseif ($preview || (is_string($tempFile) && $tempFile !== '')) {
                $finalPreview = $preview ?: $tempFile;

                if ($finalPreview) {
                    $previewUrl =
                        str_starts_with($finalPreview, 'http') ||
                        str_starts_with($finalPreview, 'data:')
                            ? $finalPreview
                            : asset($finalPreview);

                    $hasPreview = true;
                }
            }
        @endphp

        {{-- Compact Preview --}}
        <div class="relative shrink-0 group">
            <div
                class="relative w-24 h-24 rounded-2xl overflow-hidden
                       bg-gray-50 dark:bg-gray-900
                       border border-gray-200 dark:border-gray-700
                       shadow-sm"
            >
                @if($hasPreview)
                    <a
                        href="{{ $previewUrl }}"
                        target="_blank"
                        rel="noopener"
                        class="block w-full h-full"
                    >
                        <img
                            src="{{ $previewUrl }}"
                            alt="{{ $label }}"
                            class="w-full h-full object-cover transition duration-300 group-hover:scale-105"
                        >

                        <div
                            class="absolute inset-0 bg-black/0 group-hover:bg-black/35
                                   transition-all flex items-center justify-center"
                        >
                            <x-heroicon-o-arrows-pointing-out
                                class="w-5 h-5 text-white opacity-0 group-hover:opacity-100 transition-opacity"
                            />
                        </div>
                    </a>
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-gray-300 dark:text-gray-600">
                        <x-heroicon-o-photo class="w-7 h-7" />
                    </div>
                @endif

                {{-- Upload progress --}}
                <div
                    x-show="isUploading"
                    x-cloak
                    class="absolute inset-0 bg-black/45 backdrop-blur-[2px]
                           flex items-center justify-center"
                >
                    <div class="relative w-10 h-10">
                        <svg class="w-10 h-10 -rotate-90" viewBox="0 0 36 36">
                            <circle
                                cx="18"
                                cy="18"
                                r="15.5"
                                fill="none"
                                stroke="currentColor"
                                class="text-white/20"
                                stroke-width="3"
                            />
                            <circle
                                cx="18"
                                cy="18"
                                r="15.5"
                                fill="none"
                                stroke="currentColor"
                                class="text-white"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-dasharray="100"
                                :stroke-dashoffset="100 - progress"
                            />
                        </svg>

                        <span
                            class="absolute inset-0 flex items-center justify-center
                                   text-[8px] font-black text-white"
                            x-text="progress + '%'"
                        ></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Compact Upload --}}
        <div class="flex-1 min-w-0">
            <label
                for="{{ $id }}"
                class="h-24 w-full flex items-center gap-3 px-4
                       border border-dashed
                       {{ $errors->has($attributes->get('wire:model'))
                            ? 'border-red-300 bg-red-50/30'
                            : 'border-gray-200 dark:border-gray-700 bg-gray-50/40 dark:bg-gray-900/40' }}
                       rounded-2xl cursor-pointer
                       hover:border-blue-400
                       hover:bg-blue-50/30
                       dark:hover:bg-blue-900/10
                       transition-all group"
            >
                <div
                    class="shrink-0 w-10 h-10 rounded-xl
                           bg-blue-50 dark:bg-blue-900/20
                           text-blue-500
                           flex items-center justify-center
                           group-hover:scale-105 transition-transform"
                >
                    <x-heroicon-o-cloud-arrow-up class="w-5 h-5" />
                </div>

                <div class="min-w-0">
                    <p
                        class="text-[10px] font-black uppercase tracking-wide
                               text-gray-700 dark:text-gray-200 truncate"
                    >
                        {{ $isEditing
                            ? __('admin.Click to replace current file')
                            : __('admin.Click or Drag to Upload') }}
                    </p>

                    <p class="mt-1 text-[9px] font-bold text-gray-400 uppercase">
                        Max 15MB
                    </p>

                    <div class="flex gap-1.5 mt-1.5">
                        <span class="text-[8px] font-black px-1.5 py-0.5 rounded-md bg-gray-200/70 dark:bg-gray-700 text-gray-500">
                            JPG
                        </span>
                        <span class="text-[8px] font-black px-1.5 py-0.5 rounded-md bg-gray-200/70 dark:bg-gray-700 text-gray-500">
                            PNG
                        </span>
                        <span class="text-[8px] font-black px-1.5 py-0.5 rounded-md bg-gray-200/70 dark:bg-gray-700 text-gray-500">
                            WEBP
                        </span>
                    </div>
                </div>

                <div class="ml-auto shrink-0 text-gray-300 group-hover:text-blue-500 transition-colors">
                    <x-heroicon-o-plus class="w-5 h-5" />
                </div>

                <input
                    type="file"
                    {{ $attributes }}
                    id="{{ $id }}"
                    class="hidden"
                />
            </label>

            @error($attributes->get('wire:model'))
                <p class="mt-1.5 ml-2 text-[9px] font-bold uppercase text-red-500">
                    {{ $message }}
                </p>
            @enderror
        </div>
    </div>

    {{ $slot }}
</div>
