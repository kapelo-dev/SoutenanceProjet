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
        // Render : le LB interne (10.x) transmet la vraie IP via X-Forwarded-For
        $middleware->trustProxies(
            at: ['10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', '127.0.0.1'],
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );

        // Configuration du middleware d'authentification
        $middleware->redirectUsersTo('/login');
        $middleware->redirectGuestsTo('/login');
        
        // Enregistrer le middleware pour vérifier le changement de mot de passe
        $middleware->alias([
            'require.password.change' => \App\Http\Middleware\RequirePasswordChange::class,
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
