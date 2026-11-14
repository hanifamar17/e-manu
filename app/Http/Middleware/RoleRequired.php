<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Pakai setelah 'auth':  ->middleware(['auth', 'role:admin,wali kelas'])
class RoleRequired
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        abort_unless($request->user()?->hasRole(...$roles), 403);

        return $next($request);
    }
}
