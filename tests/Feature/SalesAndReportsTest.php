<?php

use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;

test('caja can register unit sale and stock is discounted', function () {
    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);
    $category = Category::create(['name' => 'Abarrotes']);

    $product = Product::create([
        'name' => 'Fideos',
        'barcode' => 'AB-100',
        'category_id' => $category->id,
        'sale_type' => Product::SALE_TYPE_UNIT,
        'weight_unit' => null,
        'price' => 3.50,
        'stock' => 20,
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($caja)
        ->post(route('sales.store'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4],
            ],
            'notes' => 'Venta mostrador',
        ]);

    $response->assertSessionHasNoErrors();

    $sale = Sale::query()->latest('id')->first();

    expect($sale)->not->toBeNull();
    expect((float) $sale->total)->toBe(14.0);
    expect((float) $product->fresh()->stock)->toBe(16.0);
    expect(SaleItem::where('sale_id', $sale->id)->count())->toBe(1);

    $movement = InventoryMovement::query()->latest('id')->first();
    expect($movement->movement_type)->toBe(InventoryMovement::TYPE_SALE);
    expect($movement->reference)->toBe($sale->sale_number);
});

test('caja can register weight sale and stock is discounted with decimals', function () {
    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);
    $category = Category::create(['name' => 'Carniceria']);

    $product = Product::create([
        'name' => 'Carne molida',
        'barcode' => null,
        'category_id' => $category->id,
        'sale_type' => Product::SALE_TYPE_WEIGHT,
        'weight_unit' => 'kg',
        'price' => 20.00,
        'stock' => 15.000,
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($caja)
        ->post(route('sales.store'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1.250],
            ],
        ]);

    $response->assertSessionHasNoErrors();

    $sale = Sale::query()->latest('id')->first();

    expect((float) $sale->total)->toBe(25.0);
    expect((float) $product->fresh()->stock)->toBe(13.75);
});

test('sales report page shows historical sales', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $category = Category::create(['name' => 'Abarrotes']);

    $product = Product::create([
        'name' => 'Aceite',
        'barcode' => 'REP-1',
        'category_id' => $category->id,
        'sale_type' => Product::SALE_TYPE_UNIT,
        'weight_unit' => null,
        'price' => 10.00,
        'stock' => 50,
        'is_active' => true,
    ]);

    $sale = Sale::create([
        'sale_number' => 'V-20260403-00099',
        'user_id' => $admin->id,
        'subtotal' => 20.00,
        'total' => 20.00,
        'sold_at' => now(),
        'notes' => null,
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'sale_type' => $product->sale_type,
        'weight_unit' => $product->weight_unit,
        'unit_price' => 10.00,
        'quantity' => 2,
        'line_total' => 20.00,
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('reports.sales'));

    $response
        ->assertOk()
        ->assertSee('V-20260403-00099')
        ->assertSee('20.00');
});
