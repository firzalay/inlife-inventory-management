<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Data Barang Inventaris</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
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
            padding: 8px 10px;
            font-weight: bold;
            text-align: left;
            font-size: 11px;
        }
        .data-table td {
            border: 1px solid #e0e0e0;
            padding: 8px 10px;
            vertical-align: top;
            font-size: 11px;
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
        .badge-baik {
            color: #24a148;
            font-weight: bold;
        }
        .badge-rusak {
            color: #da1e28;
            font-weight: bold;
        }
        .badge-perbaikan {
            color: #8e6a00;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="header">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td>
                    <div class="title">Laporan Data Barang Inventaris</div>
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
            <td class="meta-label">Filter Pencarian:</td>
            <td>{{ $search ?: 'Semua barang' }}</td>
            <td class="meta-label" style="text-align: right;">Filter Kategori:</td>
            <td style="text-align: right;">{{ $category ? $category->name : 'Semua kategori' }}</td>
        </tr>
    </table>

    @if($products->isEmpty())
        <div class="empty-message">
            Tidak ada data barang yang ditemukan untuk kriteria filter saat ini.
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Kode</th>
                    <th style="width: 28%;">Nama Barang</th>
                    <th style="width: 15%;">Kategori</th>
                    <th style="width: 15%;">Lokasi</th>
                    <th class="text-right" style="width: 7%;">Baik</th>
                    <th class="text-right" style="width: 7%;">Rusak</th>
                    <th class="text-right" style="width: 8%;">Perbaikan</th>
                    <th class="text-right" style="width: 8%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td style="font-family: Courier, monospace;">{{ $product->code }}</td>
                        <td><strong>{{ $product->name }}</strong></td>
                        <td>{{ $product->category?->name ?? '—' }}</td>
                        <td>{{ $product->location }}</td>
                        <td class="text-right badge-baik">{{ $product->stock_baik }}</td>
                        <td class="text-right badge-rusak">{{ $product->stock_rusak }}</td>
                        <td class="text-right badge-perbaikan">{{ $product->stock_perlu_perbaikan }}</td>
                        <td class="text-right" style="font-weight: bold; background-color: #f4f4f4;">{{ $product->total_stock }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</body>
</html>
