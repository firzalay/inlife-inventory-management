<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBorrowingRequest;
use App\Models\Borrowing;
use App\Models\BorrowingDetail;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BorrowingController extends Controller
{
    /**
     * Export borrowing history data to PDF with active filters.
     */
    public function exportPdf(Request $request): Response
    {
        $query = Borrowing::with('details.product')->latest();

        // Filter: Borrower name
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('borrower_name', 'like', "%{$search}%");
        }

        // Filter: Date Range (borrow_date)
        if ($request->filled('start_date')) {
            $query->whereDate('borrow_date', '>=', $request->date('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('borrow_date', '<=', $request->date('end_date'));
        }

        // Filter: Status (borrowed, returned, overdue)
        if ($request->filled('status')) {
            $status = $request->string('status');
            if ($status === 'returned') {
                $query->where('status', 'returned');
            } elseif ($status === 'overdue') {
                $query->where('status', 'borrowed')
                    ->whereDate('due_date', '<', now()->startOfDay());
            } elseif ($status === 'borrowed') {
                $query->where('status', 'borrowed')
                    ->whereDate('due_date', '>=', now()->startOfDay());
            }
        }

        $borrowings = $query->get();

        $pdf = Pdf::loadView('pdf.borrowings', [
            'borrowings' => $borrowings,
            'search' => $request->query('search'),
            'status' => $request->query('status'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'date' => now()->format('d M Y H:i'),
            'user' => auth()->user(),
        ]);

        return $pdf->stream('laporan-riwayat-peminjaman.pdf');
    }

    /**
     * Display a paginated list of borrowings with filters.
     */
    public function index(Request $request): View
    {
        $query = Borrowing::with('details.product')->latest();

        // Filter: Borrower name
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('borrower_name', 'like', "%{$search}%");
        }

        // Filter: Date Range (borrow_date)
        if ($request->filled('start_date')) {
            $query->whereDate('borrow_date', '>=', $request->date('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('borrow_date', '<=', $request->date('end_date'));
        }

        // Filter: Status (borrowed, returned, overdue)
        if ($request->filled('status')) {
            $status = $request->string('status');
            if ($status === 'returned') {
                $query->where('status', 'returned');
            } elseif ($status === 'overdue') {
                $query->where('status', 'borrowed')
                    ->whereDate('due_date', '<', now()->startOfDay());
            } elseif ($status === 'borrowed') {
                $query->where('status', 'borrowed')
                    ->whereDate('due_date', '>=', now()->startOfDay());
            }
        }

        $borrowings = $query->paginate(10)->withQueryString();

        return view('borrowings.index', compact('borrowings'));
    }

    /**
     * Show the form for creating a new borrowing.
     */
    public function create(): View
    {
        // Only products with available (good-condition) stock can be borrowed
        $products = Product::where('stock_baik', '>', 0)
            ->orderBy('name')
            ->get();

        return view('borrowings.create', compact('products'));
    }

    /**
     * Store a newly created borrowing in storage.
     */
    public function store(StoreBorrowingRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $borrowing = Borrowing::create([
                'borrower_name' => $validated['borrower_name'],
                'borrow_date' => $validated['borrow_date'],
                'due_date' => $validated['due_date'],
                'status' => 'borrowed',
            ]);

            foreach ($validated['items'] as $item) {
                BorrowingDetail::create([
                    'borrowing_id' => $borrowing->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);

                // Deduct from good-condition stock only
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $product->decrement('stock_baik', $item['quantity']);
            }
        });

        return redirect()->route('borrowings.index')
            ->with('success', 'Transaksi peminjaman berhasil dicatat.');
    }

    /**
     * Display details of a specific borrowing.
     */
    public function show(Borrowing $borrowing): View
    {
        $borrowing->load('details.product');

        return view('borrowings.show', compact('borrowing'));
    }

    /**
     * Process return of goods and route returned stock to the correct condition column.
     */
    public function returnGoods(Request $request, Borrowing $borrowing): RedirectResponse
    {
        // Manager is not allowed
        if (! auth()->user()->hasRole(['Admin', 'Staff'])) {
            abort(403);
        }

        if ($borrowing->status === 'returned') {
            return redirect()->back()->with('error', 'Transaksi ini sudah dikembalikan.');
        }

        $request->validate([
            'conditions' => ['required', 'array'],
            'conditions.*' => ['required', 'string', 'in:Baik,Rusak,Perlu Perbaikan,Hilang'],
        ]);

        DB::transaction(function () use ($request, $borrowing) {
            $borrowing->update([
                'return_date' => now()->toDateString(),
                'status' => 'returned',
            ]);

            $conditions = $request->input('conditions');

            foreach ($borrowing->details as $detail) {
                $condition = $conditions[$detail->id] ?? 'Baik';
                $detail->update([
                    'condition_on_return' => $condition,
                ]);

                // Route returned units back to the appropriate condition stock column
                $product = Product::lockForUpdate()->find($detail->product_id);
                if ($product) {
                    match ($condition) {
                        'Baik' => $product->increment('stock_baik', $detail->quantity),
                        'Rusak' => $product->increment('stock_rusak', $detail->quantity),
                        'Perlu Perbaikan' => $product->increment('stock_perlu_perbaikan', $detail->quantity),
                        default => null, // 'Hilang' — no stock restoration
                    };
                }
            }
        });

        return redirect()->route('borrowings.show', $borrowing)
            ->with('success', 'Barang berhasil dikembalikan ke inventaris.');
    }
}
