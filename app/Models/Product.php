<?php
// filepath: /home/fabri/Documentos/tienda/app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    public const SALE_TYPE_WEIGHT = 'weight';
    public const SALE_TYPE_UNIT = 'unit';

    protected $fillable = [
        'name',
        'brand',
        'supplier_name',
        'barcode',
        'category_id',
        'sale_type',
        'weight_unit',
        'allows_package_sale',
        'package_name',
        'units_per_package',
        'price',
        'package_price',
        'stock',
        'minimum_stock',
        'track_expiration',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'package_price' => 'decimal:2',
            'stock' => 'decimal:3',
            'minimum_stock' => 'decimal:3',
            'allows_package_sale' => 'boolean',
            'track_expiration' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function saleTypeLabel(): string
    {
        return $this->sale_type === self::SALE_TYPE_WEIGHT ? 'Por peso' : 'Por unidad';
    }

    public function weightUnitLabel(): ?string
    {
        return match ($this->weight_unit) {
            'kg' => 'Kg',
            'g' => 'Gramos',
            'lb' => 'Libra',
            default => null,
        };
    }

    public function stockUnitLabel(): string
    {
        if ($this->sale_type === self::SALE_TYPE_UNIT) {
            return 'unidades';
        }

        return match ($this->weight_unit) {
            'g' => 'gramos',
            'lb' => 'libras',
            default => 'kg',
        };
    }

    public function supportsPackageSale(): bool
    {
        return $this->sale_type === self::SALE_TYPE_UNIT
            && $this->allows_package_sale
            && (int) $this->units_per_package > 0
            && (float) $this->package_price > 0;
    }

    public function stockBreakdownLabel(): string
    {
        if (! $this->supportsPackageSale()) {
            return rtrim(rtrim(number_format((float) $this->stock, 3, '.', ''), '0'), '.') . ' ' . $this->stockUnitLabel();
        }

        $stock = (int) floor((float) $this->stock);
        $packages = intdiv($stock, max((int) $this->units_per_package, 1));
        $units = $stock % max((int) $this->units_per_package, 1);
        $packageName = $this->package_name ?: 'paquete';
        $packageLabel = $packages === 1 ? $packageName : Str::plural($packageName, $packages);

        return "{$packages} {$packageLabel} y {$units} unidades";
    }

    public function isLowStock(): bool
    {
        $threshold = (float) ($this->minimum_stock ?: 5);

        return (float) $this->stock <= $threshold;
    }
}