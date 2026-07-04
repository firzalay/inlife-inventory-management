<?php

namespace App\Models;

use Database\Factories\BorrowingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

#[Fillable(['borrower_name', 'borrow_date', 'due_date', 'return_date', 'status'])]
class Borrowing extends Model
{
    /** @use HasFactory<BorrowingFactory> */
    use HasFactory;

    /**
     * Boot the model and clear stats cache on saved/deleted events.
     */
    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('dashboard_stats');
        });

        static::deleted(function () {
            Cache::forget('dashboard_stats');
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'borrow_date' => 'date',
            'due_date' => 'date',
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

    /**
     * Get computed/dynamic status of the borrowing.
     * Overdue is determined dynamically if return_date is null and today > due_date.
     */
    public function getComputedStatusAttribute(): string
    {
        if ($this->status === 'returned') {
            return 'returned';
        }

        if (now()->startOfDay()->gt($this->due_date)) {
            return 'overdue';
        }

        return 'borrowed';
    }
}
