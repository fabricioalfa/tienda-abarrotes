<?php
// filepath: /home/fabri/Documentos/tienda/app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    public const SALE_TYPE_WEIGHT = 'weight';
    public const SALE_TYPE_UNIT = 'unit';

    protected $fillable = [
        'name',
        'barcode',
        'category_id',
        'sale_type',
        'weight_unit',
        'price',
        'stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'decimal:3',
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

    public function saleTypeLabel(): string
    {
        return $this->sale_type === self::SALE_TYPE_WEIGHT ? 'Por peso' : 'Por unidad';
    }

    public function weightUnitLabel(): ?string
    {
        return match ($this->weight_unit) {
            'kg' => 'Kg',
            'g' => 'Gramos',
            default => null,
        };
    }

    public function stockUnitLabel(): string
    {
        if ($this->sale_type === self::SALE_TYPE_UNIT) {
            return 'unidades';
        }

        return $this->weight_unit === 'g' ? 'gramos' : 'kg';
    }
}