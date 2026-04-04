<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function registerMovement(
        Product $product,
        string $movementType,
        string $direction,
        float $quantity,
        ?string $reason = null,
        ?string $reference = null,
        ?int $userId = null,
        ?float $unitCost = null,
        ?string $expiresAt = null,
        ?string $supplierName = null,
    ): InventoryMovement {
        $normalizedQuantity = $this->normalizeQuantity($quantity);

        if ($normalizedQuantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'La cantidad debe ser mayor a cero.',
            ]);
        }

        return DB::transaction(function () use ($product, $movementType, $direction, $normalizedQuantity, $reason, $reference, $userId, $unitCost, $expiresAt, $supplierName) {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);

            $before = round((float) $lockedProduct->stock, 3);
            $after = $direction === InventoryMovement::DIRECTION_IN
                ? $before + $normalizedQuantity
                : $before - $normalizedQuantity;

            if ($after < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stock insuficiente para realizar la salida.',
                ]);
            }

            $after = round($after, 3);

            if ($direction === InventoryMovement::DIRECTION_OUT) {
                $this->consumeBatches($lockedProduct, $normalizedQuantity);
            }

            $lockedProduct->update([
                'stock' => $after,
            ]);

            if ($direction === InventoryMovement::DIRECTION_IN) {
                ProductBatch::create([
                    'product_id' => $lockedProduct->id,
                    'user_id' => $userId,
                    'quantity' => $normalizedQuantity,
                    'remaining_quantity' => $normalizedQuantity,
                    'unit_cost' => $unitCost,
                    'expires_at' => $expiresAt,
                    'supplier_name' => $supplierName ?: $lockedProduct->supplier_name,
                    'reference' => $reference ?: null,
                    'notes' => $reason ?: null,
                ]);
            }

            return InventoryMovement::create([
                'product_id' => $lockedProduct->id,
                'user_id' => $userId,
                'movement_type' => $movementType,
                'direction' => $direction,
                'quantity' => $normalizedQuantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'unit_cost' => $unitCost,
                'expires_at' => $expiresAt,
                'reference' => $reference ?: null,
                'reason' => $reason ?: null,
            ]);
        });
    }

    protected function consumeBatches(Product $product, float $quantity): void
    {
        $remaining = $quantity;

        $batches = ProductBatch::query()
            ->where('product_id', $product->id)
            ->where('remaining_quantity', '>', 0)
            ->orderByRaw('expires_at IS NULL')
            ->orderBy('expires_at')
            ->orderBy('created_at')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $available = round((float) $batch->remaining_quantity, 3);
            $used = min($available, $remaining);

            if ($used <= 0) {
                continue;
            }

            $batch->update([
                'remaining_quantity' => round($available - $used, 3),
            ]);

            $remaining = round($remaining - $used, 3);
        }
    }

    protected function normalizeQuantity(float $quantity): float
    {
        return round($quantity, 3);
    }
}
