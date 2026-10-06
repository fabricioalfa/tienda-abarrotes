<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use RuntimeException;

/**
 * Siembra los datos minimos para que el sistema arranque.
 *
 * Usuarios:
 *   Las credenciales NUNCA estan escritas en el codigo. Se leen del entorno
 *   (ADMIN_* / CASHIER_*). En produccion el seedor se niega a correr si no se
 *   proporcionan, de modo que no se puedan crear cuentas con la contrasena
 *   "password" por accidente.
 *
 * Para crear el primer administrador en un servidor nuevo:
 *   1. Agregar ADMIN_EMAIL, ADMIN_PASSWORD y ADMIN_NAME a .env
 *   2. php artisan migrate --force
 *   3. php artisan db:seed --force
 */
class DatabaseSeeder extends Seeder
{
    /** Contrasena que se usa solo en local/testing. Nunca en produccion. */
    private const DEV_PASSWORD = 'password';

    /** Email de respaldo para local/testing. Nunca se usa en produccion. */
    private const DEV_EMAIL = 'dev@tienda.local';

    public function run(): void
    {
        $this->seedUsers();
        $this->seedCategories();
    }

    protected function seedUsers(): void
    {
        $isProduction = app()->environment('production');

        $this->createUser(
            config: 'seeders.admin',
            role: User::ROLE_ADMIN,
            isProduction: $isProduction,
        );

        $this->createUser(
            config: 'seeders.cashier',
            role: User::ROLE_CAJA,
            isProduction: $isProduction,
            isOptional: true,
        );
    }

    protected function createUser(
        string $config,
        string $role,
        bool $isProduction,
        bool $isOptional = false,
    ): void {
        $name = config("{$config}.name");
        $email = config("{$config}.email");
        $password = config("{$config}.password");
        $label = strtoupper(str_replace('seeders.', '', $config));

        // El usuario de caja es opcional: sin email no se crea ninguna cuenta.
        if ($isOptional && blank($email)) {
            return;
        }

        if (blank($email)) {
            if ($isProduction) {
                throw new RuntimeException(
                    "Falta la variable {$label}_EMAIL en el archivo .env."
                );
            }

            $email = self::DEV_EMAIL;
        }

        if (blank($password)) {
            if ($isProduction) {
                throw new RuntimeException(
                    "Falta la variable {$label}_PASSWORD en el archivo .env. "
                    .'El seeder no crea usuarios con contrasenas por defecto en produccion. '
                    ."Define {$label}_PASSWORD (minimo 8 caracteres) y vuelve a ejecutar."
                );
            }

            $password = self::DEV_PASSWORD;
        }

        if ($isProduction && strlen($password) < 8) {
            throw new RuntimeException(
                "La variable {$label}_PASSWORD debe tener al menos 8 caracteres."
            );
        }

        if ($isProduction && $password === self::DEV_PASSWORD) {
            throw new RuntimeException(
                "La variable {$label}_PASSWORD no puede ser la contrasena por defecto. "
                .'Elige una contrasena distinta antes de sembrar en produccion.'
            );
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
                'role' => $role,
                'email_verified_at' => Carbon::now(),
            ]
        );
    }

    protected function seedCategories(): void
    {
        Category::updateOrCreate(
            ['name' => 'Carnicería'],
            ['description' => 'Productos vendidos por peso.']
        );

        Category::updateOrCreate(
            ['name' => 'Abarrotes'],
            ['description' => 'Productos vendidos por unidad.']
        );
    }
}
