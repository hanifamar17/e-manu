<?php

use App\Http\Middleware\RoleRequired;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\SecurityHeaders;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias(['role' => RoleRequired::class]);
        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias(['role' => RoleRequired::class]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->append(SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Pengganti handler timeout -> koneksi DB gagal
        $exceptions->render(function (QueryException $e, Request $request) {
            if (config('app.debug')) {
                return null;
            }

            return response()->view('errors.db', [], 503);
        });
    })->create();
