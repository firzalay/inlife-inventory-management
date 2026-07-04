<x-app-layout>
    <div class="c-page-header">
        <div style="margin-bottom: 8px;">
            <a href="{{ route('products.show', $product) }}"
               style="font-size: 14px; color: #4d4d4d; text-decoration: none;"
               onmouseover="this.style.color='#000'"
               onmouseout="this.style.color='#4d4d4d'">
                ← Kembali ke Detail Barang
            </a>
        </div>
        <h1>Edit Barang</h1>
        <p>{{ $product->code }} — {{ $product->name }}</p>
    </div>

    <div style="max-width: 640px; background: #fff; border: 1px solid #e0e0e0; padding: 32px;">
        <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" id="form-edit-product">
            @csrf
            @method('PATCH')

            {{-- Kode Barang --}}
            <div style="margin-bottom: 24px;">
                <label for="code" style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">
                    Kode Barang <span style="color: #da1e28;">*</span>
                </label>
                <input type="text" id="code" name="code" value="{{ old('code', $product->code) }}"
                       style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit;"
                       onfocus="this.style.borderBottom='2px solid #ff0d00'"
                       onblur="this.style.borderBottom='1px solid #000'">
                @error('code')
                    <p style="margin: 4px 0 0; font-size: 12px; color: #da1e28;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nama Barang --}}
            <div style="margin-bottom: 24px;">
                <label for="name" style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">
                    Nama Barang <span style="color: #da1e28;">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}"
                       style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit;"
                       onfocus="this.style.borderBottom='2px solid #ff0d00'"
                       onblur="this.style.borderBottom='1px solid #000'">
                @error('name')
                    <p style="margin: 4px 0 0; font-size: 12px; color: #da1e28;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kategori --}}
            <div style="margin-bottom: 24px;">
                <label for="category_id" style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">
                    Kategori <span style="color: #da1e28;">*</span>
                </label>
                <select id="category_id" name="category_id"
                        style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit; cursor: pointer;">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p style="margin: 4px 0 0; font-size: 12px; color: #da1e28;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Stok Breakdown --}}
            <div style="margin-bottom: 24px;">
                <p style="font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin: 0 0 12px; font-weight: 600; text-transform: uppercase;">Stok Berdasarkan Kondisi</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">

                    {{-- Stok Baik --}}
                    <div>
                        <label for="stock_baik" style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">
                            Stok Baik <span style="color: #da1e28;">*</span>
                        </label>
                        <input type="number" id="stock_baik" name="stock_baik"
                               value="{{ old('stock_baik', $product->stock_baik) }}" min="0"
                               style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit;"
                               onfocus="this.style.borderBottom='2px solid #ff0d00'"
                               onblur="this.style.borderBottom='1px solid #000'">
                        @error('stock_baik')
                            <p style="margin: 4px 0 0; font-size: 12px; color: #da1e28;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Stok Rusak --}}
                    <div>
                        <label for="stock_rusak" style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">
                            Stok Rusak
                        </label>
                        <input type="number" id="stock_rusak" name="stock_rusak"
                               value="{{ old('stock_rusak', $product->stock_rusak) }}" min="0"
                               style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit;"
                               onfocus="this.style.borderBottom='2px solid #ff0d00'"
                               onblur="this.style.borderBottom='1px solid #000'">
                        @error('stock_rusak')
                            <p style="margin: 4px 0 0; font-size: 12px; color: #da1e28;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Stok Perlu Perbaikan --}}
                    <div>
                        <label for="stock_perlu_perbaikan" style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">
                            Perlu Perbaikan
                        </label>
                        <input type="number" id="stock_perlu_perbaikan" name="stock_perlu_perbaikan"
                               value="{{ old('stock_perlu_perbaikan', $product->stock_perlu_perbaikan) }}" min="0"
                               style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit;"
                               onfocus="this.style.borderBottom='2px solid #ff0d00'"
                               onblur="this.style.borderBottom='1px solid #000'">
                        @error('stock_perlu_perbaikan')
                            <p style="margin: 4px 0 0; font-size: 12px; color: #da1e28;">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- Lokasi --}}
            <div style="margin-bottom: 24px;">
                <label for="location" style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">
                    Lokasi Penyimpanan <span style="color: #da1e28;">*</span>
                </label>
                <input type="text" id="location" name="location" value="{{ old('location', $product->location) }}"
                       style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit;"
                       onfocus="this.style.borderBottom='2px solid #ff0d00'"
                       onblur="this.style.borderBottom='1px solid #000'">
                @error('location')
                    <p style="margin: 4px 0 0; font-size: 12px; color: #da1e28;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Foto saat ini --}}
            @if($product->image)
                <div style="margin-bottom: 16px;">
                    <p style="font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 8px;">Foto saat ini:</p>
                    <img src="{{ Storage::url($product->image) }}"
                         alt="{{ $product->name }}"
                         style="width: 120px; height: 120px; object-fit: cover; border: 1px solid #e0e0e0;">
                </div>
            @endif

            {{-- Upload Foto Baru --}}
            <div style="margin-bottom: 32px;">
                <label for="image" style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">
                    {{ $product->image ? 'Ganti Foto Barang' : 'Foto Barang' }}
                    <span style="color: #8c8c8c;">(jpg/png, maks. 2MB)</span>
                </label>
                <div style="background: #f4f4f4; border: 1px dashed #8c8c8c; padding: 16px;">
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png"
                           style="font-size: 14px; font-family: inherit; color: #4d4d4d;">
                </div>
                @error('image')
                    <p style="margin: 4px 0 0; font-size: 12px; color: #da1e28;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div style="display: flex; gap: 12px;">
                <button type="submit" id="btn-update-product"
                        style="padding: 12px 24px; background: #ff0d00; color: #fff; border: none; font-size: 14px; letter-spacing: 0.16px; cursor: pointer; font-family: inherit;"
                        onmouseover="this.style.background='#d90b00'"
                        onmouseout="this.style.background='#ff0d00'">
                    Simpan Perubahan
                </button>
                <a href="{{ route('products.show', $product) }}"
                   style="padding: 12px 24px; background: #f4f4f4; color: #4d4d4d; font-size: 14px; text-decoration: none; border: 1px solid #e0e0e0; letter-spacing: 0.16px; display: inline-flex; align-items: center;"
                   onmouseover="this.style.background='#e0e0e0'"
                   onmouseout="this.style.background='#f4f4f4'">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
