<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SalesService;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(private readonly SalesService $salesService) {}

    public function index(Request $request)
    {
        // Validar parámetros de filtro antes de usarlos en queries
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

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
        $currentRegister = CashRegister::current();

        return view('sales.index', compact('sales', 'q', 'from', 'to', 'todaySales', 'todayTotal', 'currentRegister'));
    }

    public function create()
    {
        $products = Product::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $currentRegister = CashRegister::current();

        return view('sales.create', compact('products', 'currentRegister'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.pricing_mode' => ['nullable', 'in:standard,package'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:cash,qr,mixed,credit'],
            'cash_amount' => ['nullable', 'numeric', 'min:0'],
            'qr_amount' => ['nullable', 'numeric', 'min:0'],
            'cash_received' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $sale = $this->salesService->createSale(
            user: $request->user(),
            rawItems: $data['items'],
            saleData: [
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'payment_method' => $data['payment_method'],
                'cash_amount' => $data['cash_amount'] ?? 0,
                'qr_amount' => $data['qr_amount'] ?? 0,
                'cash_received' => $data['cash_received'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ],
        );

        return redirect()
            ->route('sales.show', $sale)
            ->with('status', 'Venta registrada correctamente.');
    }

    public function show(Sale $sale)
    {
        $sale->load(['items.product', 'user', 'cashRegister']);

        return view('sales.show', compact('sale'));
    }
}
