<?php

use App\Models\Supplier;
use App\Models\User;

test('admin can create supplier with phone', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $response = $this
        ->actingAs($admin)
        ->post(route('suppliers.store'), [
            'name' => 'Distribuidora Andina',
            'phone' => '71234567',
            'is_active' => 1,
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('suppliers.index'));

    $supplier = Supplier::where('name', 'Distribuidora Andina')->first();

    expect($supplier)->not->toBeNull();
    expect($supplier->phone)->toBe('71234567');
    expect($supplier->is_active)->toBeTrue();
});

test('supplier name must be unique', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    Supplier::create(['name' => 'Duplicado', 'phone' => '111']);

    $response = $this
        ->actingAs($admin)
        ->from(route('suppliers.create'))
        ->post(route('suppliers.store'), [
            'name' => 'Duplicado',
            'phone' => '222',
        ]);

    $response->assertSessionHasErrors('name');
});

test('caja can access suppliers module', function () {
    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);

    $this->actingAs($caja)->get(route('suppliers.index'))->assertOk();
    $this->actingAs($caja)->get(route('suppliers.create'))->assertOk();
});

test('caja cannot delete supplier but admin can', function () {
    $caja = User::factory()->create(['role' => User::ROLE_CAJA]);
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $supplier = Supplier::create(['name' => 'Para eliminar', 'phone' => '333']);

    $this->actingAs($caja)
        ->delete(route('suppliers.destroy', $supplier))
        ->assertRedirect();
    expect(Supplier::where('name', 'Para eliminar')->exists())->toBeTrue();

    $this->actingAs($admin)
        ->delete(route('suppliers.destroy', $supplier))
        ->assertRedirect(route('suppliers.index'));
    expect(Supplier::where('name', 'Para eliminar')->exists())->toBeFalse();
});
