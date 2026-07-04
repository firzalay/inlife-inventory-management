<?php

namespace App\Models;

use Database\Factories\BorrowingDetailFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['borrowing_id', 'product_id', 'quantity', 'condition_on_return'])]
class BorrowingDetail extends Model
{
    /** @use HasFactory<BorrowingDetailFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    /**
     * Get the parent borrowing record.
     */
    public function borrowing(): BelongsTo
    {
        return $this->belongsTo(Borrowing::class);
    }

    /**
     * Get the product associated with this detail line.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
