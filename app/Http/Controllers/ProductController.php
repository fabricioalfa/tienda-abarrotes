<?php

// filepath: /home/fabri/Documentos/tienda/app/Http/Controllers/ProductController.php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function __construct(private readonly InventoryService $inventoryService) {}

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));

        $products = Product::with('category')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    if (ctype_digit($q)) {
                        $subQuery->where('id', (int) $q)
                            ->orWhere('code', 'like', "%{$q}%")
                            ->orWhere('name', 'like', "%{$q}%")
                            ->orWhere('barcode', 'like', "%{$q}%");

                        return;
                    }

                    $subQuery->where('code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%")
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
        $initialCost = isset($data['initial_cost']) ? (float) $data['initial_cost'] : null;
        $initialExpiresAt = $data['initial_expires_at'] ?? null;
        unset($data['initial_stock']);
        unset($data['initial_cost'], $data['initial_expires_at']);

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
                unitCost: $initialCost,
                expiresAt: $initialExpiresAt,
                supplierName: $product->supplier_name,
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
        $typeKey = (string) $request->input('type_key', '');

        if ($typeKey === '') {
            // Backward compatibility with old payloads.
            $legacySaleType = (string) $request->input('sale_type', '');
            $legacyWeightUnit = (string) $request->input('weight_unit', '');
            $typeKey = $legacySaleType === Product::SALE_TYPE_UNIT ? 'unit' : $legacyWeightUnit;
        }

        $request->merge(['type_key' => $typeKey]);

        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:255',
                'regex:/^[0-9]+$/',
                Rule::unique('products', 'code')->ignore($product?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'barcode' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'barcode')->ignore($product?->id),
            ],
            'category_id' => ['required', 'exists:categories,id'],
            'type_key' => ['required', Rule::in(['unit', 'kg', 'lb', 'quarter'])],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'initial_stock' => [$isCreate ? 'nullable' : 'prohibited', 'numeric', 'min:0', 'max:9999999.999'],
            'initial_cost' => [$isCreate ? 'nullable' : 'prohibited', 'numeric', 'min:0', 'max:9999999.99'],
            'initial_expires_at' => [$isCreate ? 'nullable' : 'prohibited', 'date'],
            'minimum_stock' => ['nullable', 'numeric', 'min:0', 'max:9999999.999'],
            'track_expiration' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            // Campos de venta por paquete — solo aplican a productos por unidad
            'allows_package_sale' => ['nullable', 'boolean'],
            'package_name' => ['nullable', 'string', 'max:100'],
            'units_per_package' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'package_price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
        ]);

        if ($data['type_key'] === 'unit') {
            $data['sale_type'] = Product::SALE_TYPE_UNIT;
            $data['weight_unit'] = null;

            if ($isCreate && isset($data['initial_stock']) && floor((float) $data['initial_stock']) !== (float) $data['initial_stock']) {
                throw ValidationException::withMessages([
                    'initial_stock' => 'Los productos por unidad solo aceptan stock entero.',
                ]);
            }
        } else {
            $data['sale_type'] = Product::SALE_TYPE_WEIGHT;
            $data['weight_unit'] = $data['type_key'];
            // Weight products cannot use package sale mode
            $data['allows_package_sale'] = false;
            $data['package_name'] = null;
            $data['units_per_package'] = null;
            $data['package_price'] = null;
        }

        // Package sale fields (only for unit products) — valores ya validados en $data
        if ($data['type_key'] === 'unit') {
            $data['allows_package_sale'] = (bool) ($data['allows_package_sale'] ?? false);
            if ($data['allows_package_sale']) {
                $data['package_name'] = ($data['package_name'] ?? null) ?: null;
                $data['units_per_package'] = isset($data['units_per_package']) ? (int) $data['units_per_package'] : null;
                $data['package_price'] = isset($data['package_price']) ? (float) $data['package_price'] : null;
            } else {
                $data['package_name'] = null;
                $data['units_per_package'] = null;
                $data['package_price'] = null;
            }
        }

        $data['brand'] = null;
        $data['supplier_name'] = null;
        $data['description'] = null;
        $data['track_expiration'] = $request->boolean('track_expiration');
        $data['barcode'] = ($data['barcode'] ?? null) ?: $data['code'];
        $data['sale_price'] = $data['price'];
        $data['purchase_price'] = isset($data['initial_cost']) ? (float) $data['initial_cost'] : 0;
        $data['unit'] = $data['type_key'] === 'unit' ? 'unidad' : $data['type_key'];
        $data['category'] = Category::query()->where('id', $data['category_id'])->value('name');

        $data['minimum_stock'] = (float) ($data['minimum_stock'] ?? 0);
        unset($data['type_key']);

        if (! $isCreate) {
            unset($data['initial_stock'], $data['initial_cost'], $data['initial_expires_at']);
        }

        if ($product === null) {
            $data['stock'] = 0;
        }

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
