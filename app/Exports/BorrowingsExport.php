<?php

namespace App\Exports;

use App\Models\BorrowingDetail;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BorrowingsExport implements FromQuery, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = BorrowingDetail::with(['borrowing', 'product'])->latest('id');

        $query->whereHas('borrowing', function ($q) {
            if (! empty($this->filters['search'])) {
                $search = $this->filters['search'];
                $q->where('borrower_name', 'like', "%{$search}%");
            }

            if (! empty($this->filters['start_date'])) {
                $q->whereDate('borrow_date', '>=', $this->filters['start_date']);
            }

            if (! empty($this->filters['end_date'])) {
                $q->whereDate('borrow_date', '<=', $this->filters['end_date']);
            }

            if (! empty($this->filters['status'])) {
                $status = $this->filters['status'];
                if ($status === 'returned') {
                    $q->where('status', 'returned');
                } elseif ($status === 'overdue') {
                    $q->where('status', 'borrowed')
                        ->whereDate('due_date', '<', now()->startOfDay());
                } elseif ($status === 'borrowed') {
                    $q->where('status', 'borrowed')
                        ->whereDate('due_date', '>=', now()->startOfDay());
                }
            }
        });

        return $query;
    }

    public function headings(): array
    {
        return [
            'Nama Peminjam',
            'Kode Barang',
            'Nama Barang',
            'Jumlah',
            'Tanggal Pinjam',
            'Batas Pengembalian',
            'Tanggal Kembali',
            'Kondisi Kembali',
            'Status',
        ];
    }

    /**
     * @param  BorrowingDetail  $detail
     */
    public function map($detail): array
    {
        $borrowing = $detail->borrowing;

        $computedStatus = $borrowing?->computed_status;
        $statusLabel = match ($computedStatus) {
            'returned' => 'Dikembalikan',
            'overdue' => 'Terlambat',
            'borrowed' => 'Dipinjam',
            default => '—',
        };

        return [
            $borrowing?->borrower_name ?? '—',
            $detail->product?->code ?? '—',
            $detail->product?->name ?? 'Barang Terhapus',
            $detail->quantity,
            $borrowing?->borrow_date ? $borrowing->borrow_date->format('d/m/Y') : '—',
            $borrowing?->due_date ? $borrowing->due_date->format('d/m/Y') : '—',
            $borrowing?->return_date ? $borrowing->return_date->format('d/m/Y') : '—',
            $detail->condition_on_return ?? '—',
            $statusLabel,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 15,
            'C' => 25,
            'D' => 10,
            'E' => 15,
            'F' => 18,
            'G' => 15,
            'H' => 18,
            'I' => 15,
        ];
    }
}
