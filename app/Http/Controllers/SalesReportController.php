<?php

namespace App\Http\Controllers;

use App\Models\ProductBatch;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReportController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        $request->validate([
            'from' => ['nullable', 'date', 'before_or_equal:today'],
            'to' => ['nullable', 'date', 'after_or_equal:from', 'before_or_equal:today'],
        ]);

        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        $sales = Sale::with(['user', 'items'])
            ->whereDate('sold_at', '>=', $from)
            ->whereDate('sold_at', '<=', $to)
            ->latest('sold_at')
            ->get();

        $filename = 'ventas_'.$from.'_al_'.$to.'.csv';

        return response()->streamDownload(function () use ($sales) {
            $handle = fopen('php://output', 'w');
            // BOM para Excel en español
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Nro Venta', 'Fecha', 'Cajero', 'Cliente', 'Método Pago', 'Items', 'Subtotal', 'Descuento', 'Total']);
            foreach ($sales as $sale) {
                fputcsv($handle, [
                    $sale->sale_number,
                    $sale->sold_at->format('d/m/Y H:i'),
                    $sale->user?->name ?? '-',
                    $sale->customer_name ?: 'Mostrador',
                    $sale->paymentMethodLabel(),
                    $sale->items->count(),
                    number_format((float) $sale->subtotal, 2, '.', ''),
                    number_format((float) $sale->discount_amount, 2, '.', ''),
                    number_format((float) $sale->total, 2, '.', ''),
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function index(Request $request)
    {
        // Validar fechas antes de usarlas en queries
        $request->validate([
            'from' => ['nullable', 'date', 'before_or_equal:today'],
            'to' => ['nullable', 'date', 'after_or_equal:from', 'before_or_equal:today'],
        ]);

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

        $topProducts = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->selectRaw('sale_items.product_name, SUM(sale_items.quantity) as sold_quantity, SUM(sale_items.line_total) as total_amount')
            ->whereDate('sales.sold_at', '>=', $from)
            ->whereDate('sales.sold_at', '<=', $to)
            ->groupBy('sale_items.product_name')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        $expiringBatches = ProductBatch::with('product')
            ->where('remaining_quantity', '>', 0)
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<=', now()->addDays(10)->toDateString())
            ->orderBy('expires_at')
            ->limit(10)
            ->get();

        return view('reports.sales', compact(
            'from',
            'to',
            'totalSalesAmount',
            'totalSalesCount',
            'averageTicket',
            'todayTotal',
            'dailyTotals',
            'salesHistory',
            'topProducts',
            'expiringBatches',
        ));
    }
}
