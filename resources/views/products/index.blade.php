<x-app-layout>
    <div class="c-page-header">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <h1>Data Barang</h1>
                <p>Daftar seluruh barang inventaris yang terdaftar</p>
            </div>
            @role('Admin|Staff')
            <a href="{{ route('products.create') }}"
               id="btn-add-product"
               style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 16px; background: #ff0d00; color: #fff; font-size: 14px; text-decoration: none; letter-spacing: 0.16px; white-space: nowrap;"
               onmouseover="this.style.background='#d90b00'"
               onmouseout="this.style.background='#ff0d00'">
                + Tambah Barang
            </a>
            @endrole
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <form method="GET" action="{{ route('products.index') }}" id="filter-form"
          style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
        <div style="flex: 1; min-width: 200px;">
            <label style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">Cari barang</label>
            <input type="text"
                   id="search"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Nama atau kode barang..."
                   style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit;"
                   onfocus="this.style.borderBottom='2px solid #ff0d00'"
                   onblur="this.style.borderBottom='1px solid #000'">
        </div>

        <div style="min-width: 160px;">
            <label style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">Kategori</label>
            <select id="category_id" name="category_id"
                    style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit; cursor: pointer;">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
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
            @if(request()->hasAny(['search','category_id']))
                <a href="{{ route('products.index') }}"
                   style="padding: 12px 16px; background: #f4f4f4; color: #4d4d4d; font-size: 14px; text-decoration: none; letter-spacing: 0.16px; border: 1px solid #e0e0e0;"
                   onmouseover="this.style.background='#e0e0e0'"
                   onmouseout="this.style.background='#f4f4f4'">
                    Reset
                </a>
            @endif
        </div>
    </form>

    {{-- Products Table --}}
    <div style="background: #fff; border: 1px solid #e0e0e0;">
        @if($products->isEmpty())
            <div style="padding: 48px; text-align: center; color: #8c8c8c; font-size: 14px;">
                Tidak ada barang yang ditemukan.
            </div>
        @else
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="background: #f4f4f4; border-bottom: 1px solid #e0e0e0;">
                        <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px; width: 60px;">Foto</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Kode</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Nama Barang</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Kategori</th>
                        <th style="padding: 12px 16px; text-align: right; font-weight: 600; color: #000; letter-spacing: 0.16px;">Baik</th>
                         <th style="padding: 12px 16px; text-align: right; font-weight: 600; color: #000; letter-spacing: 0.16px;">Rusak</th>
                         <th style="padding: 12px 16px; text-align: right; font-weight: 600; color: #000; letter-spacing: 0.16px;">Perlu Perbaikan</th>
                         <th style="padding: 12px 16px; text-align: right; font-weight: 600; color: #000; letter-spacing: 0.16px;">Total</th>
                        <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #000; letter-spacing: 0.16px;">Lokasi</th>
                        <th style="padding: 12px 16px; text-align: right; font-weight: 600; color: #000; letter-spacing: 0.16px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr style="border-bottom: 1px solid #e0e0e0;" onmouseover="this.style.background='#f4f4f4'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 12px 16px;">
                                @if($product->image)
                                    <img src="{{ Storage::url($product->image) }}"
                                         alt="{{ $product->name }}"
                                         style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div style="width: 40px; height: 40px; background: #f4f4f4; border: 1px solid #e0e0e0; display: flex; align-items: center; justify-content: center; color: #8c8c8c; font-size: 18px;">&#9723;</div>
                                @endif
                            </td>
                            <td style="padding: 12px 16px; color: #4d4d4d; font-family: 'IBM Plex Mono', monospace;">{{ $product->code }}</td>
                            <td style="padding: 12px 16px; font-weight: 400; color: #000;">
                                <a href="{{ route('products.show', $product) }}"
                                   style="color: #ff0d00; text-decoration: none;"
                                   onmouseover="this.style.textDecoration='underline'"
                                   onmouseout="this.style.textDecoration='none'">
                                    {{ $product->name }}
                                </a>
                            </td>
                            <td style="padding: 12px 16px; color: #4d4d4d;">{{ $product->category?->name ?? '—' }}</td>
                            <td style="padding: 12px 16px; text-align: right; color: #24a148; font-weight: 600;">{{ $product->stock_baik }}</td>
                             <td style="padding: 12px 16px; text-align: right; color: #da1e28; font-weight: 600;">{{ $product->stock_rusak }}</td>
                             <td style="padding: 12px 16px; text-align: right; color: #f1c21b; font-weight: 600;">{{ $product->stock_perlu_perbaikan }}</td>
                             <td style="padding: 12px 16px; text-align: right; color: #000; font-weight: 600; border-left: 1px solid #e0e0e0;">{{ $product->total_stock }}</td>
                            <td style="padding: 12px 16px; color: #4d4d4d;">{{ $product->location }}</td>
                            <td style="padding: 12px 16px; text-align: right;">
                                <div style="display: inline-flex; gap: 8px; align-items: center;">
                                    <a href="{{ route('products.show', $product) }}"
                                       style="font-size: 12px; color: #4d4d4d; text-decoration: none; padding: 4px 8px; border: 1px solid #e0e0e0;"
                                       onmouseover="this.style.background='#f4f4f4'"
                                       onmouseout="this.style.background='transparent'">Detail</a>

                                    @role('Admin|Staff')
                                    <a href="{{ route('products.edit', $product) }}"
                                       style="font-size: 12px; color: #4d4d4d; text-decoration: none; padding: 4px 8px; border: 1px solid #e0e0e0;"
                                       onmouseover="this.style.background='#f4f4f4'"
                                       onmouseout="this.style.background='transparent'">Edit</a>

                                    <form method="POST" action="{{ route('products.destroy', $product) }}"
                                          style="display: inline;"
                                          onsubmit="return confirm('Yakin ingin menghapus barang ini? Riwayat peminjaman tetap tersimpan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                style="font-size: 12px; color: #da1e28; background: none; border: 1px solid #da1e28; padding: 4px 8px; cursor: pointer; font-family: inherit; letter-spacing: 0.16px;"
                                                onmouseover="this.style.background='#fff1f1'"
                                                onmouseout="this.style.background='none'">
                                            Hapus
                                        </button>
                                    </form>
                                    @endrole
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            @if($products->hasPages())
                <div style="padding: 16px; border-top: 1px solid #e0e0e0; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px;">
                        Menampilkan {{ $products->firstItem() }}–{{ $products->lastItem() }} dari {{ $products->total() }} barang
                    </span>
                    <div style="display: flex; gap: 4px;">
                        {{ $products->links() }}
                    </div>
                </div>
            @else
                <div style="padding: 12px 16px; border-top: 1px solid #e0e0e0;">
                    <span style="font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px;">
                        Total {{ $products->total() }} barang
                    </span>
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
