<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'stock_baik' => (int) $this->stock_baik,
            'stock_rusak' => (int) $this->stock_rusak,
            'stock_perlu_perbaikan' => (int) $this->stock_perlu_perbaikan,
            'total_stock' => $this->total_stock,
            'available_stock' => $this->available_stock,
            'location' => $this->location,
            'image_url' => $this->image ? asset(Storage::url($this->image)) : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
