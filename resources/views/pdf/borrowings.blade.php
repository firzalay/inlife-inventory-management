<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Riwayat Peminjaman Barang</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #000000;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header {
            border-bottom: 2px solid #000000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 6px 0;
            letter-spacing: 0.5px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 2px 0;
            font-size: 11px;
            color: #4d4d4d;
            vertical-align: top;
        }
        .meta-label {
            width: 120px;
            font-weight: bold;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th {
            background-color: #f4f4f4;
            border: 1px solid #e0e0e0;
            padding: 6px 8px;
            font-weight: bold;
            text-align: left;
            font-size: 10px;
        }
        .data-table td {
            border: 1px solid #e0e0e0;
            padding: 6px 8px;
            vertical-align: top;
            font-size: 10px;
        }
        .data-table tr:nth-child(even) {
            background-color: #fafafa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .empty-message {
            padding: 30px;
            text-align: center;
            color: #8c8c8c;
            border: 1px dashed #e0e0e0;
            font-style: italic;
        }
        ul.item-list {
            margin: 0;
            padding-left: 12px;
        }
        ul.item-list li {
            margin-bottom: 2px;
        }
        .status-badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-returned {
            background-color: #defbe6;
            color: #0e6027;
            border: 1px solid #24a148;
        }
        .status-borrowed {
            background-color: #e0f0ff;
            color: #0043ce;
            border: 1px solid #0f62fe;
        }
        .status-overdue {
            background-color: #fff1f1;
            color: #da1e28;
            border: 1px solid #da1e28;
        }
    </style>
</head>
<body>

    <div class="header">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td>
                    <div class="title">Laporan Riwayat Peminjaman Barang</div>
                    <span style="font-size: 11px; color: #8c8c8c;">InLife Inventory Management System</span>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Tanggal Cetak:</td>
            <td>{{ $date }}</td>
            <td class="meta-label" style="text-align: right;">Dicetak Oleh:</td>
            <td style="text-align: right;">{{ $user->name }} ({{ $user->email }})</td>
        </tr>
        <tr>
            <td class="meta-label">Pencarian Nama:</td>
            <td>{{ $search ?: 'Semua peminjam' }}</td>
            <td class="meta-label" style="text-align: right;">Status:</td>
            <td style="text-align: right;">
                @if($status === 'returned')
                    Dikembalikan
                @elseif($status === 'borrowed')
                    Dipinjam
                @elseif($status === 'overdue')
                    Terlambat
                @else
                    Semua status
                @endif
            </td>
        </tr>
        @if($start_date || $end_date)
            <tr>
                <td class="meta-label">Periode Tanggal:</td>
                <td colspan="3">
                    {{ $start_date ? \Carbon\Carbon::parse($start_date)->format('d M Y') : 'Awal' }}
                    s/d
                    {{ $end_date ? \Carbon\Carbon::parse($end_date)->format('d M Y') : 'Sekarang' }}
                </td>
            </tr>
        @endif
    </table>

    @if($borrowings->isEmpty())
        <div class="empty-message">
            Tidak ada transaksi peminjaman yang ditemukan untuk kriteria filter saat ini.
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 18%;">Nama Peminjam</th>
                    <th style="width: 25%;">Barang yang Dipinjam</th>
                    <th style="width: 11%;">Tgl Pinjam</th>
                    <th style="width: 11%;">Batas Kembali</th>
                    <th style="width: 11%;">Tgl Kembali</th>
                    <th style="width: 13%;">Kondisi Kembali</th>
                    <th style="width: 11%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($borrowings as $borrowing)
                    <tr>
                        <td><strong>{{ $borrowing->borrower_name }}</strong></td>
                        <td>
                            <ul class="item-list">
                                @foreach($borrowing->details as $detail)
                                    <li>{{ $detail->product?->name ?? 'Barang Terhapus' }} ({{ $detail->quantity }} unit)</li>
                                @endforeach
                            </ul>
                        </td>
                        <td>{{ $borrowing->borrow_date->format('d M Y') }}</td>
                        <td>{{ $borrowing->due_date->format('d M Y') }}</td>
                        <td>{{ $borrowing->return_date ? $borrowing->return_date->format('d M Y') : '—' }}</td>
                        <td>
                            <ul class="item-list">
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
                                            <span style="color: {{ $color }}; font-weight: bold;">{{ $cond }}</span>
                                        @else
                                            <span style="color: #8c8c8c;">—</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                        <td>
                            @php
                                $computed = $borrowing->computed_status;
                            @endphp
                            @if($computed === 'returned')
                                <span class="status-badge status-returned">Dikembalikan</span>
                            @elseif($computed === 'overdue')
                                <span class="status-badge status-overdue">Terlambat</span>
                            @else
                                <span class="status-badge status-borrowed">Dipinjam</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</body>
</html>
