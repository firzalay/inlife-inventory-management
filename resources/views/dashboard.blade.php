<x-app-layout>
    <div class="c-page-header">
        <h1>Dashboard</h1>
        <p>Ringkasan status dan aktivitas inventaris barang</p>
    </div>

    {{-- Grid: 4 Stat Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        {{-- Stat Card 1 --}}
        <div style="background: #fff; border: 1px solid #e0e0e0; padding: 20px;">
            <p style="font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; text-transform: uppercase; margin: 0 0 8px;">Total Jenis Barang</p>
            <h2 style="font-size: 32px; font-weight: 300; color: #000; margin: 0;">{{ number_format($stats['total_product_types']) }}</h2>
            <p style="font-size: 12px; color: #8c8c8c; margin: 4px 0 0;">{{ number_format($stats['total_categories']) }} Kategori terdaftar</p>
        </div>

        {{-- Stat Card 2 --}}
        <div style="background: #fff; border: 1px solid #e0e0e0; padding: 20px;">
            <p style="font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; text-transform: uppercase; margin: 0 0 8px;">Total Unit Fisik</p>
            <h2 style="font-size: 32px; font-weight: 300; color: #000; margin: 0;">{{ number_format($stats['total_product_units']) }}</h2>
            <p style="font-size: 12px; color: #8c8c8c; margin: 4px 0 0;">Unit keseluruhan di gudang</p>
        </div>

        {{-- Stat Card 3 --}}
        <div style="background: #fff; border: 1px solid #e0e0e0; padding: 20px;">
            <p style="font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; text-transform: uppercase; margin: 0 0 8px;">Barang Dipinjam</p>
            <h2 style="font-size: 32px; font-weight: 300; color: #ff0d00; margin: 0;">{{ number_format($stats['borrowed_units']) }}</h2>
            <p style="font-size: 12px; color: #8c8c8c; margin: 4px 0 0;">Unit sedang berada di luar</p>
        </div>

        {{-- Stat Card 4 --}}
        <div style="background: #fff; border: 1px solid #e0e0e0; padding: 20px;">
            <p style="font-size: 12px; color: #4d4d4d; letter-spacing: 0.32px; text-transform: uppercase; margin: 0 0 8px;">Barang Tersedia</p>
            <h2 style="font-size: 32px; font-weight: 300; color: #24a148; margin: 0;">{{ number_format($stats['available_units']) }}</h2>
            <p style="font-size: 12px; color: #8c8c8c; margin: 4px 0 0;">Unit kondisi baik di gudang</p>
        </div>

    </div>

    {{-- Layout: Left (Chart) & Right (Low Stock Alerts) --}}
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start; margin-bottom: 24px;">
        
        {{-- Monthly Graph Card --}}
        <div style="background: #fff; border: 1px solid #e0e0e0; padding: 24px;">
            <div style="border-bottom: 1px solid #e0e0e0; padding-bottom: 12px; margin-bottom: 20px;">
                <h3 style="font-size: 16px; font-weight: 600; color: #000; margin: 0;">Aktivitas Peminjaman per Bulan</h3>
                <p style="font-size: 12px; color: #4d4d4d; margin: 4px 0 0;">Grafik jumlah transaksi peminjaman selama 12 bulan terakhir</p>
            </div>
            
            <div style="position: relative; width: 100%; height: 320px;">
                <canvas id="borrowingsMonthlyChart"></canvas>
            </div>
        </div>

        {{-- Low Stock Alerts Card --}}
        <div style="background: #fff; border: 1px solid #e0e0e0; padding: 24px;">
            <div style="border-bottom: 1px solid #e0e0e0; padding-bottom: 12px; margin-bottom: 20px;">
                <h3 style="font-size: 16px; font-weight: 600; color: #000; margin: 0;">Peringatan Stok Menipis</h3>
                <p style="font-size: 12px; color: #4d4d4d; margin: 4px 0 0;">Barang dengan ketersediaan di bawah batas minimal (stok &le; {{ config('inventory.low_stock_threshold', 5) }})</p>
            </div>

            @if($lowStockProducts->isEmpty())
                <div style="padding: 32px 16px; text-align: center; color: #24a148; background: #defbe6; border: 1px solid #24a148;">
                    <span style="font-size: 18px; margin-right: 8px;">&#10003;</span>
                    <span style="font-size: 13px; font-weight: 600;">Semua stok barang aman</span>
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach($lowStockProducts as $product)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: #f4f4f4; border-left: 3px solid {{ $product->stock_baik == 0 ? '#da1e28' : '#f1c21b' }};">
                            <div>
                                <h4 style="font-size: 13px; font-weight: 600; color: #000; margin: 0;">
                                    <a href="{{ route('products.show', $product) }}" style="color: #ff0d00; text-decoration: none;">
                                        {{ $product->name }}
                                    </a>
                                </h4>
                                <span style="font-size: 11px; color: #8c8c8c;">{{ $product->category?->name ?? '—' }} ({{ $product->code }})</span>
                            </div>
                            <div>
                                <span style="
                                    display: inline-block;
                                    padding: 2px 8px;
                                    font-size: 11px;
                                    font-weight: 600;
                                    background: {{ $product->stock_baik == 0 ? '#fff1f1' : '#fdf6dd' }};
                                    color: {{ $product->stock_baik == 0 ? '#da1e28' : '#8e6a00' }};
                                ">
                                    Sisa: {{ $product->stock_baik }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    {{-- ChartJS initialization --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('borrowingsMonthlyChart').getContext('2d');
            
            const labels = @json($chartData['labels']);
            const data = @json($chartData['data']);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Transaksi',
                        data: data,
                        backgroundColor: '#ff0d00', // Signal Red brand color
                        borderColor: '#ff0d00',
                        borderWidth: 1,
                        borderRadius: 0 // Flat corners
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: {
                                    family: 'IBM Plex Sans',
                                    size: 11
                                }
                            },
                            grid: {
                                color: '#e0e0e0'
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    family: 'IBM Plex Sans',
                                    size: 11
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
