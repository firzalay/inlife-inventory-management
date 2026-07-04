<x-app-layout>
    <div class="c-page-header">
        <div style="margin-bottom: 8px;">
            <a href="{{ route('borrowings.index') }}"
               style="font-size: 14px; color: #4d4d4d; text-decoration: none;"
               onmouseover="this.style.color='#000'"
               onmouseout="this.style.color='#4d4d4d'">
                ← Kembali ke Daftar Peminjaman
            </a>
        </div>
        <h1>Rincian Peminjaman</h1>
        <p>Detail transaksi peminjaman oleh {{ $borrowing->borrower_name }}</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px; align-items: start;">

        {{-- Left Card: Metadata --}}
        <div style="background: #fff; border: 1px solid #e0e0e0;">
            <div style="padding: 16px; border-bottom: 1px solid #e0e0e0; background: #f4f4f4;">
                <span style="font-size: 12px; font-weight: 600; letter-spacing: 0.32px; text-transform: uppercase; color: #4d4d4d;">Info Transaksi</span>
            </div>
            <div>
                @foreach([
                    ['Peminjam', $borrowing->borrower_name],
                    ['Tanggal Pinjam', $borrowing->borrow_date->format('d M Y')],
                    ['Batas Pengembalian', $borrowing->due_date->format('d M Y')],
                    ['Tanggal Kembali Aktual', $borrowing->return_date ? $borrowing->return_date->format('d M Y') : 'Belum dikembalikan'],
                ] as [$label, $value])
                    <div style="display: flex; padding: 12px 16px; border-bottom: 1px solid #e0e0e0;">
                        <span style="width: 140px; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; flex-shrink: 0;">{{ $label }}</span>
                        <span style="font-size: 14px; color: #000; flex: 1;">{{ $value }}</span>
                    </div>
                @endforeach
                <div style="display: flex; align-items: center; padding: 12px 16px;">
                    <span style="width: 140px; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; flex-shrink: 0;">Status</span>
                    <x-borrowing-status-badge :status="$borrowing->computed_status" />
                </div>
            </div>
        </div>

        {{-- Right Column: Items List & Return Form --}}
        <div style="display: flex; flex-direction: column; gap: 24px;">

            {{-- Items Table --}}
            <div style="background: #fff; border: 1px solid #e0e0e0;">
                <div style="padding: 16px; border-bottom: 1px solid #e0e0e0; background: #f4f4f4;">
                    <span style="font-size: 12px; font-weight: 600; letter-spacing: 0.32px; text-transform: uppercase; color: #4d4d4d;">Daftar Barang</span>
                </div>
                <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <thead>
                        <tr style="background: #f4f4f4; border-bottom: 1px solid #e0e0e0;">
                            <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Kode</th>
                            <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Nama Barang</th>
                            <th style="padding: 12px 16px; text-align: right; font-weight: 600; color: #000; letter-spacing: 0.16px;">Jumlah</th>
                            @if($borrowing->status === 'returned')
                                <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Kondisi Saat Kembali</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($borrowing->details as $detail)
                            <tr style="border-bottom: 1px solid #e0e0e0;">
                                <td style="padding: 12px 16px; font-family: monospace; color: #4d4d4d;">{{ $detail->product?->code ?? '—' }}</td>
                                <td style="padding: 12px 16px; color: #000;">
                                    @if($detail->product)
                                        <a href="{{ route('products.show', $detail->product) }}" style="color: #ff0d00; text-decoration: none;">
                                            {{ $detail->product->name }}
                                        </a>
                                    @else
                                        Barang Terhapus
                                    @endif
                                </td>
                                <td style="padding: 12px 16px; text-align: right; color: #000; font-weight: 600;">{{ $detail->quantity }} unit</td>
                                @if($borrowing->status === 'returned')
                                    <td style="padding: 12px 16px;">
                                        @php
                                            $conditionOnReturn = $detail->condition_on_return ?? 'Baik';
                                            [$bg, $color, $border] = match($conditionOnReturn) {
                                                'Baik' => ['#defbe6', '#0e6027', '#24a148'],
                                                'Rusak' => ['#fff1f1', '#750e13', '#da1e28'],
                                                'Perlu Perbaikan' => ['#fef9e7', '#7b5e00', '#f1c21b'],
                                                default => ['#f4f4f4', '#4d4d4d', '#8c8c8c'], // Hilang
                                            };
                                        @endphp
                                        <span style="
                                            display: inline-block;
                                            padding: 2px 8px;
                                            font-size: 12px;
                                            background: {{ $bg }};
                                            color: {{ $color }};
                                            border: 1px solid {{ $border }};
                                        ">
                                            {{ $conditionOnReturn }}
                                        </span>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Return Form (only if borrowed & user is Admin/Staff) --}}
            @if($borrowing->status === 'borrowed')
                @role('Admin|Staff')
                <div style="background: #fff; border: 1px solid #e0e0e0; padding: 24px;">
                    <div style="border-bottom: 1px solid #e0e0e0; padding-bottom: 12px; margin-bottom: 20px;">
                        <h3 style="font-size: 16px; font-weight: 600; color: #000; margin: 0;">Proses Pengembalian Barang</h3>
                        <p style="font-size: 12px; color: #4d4d4d; margin: 4px 0 0;">Harap periksa kondisi fisik setiap barang saat diterima kembali</p>
                    </div>

                    <form method="POST" action="{{ route('borrowings.return', $borrowing) }}" id="form-return-goods">
                        @csrf

                        @foreach($borrowing->details as $detail)
                            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; align-items: center; margin-bottom: 16px;">
                                <div style="font-size: 14px; color: #000;">
                                    <strong>{{ $detail->product?->name }}</strong> ({{ $detail->quantity }} unit)
                                </div>
                                <div>
                                    <select name="conditions[{{ $detail->id }}]" required
                                            style="width: 100%; padding: 8px 12px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 13px; font-family: inherit; cursor: pointer;">
                                        <option value="Baik">Baik</option>
                                        <option value="Perlu Perbaikan">Perlu Perbaikan</option>
                                        <option value="Rusak">Rusak</option>
                                        <option value="Hilang">Hilang</option>
                                    </select>
                                </div>
                            </div>
                        @endforeach

                        <div style="margin-top: 24px;">
                            <button type="submit" id="btn-submit-return"
                                    style="width: 100%; padding: 12px; background: #ff0d00; color: #fff; border: none; font-size: 14px; letter-spacing: 0.16px; cursor: pointer; font-family: inherit;"
                                    onmouseover="this.style.background='#d90b00'"
                                    onmouseout="this.style.background='#ff0d00'">
                                Kembalikan Semua Barang ke Inventaris
                            </button>
                        </div>
                    </form>
                </div>
                @endrole
            @endif

        </div>

    </div>
</x-app-layout>
