<x-guest-layout>
    <div style="text-align: center; padding: 24px 0;">
        <div style="font-size: 48px; color: #ff0d00; margin-bottom: 24px; font-weight: 300;">&#8987;</div>

        <h1 style="font-size: 24px; font-weight: 300; color: #000000; letter-spacing: 0; line-height: 1.33; margin: 0 0 16px;">
            Menunggu Persetujuan
        </h1>

        <p style="font-size: 14px; color: #4d4d4d; letter-spacing: 0.16px; line-height: 1.5; margin: 0 0 32px;">
            Akun kamu berhasil dibuat dan sedang menunggu persetujuan Admin.
        </p>

        <a href="{{ route('login') }}"
           style="display: block; width: 100%; padding: 12px; background: #000; color: #fff; font-size: 14px; text-decoration: none; text-align: center; letter-spacing: 0.16px;"
           onmouseover="this.style.background='#262626'"
           onmouseout="this.style.background='#000'">
            Kembali ke Halaman Login
        </a>
    </div>
</x-guest-layout>
