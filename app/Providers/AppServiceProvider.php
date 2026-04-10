<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Forzar HTTPS en producción
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Rate limiting adicional por ruta
        $this->configureRateLimiting();
    }

    protected function configureRateLimiting(): void
    {
        // Login: 5 intentos por minuto por IP+email (ya lo hace LoginRequest,
        // pero esto agrega una capa extra a nivel de ruta)
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(
                $request->string('email')->lower().'|'.$request->ip()
            );
        });

        // Rutas de escritura (POST/PUT/DELETE) en la API interna: 60/min por usuario
        RateLimiter::for('mutations', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(60)->by($request->user()->id)
                : Limit::perMinute(10)->by($request->ip());
        });

        // Ventas: máximo 30 ventas por minuto por usuario (previene automatización)
        RateLimiter::for('sales', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(30)->by('sales|'.$request->user()->id)
                : Limit::none();
        });
    }
}
