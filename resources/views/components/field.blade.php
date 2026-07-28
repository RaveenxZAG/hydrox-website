@props(['label', 'name'])
<label {{ $attributes->merge(['class' => 'grid gap-1.5']) }}>
    <span class="label">{{ $label }}</span>
    {{ $slot }}
    @error($name)
        <span class="text-xs font-medium text-rose-600">{{ $message }}</span>
    @enderror
</label>
