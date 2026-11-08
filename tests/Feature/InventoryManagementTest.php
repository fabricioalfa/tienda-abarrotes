<?php

use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;

test('caja cannot access products module', function () {
    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);

    $response = $this
        ->actingAs($caja)
        ->get(route('products.index'));

    $response->assertForbidden();
});

test('admin can register inventory entry and stock increases', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $category = Category::create(['name' => 'Carniceria']);

    $product = Product::create([
        'code' => '100001',
        'name' => 'Lomo fino',
        'barcode' => null,
        'category_id' => $category->id,
        'sale_type' => Product::SALE_TYPE_WEIGHT,
        'weight_unit' => 'kg',
        'price' => 35.00,
        'stock' => 2.000,
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($admin)
        ->post(route('inventory.entries.store'), [
            'product_id' => $product->id,
            'quantity' => 1.500,
            'reason' => 'Compra proveedor',
            'reference' => 'FAC-1001',
        ]);

    $response->assertSessionHasNoErrors();

    expect((float) $product->fresh()->stock)->toBe(3.5);

    $movement = InventoryMovement::query()->latest('id')->first();

    expect($movement)->not->toBeNull();
    expect($movement->movement_type)->toBe(InventoryMovement::TYPE_ENTRY);
    expect($movement->direction)->toBe(InventoryMovement::DIRECTION_IN);
    expect((float) $movement->stock_after)->toBe(3.5);
});

test('sale movement discounts stock automatically', function () {
    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);
    $category = Category::create(['name' => 'Abarrotes']);

    $product = Product::create([
        'code' => '100002',
        'name' => 'Arroz 1kg',
        'barcode' => '123456',
        'category_id' => $category->id,
        'sale_type' => Product::SALE_TYPE_UNIT,
        'weight_unit' => null,
        'price' => 4.20,
        'stock' => 10,
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($caja)
        ->post(route('inventory.sales.store'), [
            'product_id' => $product->id,
            'quantity' => 3,
            'reference' => 'B001-258',
        ]);

    $response->assertSessionHasNoErrors();

    expect((float) $product->fresh()->stock)->toBe(7.0);

    $movement = InventoryMovement::query()->latest('id')->first();

    expect($movement->movement_type)->toBe(InventoryMovement::TYPE_SALE);
    expect($movement->direction)->toBe(InventoryMovement::DIRECTION_OUT);
    expect((float) $movement->quantity)->toBe(3.0);
});

test('it blocks sale when stock is insufficient', function () {
    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);
    $category = Category::create(['name' => 'Abarrotes']);

    $product = Product::create([
        'code' => '100003',
        'name' => 'Azucar',
        'barcode' => null,
        'category_id' => $category->id,
        'sale_type' => Product::SALE_TYPE_UNIT,
        'weight_unit' => null,
        'price' => 5.00,
        'stock' => 1,
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($caja)
        ->from(route('inventory.index'))
        ->post(route('inventory.sales.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

    $response
        ->assertSessionHasErrors('quantity')
        ->assertRedirect(route('inventory.index'));

    expect((float) $product->fresh()->stock)->toBe(1.0);
    expect(InventoryMovement::count())->toBe(0);
});

test('creating product with initial stock logs inventory movement', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $category = Category::create(['name' => 'Carniceria']);

    $response = $this
        ->actingAs($admin)
        ->post(route('products.store'), [
            'name' => 'Pechuga de pollo',
            'barcode' => '987654',
            'category_id' => $category->id,
            'type_key' => 'kg',
            'price' => 17.50,
            'initial_stock' => 8.250,
            'is_active' => 1,
        ]);

    $response->assertSessionHasNoErrors();

    $product = Product::query()->where('name', 'Pechuga de pollo')->first();

    expect($product)->not->toBeNull();
    expect($product->code)->not->toBeEmpty();
    expect((float) $product->stock)->toBe(8.25);

    $movement = InventoryMovement::query()->where('product_id', $product->id)->first();

    expect($movement)->not->toBeNull();
    expect($movement->movement_type)->toBe(InventoryMovement::TYPE_ENTRY);
    expect((float) $movement->quantity)->toBe(8.25);
});

test('admin can register product with code barcode category and quarter price', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $category = Category::create(['name' => 'Carniceria']);

$response = $this
    ->actingAs($admin)
    ->post(route('products.store'), [
        'name' => 'Carne especial',
        'barcode' => '77990011',
        'category_id' => $category->id,
        'type_key' => 'quarter',
        'weight_unit' => 'quarter',
        'price' => 18.50,
        'initial_stock' => 4,
        'is_active' => 1,
    ]);

    $response->assertSessionHasNoErrors();

    $product = Product::query()->where('name', 'Carne especial')->first();

    expect($product)->not->toBeNull();
    expect($product->code)->not->toBeEmpty();
    expect($product->barcode)->toBe('77990011');
    expect($product->category_id)->toBe($category->id);
    expect($product->weight_unit)->toBe('quarter');
    expect((float) $product->price)->toBe(18.5);
});

test('it blocks product creation when purchase cost is higher than sale price', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $category = Category::create(['name' => 'Abarrotes']);

    $response = $this
        ->actingAs($admin)
        ->from(route('products.create'))
        ->post(route('products.store'), [
            'name' => 'Producto costoso',
            'category_id' => $category->id,
            'type_key' => 'unit',
            'price' => 10.00,
            'initial_cost' => 15.00,
            'is_active' => 1,
        ]);

    $response
        ->assertSessionHasErrors('initial_cost')
        ->assertRedirect(route('products.create'));

    expect(Product::query()->where('name', 'Producto costoso')->exists())->toBeFalse();
});

test('it blocks inventory entry when unit cost is higher than sale price', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $category = Category::create(['name' => 'Abarrotes']);

    $product = Product::create([
        'name' => 'Arroz',
        'category_id' => $category->id,
        'sale_type' => Product::SALE_TYPE_UNIT,
        'price' => 5.00,
        'stock' => 0,
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('inventory.index'))
        ->post(route('inventory.entries.store'), [
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_cost' => 8.00,
        ]);

    $response
        ->assertSessionHasErrors('unit_cost')
        ->assertRedirect(route('inventory.index'));

    expect((float) $product->fresh()->stock)->toBe(0.0);
    expect(InventoryMovement::count())->toBe(0);
});

test('admin can open the inventory dashboard with movements list', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $category = Category::create(['name' => 'Carniceria']);

    $product = Product::create([
        'name' => 'Chuleta',
        'category_id' => $category->id,
        'sale_type' => Product::SALE_TYPE_WEIGHT,
        'weight_unit' => 'kg',
        'price' => 40.00,
        'stock' => 3.000,
        'is_active' => true,
    ]);

    InventoryMovement::create([
        'product_id' => $product->id,
        'user_id' => $admin->id,
        'movement_type' => InventoryMovement::TYPE_ENTRY,
        'direction' => InventoryMovement::DIRECTION_IN,
        'quantity' => 3.000,
        'stock_before' => 0,
        'stock_after' => 3.000,
        'reason' => 'Ingreso inicial',
    ]);

    $this
        ->actingAs($admin)
        ->get(route('inventory.index'))
        ->assertOk()
        ->assertSee('Chuleta');

    $this
        ->actingAs($admin)
        ->get(route('inventory.movements'))
        ->assertOk()
        ->assertSee('Chuleta')
        ->assertSee('Historial de movimientos');
});
