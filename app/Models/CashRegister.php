<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashRegister extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'opened_by',
        'closed_by',
        'opened_at',
        'closed_at',
        'opening_amount',
        'cash_sales_total',
        'qr_sales_total',
        'credit_sales_total',
        'counted_cash',
        'difference_amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'opening_amount' => 'decimal:2',
            'cash_sales_total' => 'decimal:2',
            'qr_sales_total' => 'decimal:2',
            'credit_sales_total' => 'decimal:2',
            'counted_cash' => 'decimal:2',
            'difference_amount' => 'decimal:2',
        ];
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public static function current(): ?self
    {
        return self::query()
            ->where('status', self::STATUS_OPEN)
            ->latest('opened_at')
            ->first();
    }

    public function expectedCash(): float
    {
        return round((float) $this->opening_amount + (float) $this->cash_sales_total, 2);
    }
}