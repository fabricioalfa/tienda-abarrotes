<?php
// filepath: /home/fabri/Documentos/tienda/app/Http/Controllers/ProductController.php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Category;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function __construct(private readonly InventoryService $inventoryService)
    {
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));

        $products = Product::with('category')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('name', 'like', "%{$q}%")
                        ->orWhere('barcode', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('products.index', compact('products', 'q'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request, null, true);

        $initialStock = (float) ($data['initial_stock'] ?? 0);
        unset($data['initial_stock']);

        $product = Product::create($data);

        if ($initialStock > 0) {
            $this->inventoryService->registerMovement(
                product: $product,
                movementType: InventoryMovement::TYPE_ENTRY,
                direction: InventoryMovement::DIRECTION_IN,
                quantity: $initialStock,
                reason: 'Stock inicial',
                reference: null,
                userId: $request->user()?->id,
            );
        }

        return redirect()->route('products.index')->with('status', 'Producto creado correctamente.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validatedData($request, $product, false);

        $product->update($data);

        return redirect()->route('products.index')->with('status', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('status', 'Producto eliminado correctamente.');
    }

    protected function validatedData(Request $request, ?Product $product = null, bool $isCreate = false): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'barcode' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'barcode')->ignore($product?->id),
            ],
            'category_id' => ['required', 'exists:categories,id'],
            'sale_type' => ['required', Rule::in(['weight', 'unit'])],
            'weight_unit' => ['nullable', Rule::in(['kg', 'g'])],
            'price' => ['required', 'numeric', 'min:0'],
            'initial_stock' => [$isCreate ? 'nullable' : 'prohibited', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($data['sale_type'] === 'weight' && empty($data['weight_unit'])) {
            throw ValidationException::withMessages([
                'weight_unit' => 'La unidad de peso es obligatoria para productos por peso.',
            ]);
        }

        if ($data['sale_type'] === 'unit') {
            $data['weight_unit'] = null;

            if ($isCreate && isset($data['initial_stock']) && floor((float) $data['initial_stock']) !== (float) $data['initial_stock']) {
                throw ValidationException::withMessages([
                    'initial_stock' => 'Los productos por unidad solo aceptan stock entero.',
                ]);
            }
        }

        if (! $isCreate) {
            unset($data['initial_stock']);
        }

        if ($product === null) {
            $data['stock'] = 0;
        }

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}