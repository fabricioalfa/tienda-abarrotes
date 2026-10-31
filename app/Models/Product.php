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
        'code',
        'name',
        'category',
        'description',
        'brand',
        'supplier_name',
        'barcode',
        'purchase_price',
        'sale_price',
        'unit',
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
        'is_bulk',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::created(function (Product $product): void {
            if (filled($product->code)) {
                return;
            }

            $code = static::generateUniqueCode($product->id);

            $product->forceFill([
                'code' => $code,
                'barcode' => filled($product->barcode) ? $product->barcode : $code,
            ])->saveQuietly();
        });
    }

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'price' => 'decimal:2',
            'package_price' => 'decimal:2',
            'stock' => 'decimal:3',
            'minimum_stock' => 'decimal:3',
            'allows_package_sale' => 'boolean',
            'track_expiration' => 'boolean',
            'is_bulk' => 'boolean',
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

    public function presentations(): HasMany
    {
        return $this->hasMany(ProductPresentation::class)->orderBy('sort_order')->orderBy('units_per_presentation');
    }

    public function activePresentations(): HasMany
    {
        return $this->presentations()->where('is_active', true);
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
            'quarter' => 'Cuartilla',
            default => null,
        };
    }

    /**
     * Multiplicadores de las unidades de venta para productos en libras.
     * Cuartilla = 6 lb, Arroba = 24 lb.
     */
    public static function poundSaleUnits(): array
    {
        return [
            'lb' => ['label' => 'Libra', 'factor' => 1],
            'cuartilla' => ['label' => 'Cuartilla (6 lb)', 'factor' => 6],
            'arroba' => ['label' => 'Arroba (24 lb)', 'factor' => 24],
        ];
    }

    public function supportsPoundSaleUnits(): bool
    {
        return $this->sale_type === self::SALE_TYPE_WEIGHT && $this->weight_unit === 'lb';
    }

    public function stockUnitLabel(): string
    {
        if ($this->sale_type === self::SALE_TYPE_UNIT) {
            return 'unidades';
        }

        return match ($this->weight_unit) {
            'g' => 'gramos',
            'lb' => 'libras',
            'quarter' => 'cuartas',
            default => 'kg',
        };
    }

    protected static function generateUniqueCode(?int $startAt = null): string
    {
        $next = max((int) $startAt, 1);

        do {
            $candidate = str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            $next++;
        } while (static::query()->where('code', $candidate)->exists());

        return $candidate;
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
        $unitsPerPackage = max((int) $this->units_per_package, 1);
        $packageName = $this->package_name ?: 'paquete';

        $presentation = $this->activePresentations()
            ->orderByDesc('units_per_presentation')
            ->first();

        if ($presentation) {
            $unitsPerPackage = max((int) $presentation->units_per_presentation, 1);
            $packageName = $presentation->name;
        } elseif (! $this->supportsPackageSale()) {
            return rtrim(rtrim(number_format((float) $this->stock, 3, '.', ''), '0'), '.').' '.$this->stockUnitLabel();
        }

        $stock = (int) floor((float) $this->stock);
        $packages = intdiv($stock, $unitsPerPackage);
        $units = $stock % $unitsPerPackage;
        $packageLabel = $packages === 1 ? $packageName : Str::plural($packageName, $packages);

        return "{$packages} {$packageLabel} y {$units} unidades";
    }

    public function isLowStock(): bool
    {
        $threshold = (float) ($this->minimum_stock ?: 5);

        return (float) $this->stock <= $threshold;
    }
}
