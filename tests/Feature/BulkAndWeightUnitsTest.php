<?php

use App\Models\CashRegister;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function openCashRegisterForBulk(int $userId): CashRegister
{
    DB::table('cash_registers')->insert([
        'opened_by' => $userId,
        'opened_at' => now(),
        'opening_amount' => 0,
        'status' => CashRegister::STATUS_OPEN,
        'cash_sales_total' => 0,
        'qr_sales_total' => 0,
        'credit_sales_total' => 0,
        'notes' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return CashRegister::query()->latest('id')->firstOrFail();
}

function makeWeightProduct(string $unit = 'kg', float $price = 45.00, bool $bulk = false): Product
{
    $category = Category::firstOrCreate(['name' => 'Carniceria']);

    return Product::create([
        'name' => 'Pulpa '.uniqid(),
        'category_id' => $category->id,
        'sale_type' => Product::SALE_TYPE_WEIGHT,
        'weight_unit' => $unit,
        'price' => $price,
        'stock' => 100.000,
        'is_bulk' => $bulk,
        'is_active' => true,
    ]);
}

test('admin can create bulk weight product', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $category = Category::create(['name' => 'Carniceria']);

    $response = $this
        ->actingAs($admin)
        ->post(route('products.store'), [
            'name' => 'Pulpa de res',
            'category_id' => $category->id,
            'type_key' => 'kg',
            'price' => 45.00,
            'initial_stock' => 30,
            'is_bulk' => 1,
            'is_active' => 1,
        ]);

    $response->assertSessionHasNoErrors();

    $product = Product::where('name', 'Pulpa de res')->firstOrFail();

    expect($product->is_bulk)->toBeTrue();
    expect($product->sale_type)->toBe(Product::SALE_TYPE_WEIGHT);
});

test('unit products cannot be bulk', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $category = Category::create(['name' => 'Abarrotes']);

    $response = $this
        ->actingAs($admin)
        ->post(route('products.store'), [
            'name' => 'Arroz',
            'category_id' => $category->id,
            'type_key' => 'unit',
            'price' => 8.00,
            'is_bulk' => 1,
            'is_active' => 1,
        ]);

    $response->assertSessionHasNoErrors();

    $product = Product::where('name', 'Arroz')->firstOrFail();

    expect($product->is_bulk)->toBeFalse();
});

test('caja can sell weight product in cuartillas converting to lb stock', function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite CHECK constraint no permite weight_unit=lb (solo aplica en MySQL).');
    }

    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);
    openCashRegisterForBulk($caja->id);
    $product = makeWeightProduct('lb', 10.00);

    $response = $this
        ->actingAs($caja)
        ->post(route('sales.store'), [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'pricing_mode' => 'standard',
                    'sale_unit' => 'cuartilla',
                ],
            ],
            'payment_method' => 'cash',
            'cash_received' => 200.00,
        ]);

    $response->assertSessionHasNoErrors();

    $sale = Sale::query()->latest('id')->first();

    // 2 cuartillas × 6 lb × Bs. 10/lb = Bs. 120
    expect((float) $sale->total)->toBe(120.0);
    // Stock descontado: 2 × 6 = 12 lb
    expect((float) $product->fresh()->stock)->toBe(88.0);

    $item = SaleItem::where('sale_id', $sale->id)->firstOrFail();
    expect((float) $item->unit_price)->toBe(60.0); // 6 lb × Bs. 10
    expect((float) $item->stock_quantity)->toBe(12.0);
    expect($item->unit_label)->toBe('Cuartilla (6 lb)');
});

test('caja can sell weight product in arrobas converting to lb stock', function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite CHECK constraint no permite weight_unit=lb (solo aplica en MySQL).');
    }

    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);
    openCashRegisterForBulk($caja->id);
    $product = makeWeightProduct('lb', 10.00);

    $response = $this
        ->actingAs($caja)
        ->post(route('sales.store'), [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'pricing_mode' => 'standard',
                    'sale_unit' => 'arroba',
                ],
            ],
            'payment_method' => 'cash',
            'cash_received' => 300.00,
        ]);

    $response->assertSessionHasNoErrors();

    $sale = Sale::query()->latest('id')->first();

    // 1 arroba × 24 lb × Bs. 10/lb = Bs. 240
    expect((float) $sale->total)->toBe(240.0);
    // Stock descontado: 1 × 24 = 24 lb
    expect((float) $product->fresh()->stock)->toBe(76.0);

    $item = SaleItem::where('sale_id', $sale->id)->firstOrFail();
    expect((float) $item->unit_price)->toBe(240.0); // 24 lb × Bs. 10
    expect((float) $item->stock_quantity)->toBe(24.0);
    expect($item->unit_label)->toBe('Arroba (24 lb)');
});

test('sale_unit only works for lb products', function () {
    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);
    openCashRegisterForBulk($caja->id);
    $product = makeWeightProduct('kg', 45.00);

    $response = $this
        ->actingAs($caja)
        ->post(route('sales.store'), [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'pricing_mode' => 'standard',
                    'sale_unit' => 'cuartilla',
                ],
            ],
            'payment_method' => 'cash',
            'cash_received' => 200.00,
        ]);

    $response->assertSessionHasErrors('items');
});

test('sale_unit invalid value is rejected', function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite CHECK constraint no permite weight_unit=lb (solo aplica en MySQL).');
    }

    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);
    openCashRegisterForBulk($caja->id);
    $product = makeWeightProduct('lb', 10.00);

    $response = $this
        ->actingAs($caja)
        ->post(route('sales.store'), [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'pricing_mode' => 'standard',
                    'sale_unit' => 'tonelada',
                ],
            ],
            'payment_method' => 'cash',
            'cash_received' => 100.00,
        ]);

    $response->assertSessionHasErrors('items.0.sale_unit');
});

test('bulk product sale works with decimal kg quantity', function () {
    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);
    openCashRegisterForBulk($caja->id);
    $product = makeWeightProduct('kg', 45.00, bulk: true);

    $response = $this
        ->actingAs($caja)
        ->post(route('sales.store'), [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1.250,
                    'pricing_mode' => 'standard',
                ],
            ],
            'payment_method' => 'cash',
            'cash_received' => 60.00,
        ]);

    $response->assertSessionHasNoErrors();

    $sale = Sale::query()->latest('id')->first();

    expect((float) $sale->total)->toBe(56.25); // 1.25 × 45
    expect((float) $product->fresh()->stock)->toBe(98.75);
});

test('cuartilla sale requires enough lb stock', function () {
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite CHECK constraint no permite weight_unit=lb (solo aplica en MySQL).');
    }

    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);
    openCashRegisterForBulk($caja->id);

    $category = Category::firstOrCreate(['name' => 'Carniceria']);
    $product = Product::create([
        'name' => 'Tocino escaso',
        'category_id' => $category->id,
        'sale_type' => Product::SALE_TYPE_WEIGHT,
        'weight_unit' => 'lb',
        'price' => 10.00,
        'stock' => 5.000, // menos de una cuartilla (6 lb)
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($caja)
        ->post(route('sales.store'), [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'pricing_mode' => 'standard',
                    'sale_unit' => 'cuartilla',
                ],
            ],
            'payment_method' => 'cash',
            'cash_received' => 60.00,
        ]);

    $response->assertSessionHasErrors('items');
});
