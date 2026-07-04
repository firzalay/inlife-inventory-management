<?php

namespace App\Services;

use App\Models\Borrowing;
use App\Models\BorrowingDetail;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * Get main inventory statistics.
     *
     * @return array<string, int>
     */
    public function getStats(): array
    {
        $totalCategories = Category::count();
        $totalProductTypes = Product::count();

        // Total units across all conditions
        $totalProductUnits = (int) Product::selectRaw(
            'SUM(stock_baik + stock_rusak + stock_perlu_perbaikan) as total'
        )->value('total');

        // Sum quantity in borrowing_details where borrowing status is 'borrowed'
        $borrowedUnits = (int) BorrowingDetail::whereHas('borrowing', function ($q) {
            $q->where('status', 'borrowed');
        })->sum('quantity');

        // Available units = only good-condition stock eligible for borrowing
        $availableUnits = (int) Product::sum('stock_baik');

        return [
            'total_categories' => $totalCategories,
            'total_product_types' => $totalProductTypes,
            'total_product_units' => $totalProductUnits,
            'borrowed_units' => $borrowedUnits,
            'available_units' => $availableUnits,
        ];
    }

    /**
     * Get monthly borrowing transaction counts for the last 12 months.
     * Compatible with both SQLite and MySQL (processed in PHP).
     *
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public function getMonthlyBorrowingsData(): array
    {
        $dataMap = [];

        // Build data map for the last 12 months (covering 0 value fallback)
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $key = $date->format('Y-m');
            $label = $date->translatedFormat('M Y');
            $dataMap[$key] = [
                'label' => $label,
                'count' => 0,
            ];
        }

        // Fetch borrowings in range
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $borrowings = Borrowing::whereDate('borrow_date', '>=', $startDate)->get();

        // Populate counts
        foreach ($borrowings as $borrowing) {
            if ($borrowing->borrow_date) {
                $key = $borrowing->borrow_date->format('Y-m');
                if (isset($dataMap[$key])) {
                    $dataMap[$key]['count']++;
                }
            }
        }

        $labels = [];
        $counts = [];

        foreach ($dataMap as $item) {
            $labels[] = $item['label'];
            $counts[] = $item['count'];
        }

        return [
            'labels' => $labels,
            'data' => $counts,
        ];
    }

    /**
     * Get list of products with low good-condition stock (below or equal to threshold).
     *
     * @return Collection<int, Product>
     */
    public function getLowStockProducts(): Collection
    {
        $threshold = config('inventory.low_stock_threshold', 5);

        return Product::with('category')
            ->where('stock_baik', '<=', $threshold)
            ->orderBy('stock_baik')
            ->get();
    }
}
