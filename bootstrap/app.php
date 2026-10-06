<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
            'auth.mobile' => \App\Http\Middleware\AuthenticateMobileToken::class,
        ]);

        // Vérifier le token mobile AVANT la résolution des modèles ({kiosque}, {agent}...)
        // pour ne pas révéler l'existence d'un enregistrement à un client non authentifié
        $middleware->prependToPriorityList(
            before: \Illuminate\Routing\Middleware\SubstituteBindings::class,
            prepend: \App\Http\Middleware\AuthenticateMobileToken::class,
        );

        $middleware->web(append: [
            \App\Http\Middleware\CheckBlockedIp::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Navigation AJAX (ajax-navigation.js) : renvoyer l'erreur en JSON plutôt qu'une page HTML complète
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Votre session a expiré. Veuillez vous reconnecter.',
                    'redirect' => route('login'),
                ], 401);
            }
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if (! $request->ajax() && ! $request->expectsJson()) {
                return null;
            }

            $status = $e->getStatusCode();
            $isExplicitAbort = get_class($e) === HttpException::class && $e->getPrevious() === null;

            $defaults = [
                401 => 'Vous devez être connecté pour accéder à cette ressource.',
                403 => "Vous n'avez pas les autorisations nécessaires.",
                404 => 'Ressource introuvable.',
                419 => 'Votre session a expiré. Veuillez actualiser la page.',
                429 => 'Trop de requêtes. Veuillez patienter avant de réessayer.',
                503 => 'Service momentanément indisponible.',
            ];

            $message = $isExplicitAbort && $e->getMessage() !== ''
                ? $e->getMessage()
                : ($defaults[$status] ?? 'Une erreur inattendue est survenue.');

            $payload = ['success' => false, 'message' => $message];
            if (in_array($status, [401, 419], true)) {
                $payload['redirect'] = route('login');
            }

            return response()->json($payload, $status, $e->getHeaders());
        });
    })->create();
