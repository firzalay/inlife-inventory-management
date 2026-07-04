@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'block w-full text-sm']) }}
    style="background-color: #f4f4f4; color: #000000; border: none; border-bottom: 1px solid #000000; padding: 11px 16px; letter-spacing: 0.16px; outline: none; border-radius: 0; transition: border-bottom 0.15s;"
    onfocus="this.style.borderBottom='2px solid #ff0d00'"
    onblur="this.style.borderBottom='1px solid #000000'">
