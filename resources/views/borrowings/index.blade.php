<x-app-layout>
    <div class="c-page-header">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <h1>Daftar Peminjaman</h1>
                <p>Riwayat dan status peminjaman barang inventaris</p>
            </div>
            @role('Admin|Staff')
            <a href="{{ route('borrowings.create') }}"
               id="btn-add-borrowing"
               style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 16px; background: #ff0d00; color: #fff; font-size: 14px; text-decoration: none; letter-spacing: 0.16px; white-space: nowrap;"
               onmouseover="this.style.background='#d90b00'"
               onmouseout="this.style.background='#ff0d00'">
                + Catat Peminjaman
            </a>
            @endrole
        </div>
    </div>

    {{-- Filter form --}}
    <form method="GET" action="{{ route('borrowings.index') }}" id="borrowing-filter-form"
          style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 24px; background: #fff; border: 1px solid #e0e0e0; padding: 16px;">
        
        <div style="flex: 1; min-width: 200px;">
            <label style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">Peminjam</label>
            <input type="text"
                   id="search"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Nama peminjam..."
                   style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit;"
                   onfocus="this.style.borderBottom='2px solid #ff0d00'"
                   onblur="this.style.borderBottom='1px solid #000'">
        </div>

        <div style="min-width: 160px;">
            <label style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">Status</label>
            <select id="status" name="status"
                    style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit; cursor: pointer;">
                <option value="">Semua Status</option>
                <option value="borrowed" {{ request('status') === 'borrowed' ? 'selected' : '' }}>Dipinjam</option>
                <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Dikembalikan</option>
                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Terlambat</option>
            </select>
        </div>

        <div style="min-width: 140px;">
            <label style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">Dari Tanggal</label>
            <input type="date"
                   id="start_date"
                   name="start_date"
                   value="{{ request('start_date') }}"
                   style="width: 100%; padding: 10px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit;">
        </div>

        <div style="min-width: 140px;">
            <label style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">Sampai Tanggal</label>
            <input type="date"
                   id="end_date"
                   name="end_date"
                   value="{{ request('end_date') }}"
                   style="width: 100%; padding: 10px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit;">
        </div>

        <div style="display: flex; align-items: flex-end; gap: 8px;">
            <button type="submit" id="btn-filter"
                    style="padding: 12px 16px; background: #000; color: #fff; border: none; font-size: 14px; letter-spacing: 0.16px; cursor: pointer; font-family: inherit;"
                    onmouseover="this.style.background='#262626'"
                    onmouseout="this.style.background='#000'">
                Filter
            </button>
            @if(request()->hasAny(['search','status','start_date','end_date']))
                <a href="{{ route('borrowings.index') }}"
                   style="padding: 12px 16px; background: #f4f4f4; color: #4d4d4d; font-size: 14px; text-decoration: none; letter-spacing: 0.16px; border: 1px solid #e0e0e0; display: inline-flex; align-items: center;"
                   onmouseover="this.style.background='#e0e0e0'"
                   onmouseout="this.style.background='#f4f4f4'">
                    Reset
                </a>
            @endif
            @role('Admin|Manager')
            <a href="{{ route('borrowings.export.pdf', request()->query()) }}"
               id="btn-export-pdf"
               style="padding: 12px 16px; background: #ff0d00; color: #fff; font-size: 14px; text-decoration: none; letter-spacing: 0.16px; display: inline-flex; align-items: center; border: none;"
               onmouseover="this.style.background='#d90b00'"
               onmouseout="this.style.background='#ff0d00'">
                Export PDF
            </a>
            @endrole
        </div>
    </form>

    {{-- Borrowings List Table --}}
    <div style="background: #fff; border: 1px solid #e0e0e0;">
        @if($borrowings->isEmpty())
            <div style="padding: 48px; text-align: center; color: #8c8c8c; font-size: 14px;">
                Tidak ada data peminjaman yang ditemukan.
            </div>
        @else
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="background: #f4f4f4; border-bottom: 1px solid #e0e0e0;">
                        <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Nama Peminjam</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Barang yang Dipinjam</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Tgl Pinjam</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Tgl Batas Kembali</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Tgl Kembali Aktual</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Kondisi Kembali</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Status</th>
                        <th style="padding: 12px 16px; text-align: right; font-weight: 600; color: #000; letter-spacing: 0.16px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($borrowings as $borrowing)
                        <tr style="border-bottom: 1px solid #e0e0e0;" onmouseover="this.style.background='#f4f4f4'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 12px 16px; font-weight: 600; color: #000;">{{ $borrowing->borrower_name }}</td>
                            <td style="padding: 12px 16px; color: #4d4d4d;">
                                <ul style="margin: 0; padding-left: 16px; list-style-type: square; font-size: 13px;">
                                    @foreach($borrowing->details as $detail)
                                        <li>{{ $detail->product?->name ?? 'Barang Terhapus' }} ({{ $detail->quantity }} unit)</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td style="padding: 12px 16px; color: #000;">{{ $borrowing->borrow_date->format('d M Y') }}</td>
                            <td style="padding: 12px 16px; color: #4d4d4d;">{{ $borrowing->due_date->format('d M Y') }}</td>
                            <td style="padding: 12px 16px; color: #4d4d4d;">
                                {{ $borrowing->return_date ? $borrowing->return_date->format('d M Y') : '—' }}
                            </td>
                            <td style="padding: 12px 16px; color: #4d4d4d;">
                                <ul style="margin: 0; padding-left: 16px; list-style-type: square; font-size: 13px;">
                                    @foreach($borrowing->details as $detail)
                                        <li>
                                            @if($borrowing->status === 'returned')
                                                @php
                                                    $cond = $detail->condition_on_return ?? 'Baik';
                                                    $color = match($cond) {
                                                        'Baik' => '#24a148',
                                                        'Rusak' => '#da1e28',
                                                        'Perlu Perbaikan' => '#f1c21b',
                                                        default => '#8c8c8c',
                                                    };
                                                @endphp
                                                <span style="color: {{ $color }}; font-weight: 600;">{{ $cond }}</span>
                                            @else
                                                <span style="color: #8c8c8c;">—</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td style="padding: 12px 16px;">
                                <x-borrowing-status-badge :status="$borrowing->computed_status" />
                            </td>
                            <td style="padding: 12px 16px; text-align: right;">
                                <a href="{{ route('borrowings.show', $borrowing) }}"
                                   style="font-size: 12px; color: #ff0d00; text-decoration: none; padding: 6px 12px; border: 1px solid #e0e0e0; background: #fff;"
                                   onmouseover="this.style.background='#ff0d00'; this.style.color='#fff';"
                                   onmouseout="this.style.background='#fff'; this.style.color='#ff0d00';">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            @if($borrowings->hasPages())
                <div style="padding: 16px; border-top: 1px solid #e0e0e0; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px;">
                        Menampilkan {{ $borrowings->firstItem() }}–{{ $borrowings->lastItem() }} dari {{ $borrowings->total() }} transaksi
                    </span>
                    <div>
                        {{ $borrowings->links() }}
                    </div>
                </div>
            @else
                <div style="padding: 12px 16px; border-top: 1px solid #e0e0e0;">
                    <span style="font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px;">
                        Total {{ $borrowings->total() }} transaksi
                    </span>
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
