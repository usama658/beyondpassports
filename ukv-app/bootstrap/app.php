<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust reverse proxies (Cloudflare tunnel/CDN, Forge/LB, Laravel Cloud) so the app honours
        // X-Forwarded-Proto/Host and generates correct https://<public-host> asset/route URLs
        // instead of the internal http://127.0.0.1. Required behind any proxy in production too.
        $middleware->trustProxies(at: '*');
        // Stripe posts its webhook without a CSRF token.
        $middleware->validateCsrfTokens(except: ['stripe/webhook']);
        // Security headers (CSP/HSTS/etc.) on web responses; admin gets a Filament-safe CSP.
        $middleware->web(append: [SecurityHeaders::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
