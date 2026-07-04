<x-app-layout>
    <div class="c-page-header">
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 8px;">
            <a href="{{ route('products.index') }}"
               style="font-size: 14px; color: #4d4d4d; text-decoration: none; letter-spacing: 0.16px;"
               onmouseover="this.style.color='#000'"
               onmouseout="this.style.color='#4d4d4d'">
                ← Kembali ke Daftar Barang
            </a>
        </div>
        <h1>Tambah Barang Baru</h1>
        <p>Isi formulir di bawah untuk mendaftarkan barang inventaris baru</p>
    </div>

    <div style="max-width: 640px; background: #fff; border: 1px solid #e0e0e0; padding: 32px;">
        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" id="form-create-product">
            @csrf

            {{-- Kode Barang --}}
            <div style="margin-bottom: 24px;">
                <label for="code" style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">
                    Kode Barang <span style="color: #da1e28;">*</span>
                </label>
                <input type="text"
                       id="code"
                       name="code"
                       value="{{ old('code') }}"
                       placeholder="PRD-0001"
                       style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit;"
                       onfocus="this.style.borderBottom='2px solid #ff0d00'"
                       onblur="this.style.borderBottom='1px solid #000'">
                @error('code')
                    <p style="margin: 4px 0 0; font-size: 12px; color: #da1e28; letter-spacing: 0.32px;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nama Barang --}}
            <div style="margin-bottom: 24px;">
                <label for="name" style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">
                    Nama Barang <span style="color: #da1e28;">*</span>
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name') }}"
                       placeholder="Nama barang inventaris"
                       style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit;"
                       onfocus="this.style.borderBottom='2px solid #ff0d00'"
                       onblur="this.style.borderBottom='1px solid #000'">
                @error('name')
                    <p style="margin: 4px 0 0; font-size: 12px; color: #da1e28; letter-spacing: 0.32px;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kategori --}}
            <div style="margin-bottom: 24px;">
                <label for="category_id" style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">
                    Kategori <span style="color: #da1e28;">*</span>
                </label>
                <select id="category_id" name="category_id"
                        style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit; cursor: pointer;">
                    <option value="">Pilih kategori...</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p style="margin: 4px 0 0; font-size: 12px; color: #da1e28; letter-spacing: 0.32px;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Stok & Lokasi (2-column) --}}
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <label for="stock" style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">
                        Stok <span style="color: #da1e28;">*</span>
                    </label>
                    <input type="number"
                           id="stock"
                           name="stock"
                           value="{{ old('stock', 0) }}"
                           min="0"
                           style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit;"
                           onfocus="this.style.borderBottom='2px solid #ff0d00'"
                           onblur="this.style.borderBottom='1px solid #000'">
                    @error('stock')
                        <p style="margin: 4px 0 0; font-size: 12px; color: #da1e28; letter-spacing: 0.32px;">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="location" style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">
                        Lokasi Penyimpanan <span style="color: #da1e28;">*</span>
                    </label>
                    <input type="text"
                           id="location"
                           name="location"
                           value="{{ old('location') }}"
                           placeholder="Gudang A Rak 01"
                           style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit;"
                           onfocus="this.style.borderBottom='2px solid #ff0d00'"
                           onblur="this.style.borderBottom='1px solid #000'">
                    @error('location')
                        <p style="margin: 4px 0 0; font-size: 12px; color: #da1e28; letter-spacing: 0.32px;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Kondisi --}}
            <div style="margin-bottom: 24px;">
                <label for="condition" style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">
                    Kondisi Barang <span style="color: #da1e28;">*</span>
                </label>
                <select id="condition" name="condition"
                        style="width: 100%; padding: 11px 16px; background: #f4f4f4; border: none; border-bottom: 1px solid #000; font-size: 14px; letter-spacing: 0.16px; outline: none; font-family: inherit; cursor: pointer;">
                    <option value="good" {{ old('condition', 'good') === 'good' ? 'selected' : '' }}>Baik</option>
                    <option value="damaged" {{ old('condition') === 'damaged' ? 'selected' : '' }}>Rusak</option>
                    <option value="lost" {{ old('condition') === 'lost' ? 'selected' : '' }}>Hilang</option>
                </select>
                @error('condition')
                    <p style="margin: 4px 0 0; font-size: 12px; color: #da1e28; letter-spacing: 0.32px;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Upload Gambar --}}
            <div style="margin-bottom: 32px;">
                <label for="image" style="display: block; font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; margin-bottom: 4px;">
                    Foto Barang
                    <span style="color: #8c8c8c;">(jpg/png, maks. 2MB)</span>
                </label>
                <div style="background: #f4f4f4; border: 1px dashed #8c8c8c; padding: 16px;">
                    <input type="file"
                           id="image"
                           name="image"
                           accept="image/jpeg,image/png"
                           style="font-size: 14px; font-family: inherit; color: #4d4d4d;">
                </div>
                @error('image')
                    <p style="margin: 4px 0 0; font-size: 12px; color: #da1e28; letter-spacing: 0.32px;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div style="display: flex; gap: 12px;">
                <button type="submit"
                        id="btn-store-product"
                        style="padding: 12px 24px; background: #ff0d00; color: #fff; border: none; font-size: 14px; letter-spacing: 0.16px; cursor: pointer; font-family: inherit;"
                        onmouseover="this.style.background='#d90b00'"
                        onmouseout="this.style.background='#ff0d00'">
                    Simpan Barang
                </button>
                <a href="{{ route('products.index') }}"
                   style="padding: 12px 24px; background: #f4f4f4; color: #4d4d4d; font-size: 14px; text-decoration: none; border: 1px solid #e0e0e0; letter-spacing: 0.16px; display: inline-flex; align-items: center;"
                   onmouseover="this.style.background='#e0e0e0'"
                   onmouseout="this.style.background='#f4f4f4'">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
