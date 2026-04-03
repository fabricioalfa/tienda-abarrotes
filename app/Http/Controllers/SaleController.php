<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Services\SalesService;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(private readonly SalesService $salesService)
    {
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $from = $request->get('from');
        $to = $request->get('to');

        $sales = Sale::with(['user', 'items'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('sale_number', 'like', "%{$q}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$q}%"));
                });
            })
            ->when($from, fn ($query) => $query->whereDate('sold_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('sold_at', '<=', $to))
            ->latest('sold_at')
            ->paginate(12)
            ->withQueryString();

        $todaySales = Sale::whereDate('sold_at', now()->toDateString())->count();
        $todayTotal = (float) Sale::whereDate('sold_at', now()->toDateString())->sum('total');

        return view('sales.index', compact('sales', 'q', 'from', 'to', 'todaySales', 'todayTotal'));
    }

    public function create()
    {
        $products = Product::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('sales.create', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $sale = $this->salesService->createSale(
            user: $request->user(),
            rawItems: $data['items'],
            notes: $data['notes'] ?? null,
        );

        return redirect()
            ->route('sales.show', $sale)
            ->with('status', 'Venta registrada correctamente.');
    }

    public function show(Sale $sale)
    {
        $sale->load(['items.product', 'user']);

        return view('sales.show', compact('sale'));
    }
}
