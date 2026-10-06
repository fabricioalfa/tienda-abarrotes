<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Credenciales del seeder
    |--------------------------------------------------------------------------
    |
    | Estas credenciales solo se usan al ejecutar `php artisan db:seed` para
    | crear el primer administrador de una instalacion nueva.
    |
    | Se leen con config() y no con env() a proposito: cuando la aplicacion
    | tiene `php artisan config:cache` ejecutado, Laravel deja de cargar el
    | archivo .env (ver LoadEnvironmentVariables::bootstrap), por lo que
    | env() devolveria null y el seeder fallaria aunque las variables esten
    | escritas en el .env. config() si funciona antes y despues del cache.
    |
    | El seeder se niega a correr en produccion si la contrasena falta, es muy
    | corta o es la contrasena de desarrollo por defecto.
    |
    */

    'admin' => [
        'name' => env('ADMIN_NAME', 'Administrador'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    /*
    | El usuario de caja es opcional: si dejas 'email' vacio, el seeder no crea
    | ninguna cuenta de caja y puedes darlas de alta despues desde la app.
    */
    'cashier' => [
        'name' => env('CASHIER_NAME', 'Usuario Caja'),
        'email' => env('CASHIER_EMAIL'),
        'password' => env('CASHIER_PASSWORD'),
    ],

];
