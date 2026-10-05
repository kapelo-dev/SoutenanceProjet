<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileAgentController;
use App\Http\Controllers\Api\MobileConfigController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OperateurController;
use App\Http\Controllers\KiosqueController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UtilisateurController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Préfixe /api, groupe de middleware "api" (sans session).
| Remarque : certaines URL /api/... existent aussi dans routes/web.php
| (auth session + route.permission) pour le back-office ; dans ce cas
| c'est la définition de web.php qui est utilisée par Laravel.
|
*/

/*
|--------------------------------------------------------------------------
| Routes publiques (avant connexion)
|--------------------------------------------------------------------------
*/

// Santé API mobile (ping depuis la page Configuration App Mobile)
Route::get('/mobile/ping', function () {
    return response()->json([
        'ok' => true,
        'app' => config('app.name', 'PDV Connect'),
        'message' => 'API mobile accessible.',
    ]);
});

Route::prefix('mobile')->group(function () {
    Route::get('/app-version', [MobileConfigController::class, 'appVersion']);
    Route::post('/verify-config-code', [MobileConfigController::class, 'verifyConfigCode'])
        ->middleware('throttle:20,1');
});

// Connexion / déconnexion agent mobile
Route::prefix('mobile/agent')->group(function () {
    Route::post('/login', [MobileAgentController::class, 'login'])
        ->middleware('throttle:10,1');
    // Hors auth.mobile : la déconnexion doit réussir même si le token a déjà expiré
    Route::post('/logout', [MobileAgentController::class, 'logout']);
});

// Ingestion des transactions depuis l'application Android (SMS) — token API SMS dédié
Route::post('/transactions/from-sms', [TransactionController::class, 'storeFromSms'])
    ->middleware('sms.api.token');

/*
|--------------------------------------------------------------------------
| Routes protégées — token obtenu via POST /api/mobile/agent/login
| (en-tête "Authorization: Bearer <token>")
|--------------------------------------------------------------------------
*/

Route::middleware('auth.mobile')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Espace agent mobile
    Route::prefix('mobile/agent')->group(function () {
        Route::get('/dashboard', [MobileAgentController::class, 'dashboard']);
        Route::post('/change-password', [MobileAgentController::class, 'changePassword']);
        Route::post('/transactions/{transaction}/annuler', [MobileAgentController::class, 'cancelTransaction']);
    });

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats-temps-reel', [DashboardController::class, 'statsTempsReel']);
        Route::get('/graphique-transactions', [DashboardController::class, 'graphiqueTransactions']);
        Route::get('/stats-par-operateur', [DashboardController::class, 'statsParOperateur']);
    });

    // Opérateurs
    Route::prefix('operateurs')->group(function () {
        Route::get('/', [OperateurController::class, 'index']);
        Route::get('/{operateur}/statistiques', [OperateurController::class, 'statistiques']);
    });

    // Kiosques
    Route::prefix('kiosques')->group(function () {
        Route::get('/proximite', [KiosqueController::class, 'proximite']);
        Route::get('/carte-data', [KiosqueController::class, 'carteData']);
        Route::get('/next-code', [KiosqueController::class, 'getNextCode']);
        Route::get('/', [KiosqueController::class, 'index']);
        Route::get('/{kiosque}', [KiosqueController::class, 'show']);
    });

    // Agents
    Route::get('/agents/{agent}/soldes', [AgentController::class, 'getSoldes']);

    // Transactions
    Route::get('/transactions/statistiques', [TransactionController::class, 'statistiques']);

    // Utilisateurs
    Route::get('/utilisateurs/{utilisateur}/liens', [UtilisateurController::class, 'liensAccessibles']);
});
