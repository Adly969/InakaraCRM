<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->respond(function ($response, $exception, Request $request) {
            if (in_array($response->getStatusCode(), [403, 404, 419, 500, 503])) {
                $status = $response->getStatusCode();
                $page = match ($status) {
                    403 => 'errors/403',
                    419 => 'errors/419',
                    500, 503 => 'errors/500',
                    default => 'errors/404',
                };

                return \Inertia\Inertia::render($page, [
                    'status' => $status,
                ])
                ->toResponse($request)
                ->setStatusCode($status);
            }

            return $response;
        });
    })->create();
