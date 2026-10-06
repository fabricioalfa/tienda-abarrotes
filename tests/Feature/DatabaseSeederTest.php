<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

/**
 * El seeder es la primera barrera de seguridad de una instalacion nueva: si
 * crea un admin con contrasena "password" en un servidor publico, cualquiera
 * que pruebe admin@tienda.local entra al sistema.
 */
beforeEach(function () {
    $this->seedKeys = function (array $overrides = []) {
        foreach (array_replace_recursive([
            'seeders.admin.name' => 'Administrador',
            'seeders.admin.email' => 'admin@tienda.local',
            'seeders.admin.password' => 'password',
            'seeders.cashier.name' => 'Usuario Caja',
            'seeders.cashier.email' => 'caja@tienda.local',
            'seeders.cashier.password' => 'password',
        ], $overrides) as $key => $value) {
            config()->set($key, $value);
        }
    };
});

/** Simula APP_ENV=production sin depender de un .env real. */
function asProduction(): void
{
    app()['env'] = 'production';
}

test('el seeder crea admin y caja en local con contrasena de desarrollo', function () {
    ($this->seedKeys)();
    app()['env'] = 'local';

    expect(Artisan::call('db:seed', ['--force' => true]))->toBe(0);

    $admin = User::where('email', 'admin@tienda.local')->first();
    expect($admin)->not->toBeNull();
    expect($admin->role)->toBe(User::ROLE_ADMIN);
    expect(Hash::check('password', $admin->password))->toBeTrue();
    expect(User::where('email', 'caja@tienda.local')->exists())->toBeTrue();
});

test('el seeder siembra las categorias base', function () {
    ($this->seedKeys)();
    app()['env'] = 'local';

    Artisan::call('db:seed', ['--force' => true]);

    expect(Category::count())->toBe(2);
});

test('en produccion falla si falta la contrasena del admin', function () {
    ($this->seedKeys)(['seeders.admin.password' => null]);
    asProduction();

    expect(fn () => Artisan::call('db:seed', ['--force' => true]))
        ->toThrow(RuntimeException::class);
    expect(User::where('email', 'admin@tienda.local')->exists())->toBeFalse();
});

test('en produccion falla si la contrasena del admin es demasiado corta', function () {
    ($this->seedKeys)(['seeders.admin.password' => 'corta7']);
    asProduction();

    expect(fn () => Artisan::call('db:seed', ['--force' => true]))
        ->toThrow(RuntimeException::class);
});

test('en produccion falla si se intenta usar la contrasena por defecto', function () {
    // Escenario critico: alguien despliega en un servidor publico sin tocar la
    // contrasena y deja una cuenta de administrador ampliamente conocida.
    ($this->seedKeys)(['seeders.admin.password' => 'password']);
    asProduction();

    expect(fn () => Artisan::call('db:seed', ['--force' => true]))
        ->toThrow(RuntimeException::class);
});

test('en produccion falla si falta el email del admin', function () {
    ($this->seedKeys)(['seeders.admin.email' => null]);
    asProduction();

    expect(fn () => Artisan::call('db:seed', ['--force' => true]))
        ->toThrow(RuntimeException::class);
});

test('en produccion crea el admin si las credenciales son validas', function () {
    ($this->seedKeys)([
        'seeders.admin.email' => 'admin@tienda.com',
        'seeders.admin.password' => 'Pass-Fuerte-2026',
        'seeders.cashier.email' => 'caja@tienda.com',
        'seeders.cashier.password' => 'Otra-Clave-2026',
    ]);
    asProduction();

    expect(Artisan::call('db:seed', ['--force' => true]))->toBe(0);

    $admin = User::where('email', 'admin@tienda.com')->first();
    expect($admin)->not->toBeNull();
    expect(Hash::check('Pass-Fuerte-2026', $admin->password))->toBeTrue();
    expect(User::where('email', 'caja@tienda.com')->exists())->toBeTrue();
});

test('el usuario de caja es opcional: sin email no se crea', function () {
    ($this->seedKeys)([
        'seeders.admin.password' => 'Pass-Fuerte-2026',
        'seeders.cashier.email' => null,
        'seeders.cashier.password' => null,
    ]);
    asProduction();

    expect(Artisan::call('db:seed', ['--force' => true]))->toBe(0);

    expect(User::where('role', User::ROLE_CAJA)->exists())->toBeFalse();
    expect(User::where('role', User::ROLE_ADMIN)->exists())->toBeTrue();
});

test('el seeder lee de config() y no de env(), asi sobrevive a config:cache', function () {
    // Cuando hay config cacheado, Laravel deja de cargar el .env
    // (LoadEnvironmentVariables::bootstrap aborta si configurationIsCached()),
    // por lo que env() devolveria null y el seed no podria crear el admin.
    // Aqui se simula ese estado: config() tiene los valores, env() no.
    ($this->seedKeys)([
        'seeders.admin.email' => 'admin@tienda.com',
        'seeders.admin.password' => 'Pass-Fuerte-2026',
        'seeders.cashier.email' => null,
        'seeders.cashier.password' => null,
    ]);
    asProduction();

    expect(Artisan::call('db:seed', ['--force' => true]))->toBe(0);

    $admin = User::where('email', 'admin@tienda.com')->first();
    expect($admin)->not->toBeNull();
    expect(Hash::check('Pass-Fuerte-2026', $admin->password))->toBeTrue();
});
