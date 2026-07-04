<x-app-layout>
    <div class="c-page-header">
        <div style="margin-bottom: 8px;">
            <a href="{{ route('products.index') }}"
               style="font-size: 14px; color: #4d4d4d; text-decoration: none;"
               onmouseover="this.style.color='#000'"
               onmouseout="this.style.color='#4d4d4d'">
                ← Kembali ke Daftar Barang
            </a>
        </div>
        <h1>{{ $product->name }}</h1>
        <p>{{ $product->code }}</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px; align-items: start;">

        {{-- Left: Image --}}
        <div style="background: #fff; border: 1px solid #e0e0e0;">
            @if($product->image)
                <img src="{{ Storage::url($product->image) }}"
                     alt="{{ $product->name }}"
                     style="width: 100%; display: block; aspect-ratio: 1/1; object-fit: cover;">
            @else
                <div style="aspect-ratio: 1/1; display: flex; align-items: center; justify-content: center; background: #f4f4f4; color: #8c8c8c; flex-direction: column; gap: 8px;">
                    <span style="font-size: 48px;">&#9723;</span>
                    <span style="font-size: 12px; letter-spacing: 0.32px;">Belum ada foto</span>
                </div>
            @endif

            {{-- Action buttons --}}
            <div style="padding: 16px; border-top: 1px solid #e0e0e0; display: flex; flex-direction: column; gap: 8px;">
                @role('Admin|Staff')
                <a href="{{ route('products.edit', $product) }}"
                   id="btn-edit-product"
                   style="display: block; text-align: center; padding: 12px; background: #000; color: #fff; font-size: 14px; text-decoration: none; letter-spacing: 0.16px;"
                   onmouseover="this.style.background='#262626'"
                   onmouseout="this.style.background='#000'">
                    Edit Barang
                </a>

                <form method="POST" action="{{ route('products.destroy', $product) }}"
                      onsubmit="return confirm('Yakin ingin menghapus barang ini? Riwayat peminjaman tetap tersimpan.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            id="btn-delete-product"
                            style="display: block; width: 100%; padding: 12px; background: none; border: 1px solid #da1e28; color: #da1e28; font-size: 14px; letter-spacing: 0.16px; cursor: pointer; font-family: inherit;"
                            onmouseover="this.style.background='#fff1f1'"
                            onmouseout="this.style.background='none'">
                        Hapus Barang
                    </button>
                </form>
                @endrole
            </div>
        </div>

        {{-- Right: Details + Borrowing History --}}
        <div style="display: flex; flex-direction: column; gap: 24px;">

            {{-- Detail Card --}}
            <div style="background: #fff; border: 1px solid #e0e0e0;">
                <div style="padding: 16px; border-bottom: 1px solid #e0e0e0; background: #f4f4f4;">
                    <span style="font-size: 12px; font-weight: 600; letter-spacing: 0.32px; text-transform: uppercase; color: #4d4d4d;">Detail Barang</span>
                </div>
                <div style="padding: 0;">
                    @foreach([
                        ['Kode Barang', $product->code],
                        ['Nama', $product->name],
                        ['Kategori', $product->category?->name ?? '—'],
                        ['Stok', number_format($product->stock).' unit'],
                        ['Lokasi Penyimpanan', $product->location],
                    ] as [$label, $value])
                        <div style="display: flex; padding: 12px 16px; border-bottom: 1px solid #e0e0e0;">
                            <span style="width: 160px; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; flex-shrink: 0;">{{ $label }}</span>
                            <span style="font-size: 14px; color: #000; flex: 1;">{{ $value }}</span>
                        </div>
                    @endforeach
                    <div style="display: flex; align-items: center; padding: 12px 16px; border-bottom: 1px solid #e0e0e0;">
                        <span style="width: 160px; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; flex-shrink: 0;">Kondisi</span>
                        <x-product-condition-badge :condition="$product->condition" />
                    </div>
                    <div style="display: flex; padding: 12px 16px;">
                        <span style="width: 160px; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; flex-shrink: 0;">Ditambahkan</span>
                        <span style="font-size: 14px; color: #000;">{{ $product->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Borrowing History --}}
            <div style="background: #fff; border: 1px solid #e0e0e0;">
                <div style="padding: 16px; border-bottom: 1px solid #e0e0e0; background: #f4f4f4;">
                    <span style="font-size: 12px; font-weight: 600; letter-spacing: 0.32px; text-transform: uppercase; color: #4d4d4d;">
                        Riwayat Peminjaman
                    </span>
                </div>

                @if($product->borrowingDetails->isEmpty())
                    <div style="padding: 32px; text-align: center; color: #8c8c8c; font-size: 14px;">
                        Belum ada riwayat peminjaman untuk barang ini.
                    </div>
                @else
                    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                        <thead>
                            <tr style="background: #f4f4f4; border-bottom: 1px solid #e0e0e0;">
                                <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Peminjam</th>
                                <th style="padding: 12px 16px; text-align: right; font-weight: 600; color: #000; letter-spacing: 0.16px;">Jml</th>
                                <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Tgl Pinjam</th>
                                <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Tgl Kembali</th>
                                <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Kondisi Kembali</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->borrowingDetails as $detail)
                                <tr style="border-bottom: 1px solid #e0e0e0;">
                                    <td style="padding: 12px 16px; color: #000;">{{ $detail->borrowing->borrower_name }}</td>
                                    <td style="padding: 12px 16px; text-align: right; color: #000;">{{ $detail->quantity }}</td>
                                    <td style="padding: 12px 16px; color: #4d4d4d;">
                                        {{ $detail->borrowing->borrow_date ? \Carbon\Carbon::parse($detail->borrowing->borrow_date)->format('d M Y') : '—' }}
                                    </td>
                                    <td style="padding: 12px 16px; color: #4d4d4d;">
                                        {{ $detail->borrowing->return_date ? \Carbon\Carbon::parse($detail->borrowing->return_date)->format('d M Y') : '—' }}
                                    </td>
                                    <td style="padding: 12px 16px; color: #4d4d4d;">{{ $detail->condition_on_return ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
