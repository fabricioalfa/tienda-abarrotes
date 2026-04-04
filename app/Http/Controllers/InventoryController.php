<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventoryService)
    {
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $categoryId = $request->get('category_id');
        $productId = $request->get('product_id');
        $movementType = $request->get('movement_type');

        $products = Product::with('category')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('name', 'like', "%{$q}%")
                        ->orWhere('barcode', 'like', "%{$q}%");
                });
            })
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->latest()
            ->paginate(10, ['*'], 'products_page')
            ->withQueryString();

        $movements = InventoryMovement::with(['product', 'user'])
            ->when($movementType, fn ($query) => $query->where('movement_type', $movementType))
            ->when($productId, fn ($query) => $query->where('product_id', $productId))
            ->latest()
            ->paginate(12, ['*'], 'movements_page')
            ->withQueryString();

        $categories = Category::orderBy('name')->get();
        $productOptions = Product::where('is_active', true)->orderBy('name')->get();

        $totalProducts = Product::count();
        $outOfStock = Product::where('stock', '<=', 0)->count();
        $lowStock = Product::where('stock', '>', 0)->where('stock', '<=', 5)->count();
        $todayMovements = InventoryMovement::whereDate('created_at', now()->toDateString())->count();
        $expiringBatches = ProductBatch::with('product')
            ->where('remaining_quantity', '>', 0)
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<=', now()->addDays(10)->toDateString())
            ->orderBy('expires_at')
            ->limit(10)
            ->get();
        $costHistory = ProductBatch::with('product')
            ->whereNotNull('unit_cost')
            ->latest()
            ->limit(10)
            ->get();

        return view('inventory.index', compact(
            'products',
            'movements',
            'categories',
            'productOptions',
            'q',
            'categoryId',
            'movementType',
            'productId',
            'totalProducts',
            'outOfStock',
            'lowStock',
            'todayMovements',
            'expiringBatches',
            'costHistory',
        ));
    }

    public function storeEntry(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'expires_at' => ['nullable', 'date'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $quantity = (float) $data['quantity'];

        $this->validateQuantityForSaleType($product, $quantity);

        if ($product->track_expiration && empty($data['expires_at'])) {
            throw ValidationException::withMessages([
                'expires_at' => 'La fecha de vencimiento es obligatoria para este producto.',
            ]);
        }

        $this->inventoryService->registerMovement(
            product: $product,
            movementType: InventoryMovement::TYPE_ENTRY,
            direction: InventoryMovement::DIRECTION_IN,
            quantity: $quantity,
            reason: $data['reason'] ?? 'Ingreso de stock',
            reference: $data['reference'] ?? null,
            userId: Auth::id(),
            unitCost: isset($data['unit_cost']) ? (float) $data['unit_cost'] : null,
            expiresAt: $data['expires_at'] ?? null,
            supplierName: $data['supplier_name'] ?? $product->supplier_name,
        );

        return back()->with('status', 'Entrada de inventario registrada correctamente.');
    }

    public function storeSale(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'reference' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $quantity = (float) $data['quantity'];

        $this->validateQuantityForSaleType($product, $quantity);

        $this->inventoryService->registerMovement(
            product: $product,
            movementType: InventoryMovement::TYPE_SALE,
            direction: InventoryMovement::DIRECTION_OUT,
            quantity: $quantity,
            reason: $data['reason'] ?? 'Salida por venta',
            reference: $data['reference'] ?? null,
            userId: Auth::id(),
        );

        return back()->with('status', 'Salida por venta registrada y stock actualizado.');
    }

    public function storeAdjustment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'direction' => ['required', 'in:in,out'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'expires_at' => ['nullable', 'date'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $quantity = (float) $data['quantity'];

        $this->validateQuantityForSaleType($product, $quantity);

        $this->inventoryService->registerMovement(
            product: $product,
            movementType: InventoryMovement::TYPE_ADJUSTMENT,
            direction: $data['direction'],
            quantity: $quantity,
            reason: $data['reason'],
            reference: null,
            userId: Auth::id(),
            unitCost: isset($data['unit_cost']) ? (float) $data['unit_cost'] : null,
            expiresAt: $data['expires_at'] ?? null,
            supplierName: $product->supplier_name,
        );

        return back()->with('status', 'Ajuste manual aplicado correctamente.');
    }

    protected function validateQuantityForSaleType(Product $product, float $quantity): void
    {
        if ($product->sale_type === Product::SALE_TYPE_UNIT && floor($quantity) !== $quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Los productos por unidad solo aceptan cantidades enteras.',
            ]);
        }
    }
}
