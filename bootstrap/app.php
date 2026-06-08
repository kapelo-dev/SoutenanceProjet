<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Render : proxy interne (10.x ou ::1) + Cloudflare (CF-Connecting-IP)
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_AWS_ELB,
        );

        // Configuration du middleware d'authentification
        $middleware->redirectUsersTo('/login');
        $middleware->redirectGuestsTo('/login');
        
        // Enregistrer le middleware pour vérifier le changement de mot de passe
        $middleware->alias([
            'require.password.change' => \App\Http\Middleware\RequirePasswordChange::class,
            'route.permission' => \App\Http\Middleware\CheckRoutePermission::class,
            'sms.api.token' => \App\Http\Middleware\ValidateSmsApiToken::class,
            'check.blocked.ip' => \App\Http\Middleware\CheckBlockedIp::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\CheckBlockedIp::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
