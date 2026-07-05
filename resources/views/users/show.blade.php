<x-app-layout>
    <div class="c-page-header">
        <div style="margin-bottom: 8px;">
            <a href="{{ route('users.index') }}"
               style="font-size: 14px; color: #4d4d4d; text-decoration: none;"
               onmouseover="this.style.color='#000'"
               onmouseout="this.style.color='#4d4d4d'">
                ← Kembali ke Manajemen Pengguna
            </a>
        </div>
        <h1>Detail Pengguna: {{ $user->name }}</h1>
        <p>Lihat detail akun dan atur role / status persetujuan</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start;">
        {{-- Detail Card --}}
        <div style="background: #fff; border: 1px solid #e0e0e0; padding: 32px;">
            <h3 style="font-size: 16px; font-weight: 600; color: #000; margin: 0 0 20px 0; border-bottom: 1px solid #e0e0e0; padding-bottom: 8px;">
                Informasi Akun
            </h3>

            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <tr>
                    <td style="padding: 10px 0; color: #8c8c8c; width: 35%;">Nama Lengkap</td>
                    <td style="padding: 10px 0; font-weight: 600; color: #000;">{{ $user->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; color: #8c8c8c;">Alamat Email</td>
                    <td style="padding: 10px 0; color: #000;">{{ $user->email }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; color: #8c8c8c;">Status Akun</td>
                    <td style="padding: 10px 0;">
                        @if($user->isPending())
                            <span style="display: inline-block; padding: 2px 8px; font-size: 11px; font-weight: 600; background: #fdf6dd; color: #8e6a00;">
                                Menunggu Persetujuan
                            </span>
                        @elseif($user->isApproved())
                            <span style="display: inline-block; padding: 2px 8px; font-size: 11px; font-weight: 600; background: #defbe6; color: #0e6027;">
                                Disetujui
                            </span>
                        @elseif($user->isRejected())
                            <span style="display: inline-block; padding: 2px 8px; font-size: 11px; font-weight: 600; background: #fff1f1; color: #da1e28;">
                                Ditolak
                            </span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; color: #8c8c8c;">Tanggal Terdaftar</td>
                    <td style="padding: 10px 0; color: #4d4d4d;">{{ $user->created_at->format('d M Y H:i') }}</td>
                </tr>
                @if($user->isApproved())
                    <tr>
                        <td style="padding: 10px 0; color: #8c8c8c;">Disetujui Oleh</td>
                        <td style="padding: 10px 0; color: #4d4d4d;">
                            {{ $user->approvedBy?->name ?? 'Sistem' }} ({{ $user->approved_at?->format('d M Y H:i') ?? '—' }})
                        </td>
                    </tr>
                @endif
            </table>

            {{-- Approval / Rejection actions --}}
            @if($user->isPending())
                <div style="margin-top: 32px; padding-top: 20px; border-top: 1px solid #e0e0e0; display: flex; gap: 12px;">
                    <form method="POST" action="{{ route('users.approve', $user) }}" style="margin:0; flex: 1;">
                        @csrf
                        <button type="submit"
                                style="width: 100%; padding: 12px; background: #24a148; color: #fff; border: none; font-size: 14px; cursor: pointer; font-family: inherit; font-weight: 600;"
                                onmouseover="this.style.background='#1e843c'"
                                onmouseout="this.style.background='#24a148'">
                            Setujui Pendaftaran
                        </button>
                    </form>
                    <form method="POST" action="{{ route('users.reject', $user) }}" style="margin:0; flex: 1;">
                        @csrf
                        <button type="submit"
                                style="width: 100%; padding: 12px; background: #da1e28; color: #fff; border: none; font-size: 14px; cursor: pointer; font-family: inherit; font-weight: 600;"
                                onmouseover="this.style.background='#b21820'"
                                onmouseout="this.style.background='#da1e28'">
                            Tolak Pendaftaran
                        </button>
                    </form>
                </div>
            @endif
        </div>

        {{-- Role Settings Card --}}
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <div style="background: #fff; border: 1px solid #e0e0e0; padding: 32px;">
                <h3 style="font-size: 16px; font-weight: 600; color: #000; margin: 0 0 20px 0; border-bottom: 1px solid #e0e0e0; padding-bottom: 8px;">
                    Pengaturan Hak Akses (Role)
                </h3>

                <form method="POST" action="{{ route('users.role.update', $user) }}">
                    @csrf
                    @method('PATCH')

                    <div style="margin-bottom: 24px;">
                        <label for="role" style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 8px;">
                            Pilih Role Pengguna
                        </label>
                        <select id="role" name="role"
                                style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; outline: none; font-family: inherit; cursor: pointer;">
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <p style="margin: 4px 0 0; font-size: 12px; color: #da1e28;">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            style="padding: 12px 24px; background: #000; color: #fff; border: none; font-size: 14px; cursor: pointer; font-family: inherit;"
                            onmouseover="this.style.background='#262626'"
                            onmouseout="this.style.background='#000'">
                        Perbarui Role
                    </button>
                </form>
            </div>

            {{-- Danger zone --}}
            <div style="background: #fff; border: 1px solid #da1e28; padding: 32px;">
                <h3 style="font-size: 16px; font-weight: 600; color: #da1e28; margin: 0 0 12px 0;">
                    Hapus Pengguna
                </h3>
                <p style="font-size: 13px; color: #4d4d4d; margin: 0 0 20px 0; line-height: 1.4;">
                    Menghapus akun pengguna ini secara permanen dari sistem. Tindakan ini tidak dapat dibatalkan.
                </p>

                <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini secara permanen?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            style="padding: 12px 24px; background: #da1e28; color: #fff; border: none; font-size: 14px; cursor: pointer; font-family: inherit; font-weight: 600;"
                            onmouseover="this.style.background='#b21820'"
                            onmouseout="this.style.background='#da1e28'">
                        Hapus Akun Permanen
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
