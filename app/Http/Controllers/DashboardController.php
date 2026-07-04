<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected DashboardService $dashboardService)
    {
        //
    }

    /**
     * Display the dashboard view.
     */
    public function index(): View
    {
        // Cache stats for 5 minutes (300 seconds)
        $stats = Cache::remember('dashboard_stats', 300, function () {
            return $this->dashboardService->getStats();
        });

        $chartData = $this->dashboardService->getMonthlyBorrowingsData();
        $lowStockProducts = $this->dashboardService->getLowStockProducts();

        return view('dashboard', compact('stats', 'chartData', 'lowStockProducts'));
    }
}
