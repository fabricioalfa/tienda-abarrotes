<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_number',
        'user_id',
        'cash_register_id',
        'customer_name',
        'customer_phone',
        'payment_method',
        'subtotal',
        'total',
        'cash_amount',
        'qr_amount',
        'cash_received',
        'change_amount',
        'discount_amount',
        'sold_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'cash_amount' => 'decimal:2',
            'qr_amount' => 'decimal:2',
            'cash_received' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'sold_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Efectivo',
            'qr' => 'QR',
            'mixed' => 'Mixto',
            'credit' => 'Crédito',
            default => ucfirst((string) $this->payment_method),
        };
    }
}
