<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory;

    public const TYPE_ENTRY = 'entry';
    public const TYPE_SALE = 'sale';
    public const TYPE_ADJUSTMENT = 'adjustment';

    public const DIRECTION_IN = 'in';
    public const DIRECTION_OUT = 'out';

    protected $fillable = [
        'product_id',
        'user_id',
        'movement_type',
        'direction',
        'quantity',
        'stock_before',
        'stock_after',
        'reference',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'stock_before' => 'decimal:3',
            'stock_after' => 'decimal:3',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeLabel(): string
    {
        return match ($this->movement_type) {
            self::TYPE_ENTRY => 'Entrada',
            self::TYPE_SALE => 'Salida por venta',
            self::TYPE_ADJUSTMENT => 'Ajuste manual',
            default => ucfirst($this->movement_type),
        };
    }

    public function directionLabel(): string
    {
        return $this->direction === self::DIRECTION_IN ? 'Suma stock' : 'Resta stock';
    }
}
