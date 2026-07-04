<x-guest-layout>
    {{-- Page heading --}}
    <h1 style="font-size: 24px; font-weight: 300; color: #000000; letter-spacing: 0; line-height: 1.33; margin: 0 0 8px;">
        Sign in
    </h1>
    <p style="font-size: 14px; color: #4d4d4d; letter-spacing: 0.16px; margin: 0 0 32px;">
        InLife Inventory Management
    </p>

    {{-- Session Status --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Email --}}
        <div style="margin-bottom: 24px;">
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Password --}}
        <div style="margin-bottom: 16px;">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Remember Me --}}
        <div style="margin-bottom: 24px;">
            <label for="remember_me" style="display: inline-flex; align-items: center; cursor: pointer;">
                <input id="remember_me" type="checkbox" name="remember"
                    style="width: 16px; height: 16px; accent-color: #ff0d00; margin-right: 8px;">
                <span style="font-size: 14px; color: #4d4d4d; letter-spacing: 0.16px;">{{ __('Remember me') }}</span>
            </label>
        </div>

        {{-- Actions --}}
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <x-primary-button id="btn-login">
                {{ __('Sign in') }}
            </x-primary-button>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px;">
                <a href="{{ route('register') }}"
                    style="font-size: 14px; color: #ff0d00; letter-spacing: 0.16px; text-decoration: none;"
                    onmouseover="this.style.textDecoration='underline'"
                    onmouseout="this.style.textDecoration='none'">
                    {{ __('Create an account') }}
                </a>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                        style="font-size: 14px; color: #4d4d4d; letter-spacing: 0.16px; text-decoration: none;"
                        onmouseover="this.style.color='#000000'"
                        onmouseout="this.style.color='#4d4d4d'">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>
        </div>
    </form>
</x-guest-layout>
