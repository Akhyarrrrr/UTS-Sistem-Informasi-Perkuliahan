<?php

use App\Http\Middleware\Role;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => Role::class]);
        // Middleware is configured before the console kernel loads application config.
        if (env('VERCEL')) {
            $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);
            $middleware->trustHosts(at: fn () => array_map(
                fn (string $host): string => '^'.preg_quote($host, '/').'$',
                array_filter([parse_url(config('app.url'), PHP_URL_HOST), config('app.vercel_url')]),
            ), subdomains: false);
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create()->addAbsoluteCachePathPrefix(sys_get_temp_dir());
