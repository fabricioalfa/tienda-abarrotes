<?php

namespace App\Services;

use App\Models\CashRegister;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SalesService
{
    public function __construct(private readonly InventoryService $inventoryService) {}

    public function createSale(User $user, array $rawItems, array $saleData = []): Sale
    {
        $items = $this->mergeItemsByProduct($rawItems);

        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => 'Debe registrar al menos un item para completar la venta.',
            ]);
        }

        $currentRegister = CashRegister::query()
            ->where('status', CashRegister::STATUS_OPEN)
            ->latest('opened_at')
            ->first();

        if (! $currentRegister) {
            throw ValidationException::withMessages([
                'cash_register' => 'Debes abrir caja antes de registrar ventas.',
            ]);
        }

        return DB::transaction(function () use ($user, $items, $saleData, $currentRegister) {
            $discountAmount = round((float) ($saleData['discount_amount'] ?? 0), 2);
            $notes = $saleData['notes'] ?? null;
            $customerName = $saleData['customer_name'] ?? null;
            $customerPhone = $saleData['customer_phone'] ?? null;
            $paymentMethod = $saleData['payment_method'] ?? 'cash';
            $lineItems = [];
            $subtotal = 0;

            foreach ($items as $item) {
                $product = Product::query()->findOrFail($item['product_id']);
                $quantity = round((float) $item['quantity'], 3);
                $pricingMode = $item['pricing_mode'] ?? 'standard';

                if ($quantity <= 0) {
                    throw ValidationException::withMessages([
                        'items' => 'La cantidad del producto debe ser mayor a cero.',
                    ]);
                }

                if (! $product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => "El producto {$product->name} no esta activo para venta.",
                    ]);
                }

                // Verificación de stock suficiente ANTES de procesar (previene overselling)
                $requiredStock = $pricingMode === 'package'
                    ? round((float) $item['quantity'] * (int) $product->units_per_package, 3)
                    : round((float) $item['quantity'], 3);

                if ($product->stock < $requiredStock) {
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente para \"{$product->name}\". Disponible: {$product->stock}, solicitado: {$requiredStock}.",
                    ]);
                }

                $unitPrice = (float) $product->price;
                $stockQuantity = $quantity;
                $unitLabel = $product->sale_type === Product::SALE_TYPE_UNIT ? 'unid' : ($product->weight_unit ?? 'kg');

                if ($pricingMode === 'package') {
                    if (! $product->supportsPackageSale()) {
                        throw ValidationException::withMessages([
                            'items' => "El producto {$product->name} no tiene venta por paquete configurada.",
                        ]);
                    }

                    if (floor($quantity) !== $quantity) {
                        throw ValidationException::withMessages([
                            'items' => "Los paquetes de {$product->name} solo aceptan cantidades enteras.",
                        ]);
                    }

                    $unitPrice = (float) $product->package_price;
                    $stockQuantity = round($quantity * (int) $product->units_per_package, 3);
                    $unitLabel = $product->package_name ?: 'paquete';
                } elseif ($product->sale_type === Product::SALE_TYPE_UNIT && floor($quantity) !== $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "El producto {$product->name} solo acepta cantidades enteras.",
                    ]);
                }

                $lineTotal = round($unitPrice * $quantity, 2);
                $subtotal += $lineTotal;

                $lineItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'stock_quantity' => $stockQuantity,
                    'pricing_mode' => $pricingMode,
                    'unit_price' => $unitPrice,
                    'unit_label' => $unitLabel,
                    'line_total' => $lineTotal,
                ];
            }

            $subtotal = round($subtotal, 2);

            if ($discountAmount < 0 || $discountAmount > $subtotal) {
                throw ValidationException::withMessages([
                    'discount_amount' => 'El descuento no puede ser negativo ni mayor al subtotal.',
                ]);
            }

            $total = round($subtotal - $discountAmount, 2);
            $payment = $this->normalizePaymentData($paymentMethod, $total, $saleData, $customerName);

            $sale = Sale::create([
                'sale_number' => 'TMP-'.Str::uuid(),
                'user_id' => $user->id,
                'cash_register_id' => $currentRegister->id,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'payment_method' => $paymentMethod,
                'subtotal' => $subtotal,
                'total' => $total,
                'cash_amount' => $payment['cash_amount'],
                'qr_amount' => $payment['qr_amount'],
                'cash_received' => $payment['cash_received'],
                'change_amount' => $payment['change_amount'],
                'discount_amount' => $discountAmount,
                'sold_at' => now(),
                'notes' => $notes ?: null,
            ]);

            $saleNumber = 'V-'.now()->format('Ymd').'-'.str_pad((string) $sale->id, 5, '0', STR_PAD_LEFT);

            foreach ($lineItems as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'sale_type' => $item['product']->sale_type,
                    'weight_unit' => $item['product']->weight_unit,
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'stock_quantity' => $item['stock_quantity'],
                    'pricing_mode' => $item['pricing_mode'],
                    'unit_label' => $item['unit_label'],
                    'line_total' => $item['line_total'],
                ]);

                $this->inventoryService->registerMovement(
                    product: $item['product'],
                    movementType: InventoryMovement::TYPE_SALE,
                    direction: InventoryMovement::DIRECTION_OUT,
                    quantity: $item['stock_quantity'],
                    reason: 'Salida por venta en caja',
                    reference: $saleNumber,
                    userId: $user->id,
                );
            }

            $sale->update([
                'sale_number' => $saleNumber,
            ]);

            // Incrementos atómicos con bindings parametrizados — sin interpolación de strings
            DB::statement(
                'UPDATE cash_registers SET cash_sales_total = cash_sales_total + ?, qr_sales_total = qr_sales_total + ?, credit_sales_total = credit_sales_total + ? WHERE id = ?',
                [
                    round($payment['cash_amount'], 2),
                    round($payment['qr_amount'], 2),
                    round($payment['credit_amount'], 2),
                    $currentRegister->id,
                ]
            );

            return $sale->load(['items.product', 'user']);
        });
    }

    protected function mergeItemsByProduct(array $rawItems): array
    {
        $merged = [];

        foreach ($rawItems as $rawItem) {
            $productId = (int) ($rawItem['product_id'] ?? 0);
            $quantity = (float) ($rawItem['quantity'] ?? 0);
            $pricingMode = (string) ($rawItem['pricing_mode'] ?? 'standard');

            if ($productId <= 0 || $quantity <= 0) {
                continue;
            }

            $key = $productId.'|'.$pricingMode;

            if (! isset($merged[$key])) {
                $merged[$key] = [
                    'product_id' => $productId,
                    'quantity' => 0,
                    'pricing_mode' => $pricingMode,
                ];
            }

            $merged[$key]['quantity'] += $quantity;
        }

        return array_values($merged);
    }

    protected function normalizePaymentData(string $paymentMethod, float $total, array $saleData, ?string $customerName): array
    {
        $cashAmount = round((float) ($saleData['cash_amount'] ?? 0), 2);
        $qrAmount = round((float) ($saleData['qr_amount'] ?? 0), 2);
        $cashReceived = round((float) ($saleData['cash_received'] ?? 0), 2);

        if ($paymentMethod === 'credit' && ! $customerName) {
            throw ValidationException::withMessages([
                'customer_name' => 'El nombre del cliente es obligatorio para ventas a credito.',
            ]);
        }

        if ($paymentMethod === 'cash') {
            $cashAmount = $total;
            $qrAmount = 0;
        } elseif ($paymentMethod === 'qr') {
            $cashAmount = 0;
            $qrAmount = $total;
            $cashReceived = 0;
        } elseif ($paymentMethod === 'credit') {
            $cashAmount = 0;
            $qrAmount = 0;
            $cashReceived = 0;
        } elseif (round($cashAmount + $qrAmount, 2) !== $total) {
            throw ValidationException::withMessages([
                'payment_method' => 'En pago mixto, el efectivo y QR deben sumar el total de la venta.',
            ]);
        }

        if (in_array($paymentMethod, ['cash', 'mixed'], true) && $cashReceived < $cashAmount) {
            throw ValidationException::withMessages([
                'cash_received' => 'El efectivo recibido no puede ser menor al monto cobrado en efectivo.',
            ]);
        }

        return [
            'cash_amount' => $cashAmount,
            'qr_amount' => $qrAmount,
            'cash_received' => $cashReceived,
            'change_amount' => in_array($paymentMethod, ['cash', 'mixed'], true) ? round($cashReceived - $cashAmount, 2) : 0,
            'credit_amount' => $paymentMethod === 'credit' ? $total : 0,
        ];
    }
}
