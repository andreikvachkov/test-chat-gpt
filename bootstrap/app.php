<?php

use App\Http\Controllers\ShopController;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [HandleInertiaRequests::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function ($response) {
            if (! $response instanceof Response) {
                return $response;
            }
            if ($response->getStatusCode() === 404 && ! request()->expectsJson() && ! request()->is('admin', 'admin/*', 'livewire/*', 'assets/*', 'build/*', 'storage/*')) {
                return app(ShopController::class)->render('404')->toResponse(request())->setStatusCode(404);
            }

            return $response;
        });
    })->create();
