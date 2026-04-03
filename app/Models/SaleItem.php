<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'sale_type',
        'weight_unit',
        'unit_price',
        'quantity',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'quantity' => 'decimal:3',
            'line_total' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function quantityLabel(): string
    {
        if ($this->sale_type === Product::SALE_TYPE_UNIT) {
            return rtrim(rtrim(number_format((float) $this->quantity, 0, '.', ''), '0'), '.') . ' unid';
        }

        return rtrim(rtrim(number_format((float) $this->quantity, 3, '.', ''), '0'), '.') . ' ' . ($this->weight_unit ?? 'kg');
    }
}
