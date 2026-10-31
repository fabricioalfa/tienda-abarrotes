<?php

use App\Models\CashRegister;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPresentation;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function openCashRegisterForPresentations(int $userId): CashRegister
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

function makeEggsProduct(): Product
{
    $category = Category::firstOrCreate(['name' => 'Abarrotes']);

    return Product::create([
        'name' => 'Huevos '.uniqid(),
        'category_id' => $category->id,
        'sale_type' => Product::SALE_TYPE_UNIT,
        'weight_unit' => null,
        'price' => 1.00,
        'stock' => 200,
        'is_active' => true,
    ]);
}

test('admin can create product with multiple presentations', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $category = Category::create(['name' => 'Abarrotes']);

    $response = $this
        ->actingAs($admin)
        ->post(route('products.store'), [
            'name' => 'Huevos',
            'category_id' => $category->id,
            'type_key' => 'unit',
            'price' => 1.00,
            'initial_stock' => 120,
            'is_active' => 1,
            'presentations' => [
                ['name' => 'Medio maple', 'units' => 15, 'price' => 13.50],
                ['name' => 'Maple', 'units' => 30, 'price' => 25.00],
            ],
        ]);

    $response->assertSessionHasNoErrors();

    $product = Product::where('name', 'Huevos')->firstOrFail();
    $presentations = $product->presentations()->orderBy('sort_order')->get();

    expect($presentations)->toHaveCount(2);
    expect($presentations[0]->name)->toBe('Medio maple');
    expect($presentations[0]->units_per_presentation)->toBe(15);
    expect((float) $presentations[0]->price)->toBe(13.50);
    expect($presentations[1]->name)->toBe('Maple');
    expect($presentations[1]->units_per_presentation)->toBe(30);
    expect((float) $presentations[1]->price)->toBe(25.00);
});

test('weight products cannot have presentations', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $category = Category::create(['name' => 'Carniceria']);

    $response = $this
        ->actingAs($admin)
        ->post(route('products.store'), [
            'name' => 'Pulpa',
            'category_id' => $category->id,
            'type_key' => 'kg',
            'price' => 45.00,
            'is_active' => 1,
            'presentations' => [
                ['name' => 'Caja', 'units' => 10, 'price' => 400.00],
            ],
        ]);

    $response->assertSessionHasNoErrors();

    $product = Product::where('name', 'Pulpa')->firstOrFail();

    expect($product->presentations()->count())->toBe(0);
});

test('updating product replaces presentations', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $product = makeEggsProduct();

    ProductPresentation::create([
        'product_id' => $product->id,
        'name' => 'Docena',
        'units_per_presentation' => 12,
        'price' => 10.00,
    ]);

    $response = $this
        ->actingAs($admin)
        ->put(route('products.update', $product), [
            'name' => 'Huevos frescos',
            'category_id' => $product->category_id,
            'type_key' => 'unit',
            'price' => 1.20,
            'purchase_price' => 0.80,
            'is_active' => 1,
            'presentations' => [
                ['name' => 'Maple', 'units' => 30, 'price' => 30.00],
            ],
        ]);

    $response->assertSessionHasNoErrors();

    $product->refresh();
    $presentations = $product->presentations()->get();

    expect($presentations)->toHaveCount(1);
    expect($presentations->first()->name)->toBe('Maple');
});

test('caja can sell using a presentation and stock is discounted by units', function () {
    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);
    openCashRegisterForPresentations($caja->id);
    $product = makeEggsProduct();

    $presentation = ProductPresentation::create([
        'product_id' => $product->id,
        'name' => 'Maple',
        'units_per_presentation' => 30,
        'price' => 25.00,
    ]);

    $response = $this
        ->actingAs($caja)
        ->post(route('sales.store'), [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'pricing_mode' => 'presentation',
                    'presentation_id' => $presentation->id,
                ],
            ],
            'payment_method' => 'cash',
            'cash_received' => 60.00,
        ]);

    $response->assertSessionHasNoErrors();

    $sale = Sale::query()->latest('id')->first();

    expect((float) $sale->total)->toBe(50.0);
    expect((float) $product->fresh()->stock)->toBe(140.0);

    $item = SaleItem::where('sale_id', $sale->id)->firstOrFail();
    expect((float) $item->unit_price)->toBe(25.00);
    expect((float) $item->stock_quantity)->toBe(60.0);
    expect($item->presentation_id)->toBe($presentation->id);
    expect($item->presentation_name)->toBe('Maple');
    expect($item->unit_label)->toBe('Maple');
});

test('selling presentation from another product is rejected', function () {
    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);
    openCashRegisterForPresentations($caja->id);

    $eggs = makeEggsProduct();
    $other = makeEggsProduct();
    $other->update(['name' => 'Gaseosa']);

    $presentation = ProductPresentation::create([
        'product_id' => $other->id,
        'name' => 'Caja',
        'units_per_presentation' => 12,
        'price' => 30.00,
    ]);

    $response = $this
        ->actingAs($caja)
        ->post(route('sales.store'), [
            'items' => [
                [
                    'product_id' => $eggs->id,
                    'quantity' => 1,
                    'pricing_mode' => 'presentation',
                    'presentation_id' => $presentation->id,
                ],
            ],
            'payment_method' => 'cash',
            'cash_received' => 30.00,
        ]);

    $response->assertSessionHasErrors('items');
});

test('inactive presentation cannot be sold', function () {
    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);
    openCashRegisterForPresentations($caja->id);
    $product = makeEggsProduct();

    $presentation = ProductPresentation::create([
        'product_id' => $product->id,
        'name' => 'Maple',
        'units_per_presentation' => 30,
        'price' => 25.00,
        'is_active' => false,
    ]);

    $response = $this
        ->actingAs($caja)
        ->post(route('sales.store'), [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'pricing_mode' => 'presentation',
                    'presentation_id' => $presentation->id,
                ],
            ],
            'payment_method' => 'cash',
            'cash_received' => 25.00,
        ]);

    $response->assertSessionHasErrors('items');
});

test('presentation sale requires whole quantities', function () {
    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);
    openCashRegisterForPresentations($caja->id);
    $product = makeEggsProduct();

    $presentation = ProductPresentation::create([
        'product_id' => $product->id,
        'name' => 'Maple',
        'units_per_presentation' => 30,
        'price' => 25.00,
    ]);

    $response = $this
        ->actingAs($caja)
        ->post(route('sales.store'), [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1.5,
                    'pricing_mode' => 'presentation',
                    'presentation_id' => $presentation->id,
                ],
            ],
            'payment_method' => 'cash',
            'cash_received' => 40.00,
        ]);

    $response->assertSessionHasErrors('items');
});

test('duplicate presentation names are ignored on save', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $product = makeEggsProduct();

    $this
        ->actingAs($admin)
        ->put(route('products.update', $product), [
            'name' => 'Huevos',
            'category_id' => $product->category_id,
            'type_key' => 'unit',
            'price' => 1.00,
            'purchase_price' => 0.60,
            'is_active' => 1,
            'presentations' => [
                ['name' => 'Maple', 'units' => 30, 'price' => 25.00],
                ['name' => 'Maple', 'units' => 31, 'price' => 26.00],
            ],
        ])
        ->assertSessionHasNoErrors();

    $presentations = $product->presentations()->get();

    expect($presentations)->toHaveCount(1);
    expect($presentations->first()->units_per_presentation)->toBe(30);
});
