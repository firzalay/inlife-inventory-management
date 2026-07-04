<x-guest-layout>
    <h1 style="font-size: 24px; font-weight: 300; color: #000000; letter-spacing: 0; line-height: 1.33; margin: 0 0 8px;">
        Reset password
    </h1>
    <p style="font-size: 14px; color: #4d4d4d; letter-spacing: 0.16px; margin: 0 0 32px;">
        {{ __('Forgot your password? Enter your email and we will send you a reset link.') }}
    </p>

    {{-- Session Status --}}
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div style="margin-bottom: 32px;">
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button id="btn-forgot-password">
            {{ __('Send password reset link') }}
        </x-primary-button>

        <div style="margin-top: 16px; text-align: center;">
            <a href="{{ route('login') }}"
                style="font-size: 14px; color: #4d4d4d; letter-spacing: 0.16px; text-decoration: none;"
                onmouseover="this.style.color='#000000'"
                onmouseout="this.style.color='#4d4d4d'">
                {{ __('Back to sign in') }}
            </a>
        </div>
    </form>
</x-guest-layout>
