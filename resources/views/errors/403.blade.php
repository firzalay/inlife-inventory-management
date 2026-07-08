<x-guest-layout>
    <x-slot name="title">Akses Ditolak</x-slot>

    <div style="text-align: center;">
        <span style="font-size: 48px; color: var(--color-primary); font-weight: 600; display: block;">403</span>
        <h2 style="font-size: 20px; font-weight: 600; color: var(--color-ink); margin-top: 16px; margin-bottom: 8px; letter-spacing: 0.16px;">
            Akses Ditolak
        </h2>
        <p style="font-size: 14px; color: var(--color-ink-muted); margin-bottom: 32px; line-height: 1.5;">
            Akun Anda saat ini belum memiliki hak akses (role) yang tepat untuk melihat halaman ini. Silakan hubungi administrator untuk menyetujui akun Anda atau memberikan role yang sesuai.
        </p>

        <div style="display: flex; flex-direction: column; gap: 8px;">
            <a href="/" style="display: block; width: 100%; text-align: center; background-color: var(--color-primary); color: #ffffff; font-weight: 600; padding: 12px; font-size: 14px; text-decoration: none; border: 1px solid var(--color-primary); cursor: pointer; transition: background-color 0.2s; box-sizing: border-box;" 
               onmouseover="this.style.backgroundColor='var(--color-red-hover)'" 
               onmouseout="this.style.backgroundColor='var(--color-primary)'">
                Kembali ke Beranda
            </a>

            <form method="POST" action="{{ route('logout') }}" style="width: 100%; margin: 0;">
                @csrf
                <button type="submit" style="width: 100%; background-color: transparent; color: var(--color-ink); font-weight: 600; padding: 12px; font-size: 14px; border: 1px solid var(--color-hairline-strong); cursor: pointer; transition: background-color 0.2s; box-sizing: border-box;"
                        onmouseover="this.style.backgroundColor='var(--color-surface-1)'" 
                        onmouseout="this.style.backgroundColor='transparent'">
                    Keluar (Logout)
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
