<x-guest-layout>
    <h1 style="font-size: 24px; font-weight: 300; color: #000000; letter-spacing: 0; line-height: 1.33; margin: 0 0 8px;">
        Create account
    </h1>
    <p style="font-size: 14px; color: #4d4d4d; letter-spacing: 0.16px; margin: 0 0 32px;">
        InLife Inventory Management
    </p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        {{-- Name --}}
        <div style="margin-bottom: 24px;">
            <x-input-label for="name" :value="__('Full name')" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        {{-- Email --}}
        <div style="margin-bottom: 24px;">
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Password --}}
        <div style="margin-bottom: 24px;">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Confirm Password --}}
        <div style="margin-bottom: 32px;">
            <x-input-label for="password_confirmation" :value="__('Confirm password')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        {{-- Actions --}}
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <x-primary-button id="btn-register">
                {{ __('Create account') }}
            </x-primary-button>

            <div style="text-align: center; margin-top: 8px;">
                <a href="{{ route('login') }}"
                    style="font-size: 14px; color: #ff0d00; letter-spacing: 0.16px; text-decoration: none;"
                    onmouseover="this.style.textDecoration='underline'"
                    onmouseout="this.style.textDecoration='none'">
                    {{ __('Already have an account? Sign in') }}
                </a>
            </div>
        </div>
    </form>
</x-guest-layout>
