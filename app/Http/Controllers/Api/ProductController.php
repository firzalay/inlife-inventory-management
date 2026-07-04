<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    use ApiResponse;

    /**
     * Get paginated products with search and category filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category')->latest();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        $products = $query->paginate(12);

        return response()->json([
            'success' => true,
            'message' => 'Daftar barang berhasil diambil.',
            'data' => ProductResource::collection($products),
            'pagination' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ],
        ]);
    }

    /**
     * Get details of a single product.
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with('category')->find($id);

        if (! $product) {
            return $this->errorResponse('Barang tidak ditemukan.', 404);
        }

        return $this->successResponse(new ProductResource($product), 'Rincian barang berhasil diambil.');
    }

    /**
     * Store a new product in inventory (Admin/Staff only).
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);
        $product->load('category');

        return $this->successResponse(new ProductResource($product), 'Barang berhasil ditambahkan.', 201);
    }

    /**
     * Update details of an existing product (Admin/Staff only).
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = Product::find($id);

        if (! $product) {
            return $this->errorResponse('Barang tidak ditemukan.', 404);
        }

        $validated = $request->validated();

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);
        $product->load('category');

        return $this->successResponse(new ProductResource($product), 'Barang berhasil diperbarui.');
    }

    /**
     * Soft delete a product from inventory (Admin/Staff only).
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (! $product) {
            return $this->errorResponse('Barang tidak ditemukan.', 404);
        }

        $product->delete();

        return $this->successResponse(null, 'Barang berhasil dihapus.');
    }
}
