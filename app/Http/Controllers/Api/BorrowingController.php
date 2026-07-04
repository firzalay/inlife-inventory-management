<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBorrowingRequest;
use App\Http\Resources\BorrowingResource;
use App\Models\Borrowing;
use App\Models\BorrowingDetail;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BorrowingController extends Controller
{
    use ApiResponse;

    /**
     * Get list of borrowings with pagination and filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Borrowing::with('details.product')->latest();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('borrower_name', 'like', "%{$search}%");
        }

        if ($request->filled('start_date')) {
            $query->whereDate('borrow_date', '>=', $request->date('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('borrow_date', '<=', $request->date('end_date'));
        }

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

        $borrowings = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar transaksi peminjaman berhasil diambil.',
            'data' => BorrowingResource::collection($borrowings),
            'pagination' => [
                'total' => $borrowings->total(),
                'per_page' => $borrowings->perPage(),
                'current_page' => $borrowings->currentPage(),
                'last_page' => $borrowings->lastPage(),
            ],
        ]);
    }

    /**
     * Get details of a single borrowing transaction.
     */
    public function show(int $id): JsonResponse
    {
        $borrowing = Borrowing::with('details.product')->find($id);

        if (! $borrowing) {
            return $this->errorResponse('Transaksi peminjaman tidak ditemukan.', 404);
        }

        return $this->successResponse(new BorrowingResource($borrowing), 'Rincian transaksi peminjaman berhasil diambil.');
    }

    /**
     * Record a new borrowing transaction (Admin/Staff only).
     */
    public function store(StoreBorrowingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $borrowing = DB::transaction(function () use ($validated) {
            $newBorrowing = Borrowing::create([
                'borrower_name' => $validated['borrower_name'],
                'borrow_date' => $validated['borrow_date'],
                'due_date' => $validated['due_date'],
                'status' => 'borrowed',
            ]);

            foreach ($validated['items'] as $item) {
                // Lock the product row for update to prevent race conditions
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                BorrowingDetail::create([
                    'borrowing_id' => $newBorrowing->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                ]);

                $product->decrement('stock', $item['quantity']);
            }

            return $newBorrowing;
        });

        $borrowing->load('details.product');

        return $this->successResponse(new BorrowingResource($borrowing), 'Peminjaman berhasil dicatat.', 201);
    }

    /**
     * Process return of goods (Admin/Staff only).
     */
    public function returnGoods(Request $request, int $id): JsonResponse
    {
        $borrowing = Borrowing::with('details.product')->find($id);

        if (! $borrowing) {
            return $this->errorResponse('Transaksi peminjaman tidak ditemukan.', 404);
        }

        if ($borrowing->status === 'returned') {
            return $this->errorResponse('Barang dari transaksi ini sudah dikembalikan sebelumnya.', 400);
        }

        $validated = $request->validate([
            'conditions' => ['required', 'array'],
            'conditions.*' => ['required', 'string', 'in:Baik,Rusak,Hilang'],
        ]);

        DB::transaction(function () use ($validated, $borrowing) {
            $borrowing->update([
                'return_date' => now()->toDateString(),
                'status' => 'returned',
            ]);

            $conditions = $validated['conditions'];

            foreach ($borrowing->details as $detail) {
                $condition = $conditions[$detail->id] ?? 'Baik';
                $detail->update([
                    'condition_on_return' => $condition,
                ]);

                // Restore stock locking the product row to avoid race conditions
                $product = Product::lockForUpdate()->find($detail->product_id);
                if ($product) {
                    $product->increment('stock', $detail->quantity);
                }
            }
        });

        $borrowing->refresh()->load('details.product');

        return $this->successResponse(new BorrowingResource($borrowing), 'Barang berhasil dikembalikan ke inventaris.');
    }
}
