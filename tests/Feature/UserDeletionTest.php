<?php

use App\Models\CashRegister;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * `sales.user_id` y `cash_registers.opened_by` están declaradas como
 * ON DELETE RESTRICT. Sin guarda previa, borrar un usuario con historial
 * lanza una QueryException y el usuario ve una pantalla en blanco (HTTP 500).
 */
function seedProduct(): Product
{
    $category = Category::firstOrCreate(['name' => 'AbarrotesQA']);

    return Product::create([
        'code' => '77'.random_int(100000, 999999),
        'name' => 'Producto QA',
        'barcode' => null,
        'category_id' => $category->id,
        'sale_type' => Product::SALE_TYPE_UNIT,
        'weight_unit' => null,
        'price' => 5.00,
        'stock' => 50,
        'is_active' => true,
    ]);
}

function seedSaleFor(User $seller): Sale
{
    $product = seedProduct();

    $sale = Sale::create([
        'sale_number' => 'V-QA-'.random_int(100000, 999999),
        'user_id' => $seller->id,
        'subtotal' => 10.00,
        'total' => 10.00,
        'sold_at' => now(),
        'notes' => null,
    ]);

    SaleItem::create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'sale_type' => $product->sale_type,
        'weight_unit' => null,
        'unit_price' => 5.00,
        'quantity' => 2,
        'line_total' => 10.00,
    ]);

    return $sale;
}

function openRegisterFor(User $user): void
{
    DB::table('cash_registers')->insert([
        'opened_by' => $user->id,
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
}

test('admin no puede eliminar a un cajero que tiene ventas registradas', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);
    seedSaleFor($caja);

    $response = $this->actingAs($admin)->delete(route('users.destroy', $caja));

    $response->assertRedirect(route('users.index'));
    $response->assertSessionHas('error');

    // El usuario debe seguir existiendo y la venta intacta.
    expect(User::find($caja->id))->not->toBeNull();
    expect(Sale::where('user_id', $caja->id)->exists())->toBeTrue();
});

test('admin no puede eliminar a un cajero que abrio una caja', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);
    openRegisterFor($caja);

    $response = $this->actingAs($admin)->delete(route('users.destroy', $caja));

    $response->assertRedirect(route('users.index'));
    $response->assertSessionHas('error');
    expect(User::find($caja->id))->not->toBeNull();
});

test('un usuario sin historial si se puede eliminar', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);

    $response = $this->actingAs($admin)->delete(route('users.destroy', $caja));

    $response->assertRedirect(route('users.index'));
    $response->assertSessionHas('status');
    expect(User::find($caja->id))->toBeNull();
});

test('un admin si puede eliminar a otro admin siempre que quede uno vivo', function () {
    // Esta ruta exige rol admin y no permite borrarse a si mismo, asi que
    // nunca puede quedar el sistema sin administradores.
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $otroAdmin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $response = $this->actingAs($admin)->delete(route('users.destroy', $otroAdmin));

    $response->assertRedirect(route('users.index'));
    $response->assertSessionHas('status');
    expect(User::find($otroAdmin->id))->toBeNull();
});

test('un admin no puede borrarse a si mismo desde la gestion de usuarios', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $response = $this->actingAs($admin)->delete(route('users.destroy', $admin));

    $response->assertRedirect(route('users.index'));
    $response->assertSessionHas('error');
    expect(User::find($admin->id))->not->toBeNull();
});

test('el unico administrador no puede borrarse a si mismo desde su perfil', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $response = $this->actingAs($admin)->delete(route('profile.destroy'), [
        'password' => 'password',
    ]);

    $response->assertRedirect(route('profile.edit'));
    // El mensaje se expone bajo la clave 'password' del bag 'userDeletion'
    // porque es la unica que el modal de borrado renderiza.
    $response->assertSessionHasErrors('password', null, 'userDeletion');
    expect(User::find($admin->id))->not->toBeNull();
});

test('un cajero con ventas recibe un mensaje claro al intentar borrarse del perfil', function () {
    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);
    seedSaleFor($caja);

    $response = $this->actingAs($caja)->delete(route('profile.destroy'), [
        'password' => 'password',
    ]);

    $response->assertRedirect(route('profile.edit'));
    $response->assertSessionHasErrors('password', null, 'userDeletion');
    expect(User::find($caja->id))->not->toBeNull();
});

test('un cajero sin historial si puede borrarse del perfil', function () {
    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);

    $response = $this->actingAs($caja)->delete(route('profile.destroy'), [
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    expect(User::find($caja->id))->toBeNull();
});
