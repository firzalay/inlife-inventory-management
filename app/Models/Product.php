<?php

namespace App\Models;

use App\Notifications\LowStockNotification;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

#[Fillable(['code', 'name', 'category_id', 'stock_baik', 'stock_rusak', 'stock_perlu_perbaikan', 'location', 'image'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Boot the model and listen to stock changes to trigger notifications.
     */
    protected static function booted(): void
    {
        static::saved(function (Product $product) {
            Cache::forget('dashboard_stats');

            $threshold = config('inventory.low_stock_threshold', 5);

            // Only notify if stock_baik was changed and has reached or dropped below threshold
            if (
                $product->stock_baik <= $threshold
                && ($product->wasChanged('stock_baik') || $product->wasRecentlyCreated)
            ) {
                $recipients = User::role(['Admin', 'Staff'])->get();
                foreach ($recipients as $user) {
                    $user->notify(new LowStockNotification($product));
                }
            }
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
            'stock_baik' => 'integer',
            'stock_rusak' => 'integer',
            'stock_perlu_perbaikan' => 'integer',
        ];
    }

    /**
     * Get total stock across all conditions.
     */
    public function getTotalStockAttribute(): int
    {
        return $this->stock_baik + $this->stock_rusak + $this->stock_perlu_perbaikan;
    }

    /**
     * Get available stock (only good-condition units eligible for borrowing).
     */
    public function getAvailableStockAttribute(): int
    {
        return $this->stock_baik;
    }

    /**
     * Get the category this product belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all borrowing detail lines for this product.
     */
    public function borrowingDetails(): HasMany
    {
        return $this->hasMany(BorrowingDetail::class);
    }
}
