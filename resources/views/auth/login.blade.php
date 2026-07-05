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

    @if (session('auth_status_error'))
        <div x-data="{ open: true }" x-show="open" style="position: fixed; inset: 0; z-index: 10000; background-color: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; padding: 16px;">
            <div style="background-color: #ffffff; border: 1px solid #000000; max-width: 400px; width: 100%; padding: 32px; box-sizing: border-box;">
                @if (session('auth_status_error') === 'pending')
                    <h2 style="font-size: 18px; font-weight: 600; color: #000000; margin: 0 0 12px 0;">Pendaftaran Menunggu Persetujuan</h2>
                    <p style="font-size: 14px; color: #4d4d4d; margin: 0 0 24px 0; line-height: 1.4;">
                        Akun Anda belum disetujui oleh Admin. Silakan tunggu konfirmasi.
                    </p>
                    <button type="button" @click="open = false" 
                            style="width: 100%; padding: 12px; background: #000000; color: #ffffff; border: none; font-size: 14px; cursor: pointer; letter-spacing: 0.16px;"
                            onmouseover="this.style.background='#262626'"
                            onmouseout="this.style.background='#000000'">
                        Tutup
                    </button>
                @elseif (session('auth_status_error') === 'rejected')
                    <h2 style="font-size: 18px; font-weight: 600; color: #000000; margin: 0 0 12px 0;">Pendaftaran Ditolak</h2>
                    <p style="font-size: 14px; color: #4d4d4d; margin: 0 0 24px 0; line-height: 1.4;">
                        Pendaftaran Anda ditolak. Hubungi Admin untuk informasi lebih lanjut.
                    </p>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" @click="open = false" 
                                style="flex: 1; padding: 12px; background: #f4f4f4; color: #000000; border: 1px solid #e0e0e0; font-size: 14px; cursor: pointer; letter-spacing: 0.16px;"
                                onmouseover="this.style.background='#e0e0e0'"
                                onmouseout="this.style.background='#f4f4f4'">
                            Tutup
                        </button>
                        <a href="mailto:admin@inlife-inventory.com" 
                           style="flex: 1; padding: 12px; background: #ff0d00; color: #ffffff; border: none; font-size: 14px; cursor: pointer; text-decoration: none; text-align: center; letter-spacing: 0.16px; font-weight: 600; display: inline-block; box-sizing: border-box;"
                           onmouseover="this.style.background='#d90b00'"
                           onmouseout="this.style.background='#ff0d00'">
                            Hubungi Admin
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif
</x-guest-layout>
