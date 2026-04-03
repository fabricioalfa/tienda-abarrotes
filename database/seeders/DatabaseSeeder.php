<?php
// filepath: /home/fabri/Documentos/tienda/database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@tienda.local'],
            [
                'name' => 'Administrador',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => Carbon::now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'caja@tienda.local'],
            [
                'name' => 'Usuario Caja',
                'password' => 'password',
                'role' => User::ROLE_CAJA,
                'email_verified_at' => Carbon::now(),
            ]
        );

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