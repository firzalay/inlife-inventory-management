@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-xs font-normal mb-1']) }}
    style="color: #4d4d4d; letter-spacing: 0.32px;">
    {{ $value ?? $slot }}
</label>
