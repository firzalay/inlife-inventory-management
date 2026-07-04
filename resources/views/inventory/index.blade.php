<x-app-layout>
    <div class="c-page-header">
        <h1>Manajemen Inventaris</h1>
        <p>Akses cepat ke modul operasional inventaris barang</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
        {{-- Card 1: Kelola Barang --}}
        <div style="background: #fff; border: 1px solid #e0e0e0; padding: 24px; display: flex; flex-direction: column; justify-content: space-between; height: 200px;">
            <div>
                <h3 style="font-size: 16px; font-weight: 600; color: #000; margin: 0 0 8px;">Kelola Data Barang</h3>
                <p style="font-size: 13px; color: #4d4d4d; margin: 0;">Tambah, perbarui, cari, dan hapus data barang inventaris kantor.</p>
            </div>
            <a href="{{ route('products.index') }}" class="btn-primary" style="text-decoration: none; text-align: center; display: block; border-radius: 0;">Buka Modul Barang</a>
        </div>

        {{-- Card 2: Peminjaman --}}
        <div style="background: #fff; border: 1px solid #e0e0e0; padding: 24px; display: flex; flex-direction: column; justify-content: space-between; height: 200px;">
            <div>
                <h3 style="font-size: 16px; font-weight: 600; color: #000; margin: 0 0 8px;">Catat & Kelola Peminjaman</h3>
                <p style="font-size: 13px; color: #4d4d4d; margin: 0;">Mencatat peminjaman baru, memantau batas kembali, dan memproses pengembalian barang.</p>
            </div>
            <a href="{{ route('borrowings.index') }}" class="btn-primary" style="text-decoration: none; text-align: center; display: block; border-radius: 0;">Buka Modul Peminjaman</a>
        </div>
    </div>
</x-app-layout>
