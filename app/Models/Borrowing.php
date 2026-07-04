<?php

namespace App\Models;

use Database\Factories\BorrowingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['borrower_name', 'borrow_date', 'return_date', 'status'])]
class Borrowing extends Model
{
    /** @use HasFactory<BorrowingFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'borrow_date' => 'date',
            'return_date' => 'date',
        ];
    }

    /**
     * Get all detail lines for this borrowing.
     */
    public function details(): HasMany
    {
        return $this->hasMany(BorrowingDetail::class);
    }
}
