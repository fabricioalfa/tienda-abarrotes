<?php
// filepath: /home/fabri/Documentos/tienda/app/Http/Middleware/EnsureUserRole.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_if(! $user, 401);
        abort_if(! in_array($user->role, $roles, true), 403);

        return $next($request);
    }
}