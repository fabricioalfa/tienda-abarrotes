<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isCaja()) {
            return redirect()->route('dashboard.caja');
        }

        return redirect()->route('dashboard.admin');
    }

    public function adminIndex()
    {
        return $this->adminDashboard();
    }

    public function cajaIndex()
    {
        return $this->cajaDashboard();
    }

    private function cajaDashboard()
    {
        $currentRegister = CashRegister::current();
        $todaySales = Sale::whereDate('sold_at', now()->toDateString())->count();
        $todayTotal = (float) Sale::whereDate('sold_at', now()->toDateString())->sum('total');

        $expiringInFive = ProductBatch::where('remaining_quantity', '>', 0)
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '>=', now()->toDateString())
            ->whereDate('expires_at', '<=', now()->addDays(5)->toDateString())
            ->count();

        $lastSales = Sale::with('items')
            ->whereDate('sold_at', now()->toDateString())
            ->latest('sold_at')
            ->limit(5)
            ->get();

        $lowStockProducts = Product::where('is_active', true)
            ->whereRaw('stock <= minimum_stock')
            ->where('minimum_stock', '>', 0)
            ->orderBy('stock')
            ->get(['id', 'name', 'stock', 'minimum_stock', 'sale_type', 'weight_unit']);

        return view('dashboard-caja', compact(
            'currentRegister',
            'todaySales',
            'todayTotal',
            'expiringInFive',
            'lastSales',
            'lowStockProducts',
        ));
    }

    private function adminDashboard()
    {
        $productCount = Product::count();
        $activeProductCount = Product::where('is_active', true)->count();

        $expiringInFive = ProductBatch::where('remaining_quantity', '>', 0)
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '>=', now()->toDateString())
            ->whereDate('expires_at', '<=', now()->addDays(5)->toDateString())
            ->count();

        $expiringInTen = ProductBatch::where('remaining_quantity', '>', 0)
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '>', now()->addDays(5)->toDateString())
            ->whereDate('expires_at', '<=', now()->addDays(10)->toDateString())
            ->count();

        $currentRegister = CashRegister::current();

        $todayTotal = (float) Sale::whereDate('sold_at', now()->toDateString())->sum('total');
        $todaySales = Sale::whereDate('sold_at', now()->toDateString())->count();

        // Ventas de los últimos 7 días para el gráfico
        $salesLast7 = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();

            return [
                'date' => now()->subDays($daysAgo)->format('d/m'),
                'total' => (float) Sale::whereDate('sold_at', $date)->sum('total'),
                'count' => Sale::whereDate('sold_at', $date)->count(),
            ];
        });

        return view('dashboard', compact(
            'productCount',
            'activeProductCount',
            'expiringInFive',
            'expiringInTen',
            'currentRegister',
            'todayTotal',
            'todaySales',
            'salesLast7',
        ));
    }
}
