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
        <h1>Catat Peminjaman Baru</h1>
        <p>Gunakan formulir ini untuk mencatat peminjaman barang inventaris</p>
    </div>

    @if ($products->isEmpty())
        <div style="background: #fff; border: 1px solid #e0e0e0; padding: 48px; text-align: center;">
            <p style="font-size: 14px; color: #4d4d4d; margin-bottom: 16px;">Tidak ada barang dengan stok tersedia untuk dipinjam saat ini.</p>
            <a href="{{ route('products.index') }}" class="btn-primary" style="text-decoration: none;">Kelola Data Barang</a>
        </div>
    @else
        <div style="max-width: 800px; background: #fff; border: 1px solid #e0e0e0; padding: 32px;">
            <form method="POST" action="{{ route('borrowings.store') }}" id="form-create-borrowing">
                @csrf

                {{-- Borrower Name --}}
                <div style="margin-bottom: 24px;">
                    <label for="borrower_name" style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">
                        Nama Peminjam <span style="color: #da1e28;">*</span>
                    </label>
                    <input type="text"
                           id="borrower_name"
                           name="borrower_name"
                           value="{{ old('borrower_name') }}"
                           placeholder="Nama lengkap peminjam"
                           style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit;"
                           onfocus="this.style.borderBottom='2px solid #ff0d00'"
                           onblur="this.style.borderBottom='1px solid #000'">
                    @error('borrower_name')
                        <p style="margin: 4px 0 0; font-size: 12px; color: #da1e28;">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Dates (2 columns) --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 32px;">
                    <div>
                        <label for="borrow_date" style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">
                            Tanggal Pinjam <span style="color: #da1e28;">*</span>
                        </label>
                        <input type="date"
                               id="borrow_date"
                               name="borrow_date"
                               value="{{ old('borrow_date', today()->toDateString()) }}"
                               style="width: 100%; padding: 10px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit;">
                        @error('borrow_date')
                            <p style="margin: 4px 0 0; font-size: 12px; color: #da1e28;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="due_date" style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">
                            Estimasi Tanggal Kembali <span style="color: #da1e28;">*</span>
                        </label>
                        <input type="date"
                               id="due_date"
                               name="due_date"
                               value="{{ old('due_date', today()->addDays(7)->toDateString()) }}"
                               style="width: 100%; padding: 10px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit;">
                        @error('due_date')
                            <p style="margin: 4px 0 0; font-size: 12px; color: #da1e28;">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Items Header --}}
                <div style="border-bottom: 1px solid #e0e0e0; padding-bottom: 8px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 14px; font-weight: 600; color: #000; letter-spacing: 0.16px;">Barang yang Dipinjam</span>
                    <button type="button" onclick="addItemRow()"
                            style="padding: 6px 12px; background: #000; color: #fff; border: none; font-size: 12px; cursor: pointer; font-family: inherit; letter-spacing: 0.16px;"
                            onmouseover="this.style.background='#262626'"
                            onmouseout="this.style.background='#000'">
                        + Tambah Baris
                    </button>
                </div>

                {{-- Items Dynamic List --}}
                <div id="items-container">
                    @php
                        $oldItems = old('items', [['product_id' => '', 'quantity' => 1]]);
                    @endphp

                    @foreach($oldItems as $index => $oldItem)
                        <div class="item-row" style="display: grid; grid-template-columns: 3fr 1fr auto; gap: 16px; align-items: end; margin-bottom: 16px;">
                            <div>
                                <label style="display: block; font-size: 11px; color: #8c8c8c; margin-bottom: 4px;">Pilih Barang</label>
                                <select name="items[{{ $index }}][product_id]" class="product-select" onchange="updateStockLabel(this)"
                                        style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; outline: none; font-family: inherit; cursor: pointer;">
                                    <option value="">Pilih barang...</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-stock="{{ $product->stock_baik }}" {{ (isset($oldItem['product_id']) && $oldItem['product_id'] == $product->id) ? 'selected' : '' }}>
                                            {{ $product->name }} ({{ $product->code }}) — Stok: {{ $product->stock_baik }}
                                        </option>
                                    @endforeach
                                </select>
                                @error("items.{$index}.product_id")
                                    <p style="margin: 4px 0 0; font-size: 11px; color: #da1e28;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label style="display: block; font-size: 11px; color: #8c8c8c; margin-bottom: 4px;">Jumlah</label>
                                <input type="number"
                                       name="items[{{ $index }}][quantity]"
                                       value="{{ $oldItem['quantity'] ?? 1 }}"
                                       min="1"
                                       style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; outline: none; font-family: inherit;">
                                @error("items.{$index}.quantity")
                                    <p style="margin: 4px 0 0; font-size: 11px; color: #da1e28;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <button type="button" onclick="removeItemRow(this)"
                                        style="padding: 11px 16px; background: none; border: 1px solid #da1e28; color: #da1e28; cursor: pointer; font-size: 14px; font-family: inherit;"
                                        onmouseover="this.style.background='#fff1f1'"
                                        onmouseout="this.style.background='none'">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Submit actions --}}
                <div style="margin-top: 32px; display: flex; gap: 12px;">
                    <button type="submit"
                            id="btn-store-borrowing"
                            style="padding: 12px 24px; background: #ff0d00; color: #fff; border: none; font-size: 14px; letter-spacing: 0.16px; cursor: pointer; font-family: inherit;"
                            onmouseover="this.style.background='#d90b00'"
                            onmouseout="this.style.background='#ff0d00'">
                        Simpan Transaksi Peminjaman
                    </button>
                    <a href="{{ route('borrowings.index') }}"
                       style="padding: 12px 24px; background: #f4f4f4; color: #4d4d4d; font-size: 14px; text-decoration: none; border: 1px solid #e0e0e0; letter-spacing: 0.16px; display: inline-flex; align-items: center;"
                       onmouseover="this.style.background='#e0e0e0'"
                       onmouseout="this.style.background='#f4f4f4'">
                        Batal
                    </a>
                </div>
            </form>
        </div>

        {{-- Dynamic scripts for adding/removing items --}}
        <script>
            let rowIndex = {{ count($oldItems) }};

            function addItemRow() {
                const container = document.getElementById('items-container');
                const row = document.createElement('div');
                row.className = 'item-row';
                row.style = 'display: grid; grid-template-columns: 3fr 1fr auto; gap: 16px; align-items: end; margin-bottom: 16px;';

                row.innerHTML = `
                    <div>
                        <label style="display: block; font-size: 11px; color: #8c8c8c; margin-bottom: 4px;">Pilih Barang</label>
                        <select name="items[\${rowIndex}][product_id]" class="product-select"
                                style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; outline: none; font-family: inherit; cursor: pointer;">
                            <option value="">Pilih barang...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-stock="{{ $product->stock_baik }}">
                                    {{ $product->name }} ({{ $product->code }}) — Stok: {{ $product->stock_baik }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; color: #8c8c8c; margin-bottom: 4px;">Jumlah</label>
                        <input type="number"
                               name="items[\${rowIndex}][quantity]"
                               value="1"
                               min="1"
                               style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; outline: none; font-family: inherit;">
                    </div>
                    <div>
                        <button type="button" onclick="removeItemRow(this)"
                                style="padding: 11px 16px; background: none; border: 1px solid #da1e28; color: #da1e28; cursor: pointer; font-size: 14px; font-family: inherit;"
                                onmouseover="this.style.background='#fff1f1'"
                                onmouseout="this.style.background='none'">
                            Hapus
                        </button>
                    </div>
                `;

                container.appendChild(row);
                rowIndex++;
            }

            function removeItemRow(button) {
                const rows = document.querySelectorAll('.item-row');
                if (rows.length > 1) {
                    button.closest('.item-row').remove();
                } else {
                    alert('Minimal harus meminjam 1 barang.');
                }
            }
        </script>
    @endif
</x-app-layout>
