<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        $baseQuery = Sale::query()
            ->whereDate('sold_at', '>=', $from)
            ->whereDate('sold_at', '<=', $to);

        $totalSalesAmount = (float) (clone $baseQuery)->sum('total');
        $totalSalesCount = (int) (clone $baseQuery)->count();
        $averageTicket = $totalSalesCount > 0 ? round($totalSalesAmount / $totalSalesCount, 2) : 0;
        $todayTotal = (float) Sale::whereDate('sold_at', now()->toDateString())->sum('total');

        $dailyTotals = (clone $baseQuery)
            ->selectRaw('DATE(sold_at) as day, COUNT(*) as sales_count, SUM(total) as amount')
            ->groupBy(DB::raw('DATE(sold_at)'))
            ->orderBy('day', 'desc')
            ->limit(60)
            ->get();

        $salesHistory = (clone $baseQuery)
            ->with(['user', 'items'])
            ->latest('sold_at')
            ->paginate(15)
            ->withQueryString();

        return view('reports.sales', compact(
            'from',
            'to',
            'totalSalesAmount',
            'totalSalesCount',
            'averageTicket',
            'todayTotal',
            'dailyTotals',
            'salesHistory',
        ));
    }
}
