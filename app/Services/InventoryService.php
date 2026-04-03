<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
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
    ): InventoryMovement {
        $normalizedQuantity = $this->normalizeQuantity($quantity);

        if ($normalizedQuantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'La cantidad debe ser mayor a cero.',
            ]);
        }

        return DB::transaction(function () use ($product, $movementType, $direction, $normalizedQuantity, $reason, $reference, $userId) {
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

            $lockedProduct->update([
                'stock' => $after,
            ]);

            return InventoryMovement::create([
                'product_id' => $lockedProduct->id,
                'user_id' => $userId,
                'movement_type' => $movementType,
                'direction' => $direction,
                'quantity' => $normalizedQuantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'reference' => $reference ?: null,
                'reason' => $reason ?: null,
            ]);
        });
    }

    protected function normalizeQuantity(float $quantity): float
    {
        return round($quantity, 3);
    }
}
