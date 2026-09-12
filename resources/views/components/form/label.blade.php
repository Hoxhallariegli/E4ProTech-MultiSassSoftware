@props([
    'label' => '',
    'name' => '',
    'required' => ''
])
<label aria-label="{{ $label }}" for="{{ $name }}" {{ $attributes->merge(['class' => 'block mb-2 text-[11px] font-bold text-gray-900 dark:text-gray-100 uppercase tracking-widest ml-1']) }}>
    {{ $label }}
    @if ($required != '') <span class="text-red-500">*</span> @endif
</label>
