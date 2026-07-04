<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBorrowingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole(['Admin', 'Staff']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'borrower_name' => ['required', 'string', 'max:255'],
            'borrow_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:borrow_date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Get the validation error messages / callbacks.
     */
    public function after(): array
    {
        return [
            function ($validator) {
                $items = $this->input('items', []);
                $productQuantities = [];

                // Aggregate quantities to prevent duplicate product selection bypass
                foreach ($items as $index => $item) {
                    if (isset($item['product_id']) && isset($item['quantity'])) {
                        $pId = $item['product_id'];
                        $qty = (int) $item['quantity'];
                        $productQuantities[$pId] = ($productQuantities[$pId] ?? 0) + $qty;
                    }
                }

                // Check stock for each aggregated product
                foreach ($productQuantities as $productId => $totalQty) {
                    $product = Product::find($productId);
                    if ($product) {
                        if ($product->stock < $totalQty) {
                            // Find the first index that contains this product to attach error
                            $errorIndex = 0;
                            foreach ($items as $index => $item) {
                                if (isset($item['product_id']) && $item['product_id'] == $productId) {
                                    $errorIndex = $index;
                                    break;
                                }
                            }
                            $validator->errors()->add(
                                "items.{$errorIndex}.quantity",
                                "Stok untuk barang '{$product->name}' tidak mencukupi. Tersedia: {$product->stock} unit, diminta: {$totalQty} unit."
                            );
                        }
                    }
                }
            },
        ];
    }

    /**
     * Human-readable attribute names.
     */
    public function attributes(): array
    {
        return [
            'borrower_name' => 'nama peminjam',
            'borrow_date' => 'tanggal pinjam',
            'due_date' => 'estimasi tanggal kembali',
            'items' => 'daftar barang',
            'items.*.product_id' => 'barang',
            'items.*.quantity' => 'jumlah',
        ];
    }
}
