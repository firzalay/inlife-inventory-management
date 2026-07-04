<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-3 text-sm font-normal text-white transition-colors duration-150 w-full']) }}
    style="background-color: #ff0d00; letter-spacing: 0.16px; border-radius: 0;"
    onmouseover="this.style.backgroundColor='#d90b00'"
    onmouseout="this.style.backgroundColor='#ff0d00'">
    {{ $slot }}
</button>
