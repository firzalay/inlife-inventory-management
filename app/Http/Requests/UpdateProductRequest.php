<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
        $product = $this->route('product');
        $productId = is_object($product) ? $product->id : $product;

        return [
            'code' => ['required', 'string', 'max:50', "unique:products,code,{$productId}"],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'stock' => ['required', 'integer', 'min:0'],
            'location' => ['required', 'string', 'max:255'],
            'condition' => ['required', 'in:good,damaged,lost'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    /**
     * Get the human-readable attribute names for error messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'code' => 'kode barang',
            'name' => 'nama barang',
            'category_id' => 'kategori',
            'stock' => 'stok',
            'location' => 'lokasi penyimpanan',
            'condition' => 'kondisi barang',
            'image' => 'gambar barang',
        ];
    }
}
