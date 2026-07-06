<x-app-layout>
    <div class="c-page-header">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <h1>Manajemen Pengguna</h1>
                <p>Kelola verifikasi pendaftaran akun dan pembagian hak akses (role)</p>
            </div>
        </div>
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('users.index') }}" id="user-filter-form"
          style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 24px; background: #fff; border: 1px solid #e0e0e0; padding: 16px;">
        
        <div style="flex: 1; min-width: 200px;">
            <label style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">Pencarian</label>
            <input type="text"
                   id="search"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Nama atau email..."
                   style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit;"
                   onfocus="this.style.borderBottom='2px solid #ff0d00'"
                   onblur="this.style.borderBottom='1px solid #000'">
        </div>

        <div style="min-width: 160px;">
            <label style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">Status</label>
            <select id="status" name="status"
                    style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit; cursor: pointer;">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
            </select>
        </div>

        <div style="min-width: 160px;">
            <label style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">Role</label>
            <select id="role" name="role"
                    style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit; cursor: pointer;">
                <option value="">Semua Role</option>
                @foreach($roles as $r)
                    <option value="{{ $r->name }}" {{ request('role') === $r->name ? 'selected' : '' }}>{{ $r->name }}</option>
                @endforeach
            </select>
        </div>

        <div style="display: flex; align-items: flex-end; gap: 8px;">
            <button type="submit" id="btn-filter"
                    style="padding: 12px 16px; background: #000; color: #fff; border: none; font-size: 14px; letter-spacing: 0.16px; cursor: pointer; font-family: inherit;"
                    onmouseover="this.style.background='#262626'"
                    onmouseout="this.style.background='#000'">
                Filter
            </button>
            @if(request()->hasAny(['search','status','role']))
                <a href="{{ route('users.index') }}"
                   style="padding: 12px 16px; background: #f4f4f4; color: #4d4d4d; font-size: 14px; text-decoration: none; letter-spacing: 0.16px; border: 1px solid #e0e0e0; display: inline-flex; align-items: center;"
                   onmouseover="this.style.background='#e0e0e0'"
                   onmouseout="this.style.background='#f4f4f4'">
                    Reset
                </a>
            @endif
        </div>
    </form>

    {{-- Users Table --}}
    <div style="background: #fff; border: 1px solid #e0e0e0;">
        @if($users->isEmpty())
            <div style="padding: 48px; text-align: center; color: #8c8c8c; font-size: 14px;">
                Tidak ada pengguna yang ditemukan.
            </div>
        @else
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="background: #f4f4f4; border-bottom: 1px solid #e0e0e0;">
                        <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Nama</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Email</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Status</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Role</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Tgl Daftar</th>
                        <th style="padding: 12px 16px; text-align: right; font-weight: 600; color: #000; letter-spacing: 0.16px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr style="border-bottom: 1px solid #e0e0e0;" onmouseover="this.style.background='#f4f4f4'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 12px 16px; font-weight: 600; color: #000;">{{ $user->name }}</td>
                            <td style="padding: 12px 16px; color: #4d4d4d;">{{ $user->email }}</td>
                            <td style="padding: 12px 16px;">
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
                            <td style="padding: 12px 16px; color: #000; font-weight: 600;">
                                {{ $user->roles->pluck('name')->join(', ') ?: '—' }}
                            </td>
                            <td style="padding: 12px 16px; color: #4d4d4d;">
                                {{ $user->created_at->format('d M Y') }}
                            </td>
                            <td style="padding: 12px 16px; text-align: right;">
                                <div style="display: inline-flex; gap: 8px;">
                                    @if($user->isPending())
                                        <form method="POST" action="{{ route('users.approve', $user) }}" style="margin:0;">
                                            @csrf
                                            <button type="submit" class="btn-approve"
                                                    style="padding: 6px 12px; background: #24a148; color: #fff; border: none; font-size: 12px; cursor: pointer; font-family: inherit;">
                                                Setujui
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('users.reject', $user) }}" style="margin:0;">
                                            @csrf
                                            <button type="submit" class="btn-reject"
                                                    style="padding: 6px 12px; background: #da1e28; color: #fff; border: none; font-size: 12px; cursor: pointer; font-family: inherit;">
                                                Tolak
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('users.show', $user) }}"
                                       class="c-btn-action-primary">
                                        Detail / Ubah Role
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            @if($users->hasPages())
                <div style="padding: 16px; border-top: 1px solid #e0e0e0; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 12px; color: #4d4d4d;">
                        Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} pengguna
                    </span>
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
