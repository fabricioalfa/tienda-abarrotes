<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesService
{
    public function __construct(private readonly InventoryService $inventoryService)
    {
    }

    public function createSale(User $user, array $rawItems, ?string $notes = null): Sale
    {
        $items = $this->mergeItemsByProduct($rawItems);

        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => 'Debe registrar al menos un item para completar la venta.',
            ]);
        }

        return DB::transaction(function () use ($user, $items, $notes) {
            $sale = Sale::create([
                'sale_number' => 'PENDING',
                'user_id' => $user->id,
                'subtotal' => 0,
                'total' => 0,
                'sold_at' => now(),
                'notes' => $notes ?: null,
            ]);

            $saleNumber = 'V-' . now()->format('Ymd') . '-' . str_pad((string) $sale->id, 5, '0', STR_PAD_LEFT);
            $subtotal = 0;

            foreach ($items as $item) {
                $product = Product::query()->findOrFail($item['product_id']);
                $quantity = round((float) $item['quantity'], 3);

                if ($quantity <= 0) {
                    throw ValidationException::withMessages([
                        'items' => 'La cantidad del producto debe ser mayor a cero.',
                    ]);
                }

                if ($product->sale_type === Product::SALE_TYPE_UNIT && floor($quantity) !== $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "El producto {$product->name} solo acepta cantidades enteras.",
                    ]);
                }

                if (! $product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => "El producto {$product->name} no esta activo para venta.",
                    ]);
                }

                $lineTotal = round((float) $product->price * $quantity, 2);
                $subtotal += $lineTotal;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sale_type' => $product->sale_type,
                    'weight_unit' => $product->weight_unit,
                    'unit_price' => $product->price,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ]);

                $this->inventoryService->registerMovement(
                    product: $product,
                    movementType: InventoryMovement::TYPE_SALE,
                    direction: InventoryMovement::DIRECTION_OUT,
                    quantity: $quantity,
                    reason: 'Salida por venta en caja',
                    reference: $saleNumber,
                    userId: $user->id,
                );
            }

            $sale->update([
                'sale_number' => $saleNumber,
                'subtotal' => round($subtotal, 2),
                'total' => round($subtotal, 2),
            ]);

            return $sale->load(['items.product', 'user']);
        });
    }

    protected function mergeItemsByProduct(array $rawItems): array
    {
        $merged = [];

        foreach ($rawItems as $rawItem) {
            $productId = (int) ($rawItem['product_id'] ?? 0);
            $quantity = (float) ($rawItem['quantity'] ?? 0);

            if ($productId <= 0 || $quantity <= 0) {
                continue;
            }

            if (! isset($merged[$productId])) {
                $merged[$productId] = [
                    'product_id' => $productId,
                    'quantity' => 0,
                ];
            }

            $merged[$productId]['quantity'] += $quantity;
        }

        return array_values($merged);
    }
}
