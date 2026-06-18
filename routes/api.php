<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Public API Routes (pas d'authentification requise pour le développement)
|--------------------------------------------------------------------------
*/

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
Route::prefix('agents')->group(function () {
    Route::get('/{agent}/soldes', [AgentController::class, 'getSoldes']);
});

// Santé API mobile (ping depuis la page Configuration App Mobile)
Route::get('/mobile/ping', function () {
    return response()->json([
        'ok' => true,
        'app' => config('app.name', 'PDV Connect'),
        'message' => 'API mobile accessible.',
    ]);
});

Route::prefix('mobile')->group(function () {
    Route::post('/verify-config-code', [\App\Http\Controllers\Api\MobileConfigController::class, 'verifyConfigCode'])
        ->middleware('throttle:20,1');
});

// Transactions
Route::prefix('transactions')->group(function () {
    Route::get('/statistiques', [TransactionController::class, 'statistiques']);
    // Ingestion depuis l'application Android (SMS) — authentification par token
    Route::post('/from-sms', [TransactionController::class, 'storeFromSms'])
        ->middleware('sms.api.token');
});

// Espace agent mobile (connexion + dashboard)
Route::prefix('mobile/agent')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Api\MobileAgentController::class, 'login']);
    Route::get('/dashboard', [\App\Http\Controllers\Api\MobileAgentController::class, 'dashboard']);
    Route::post('/logout', [\App\Http\Controllers\Api\MobileAgentController::class, 'logout']);
    Route::post('/transactions/{transaction}/annuler', [\App\Http\Controllers\Api\MobileAgentController::class, 'cancelTransaction']);
});

// Utilisateurs
Route::prefix('utilisateurs')->group(function () {
    Route::get('/{utilisateur}/liens', [UtilisateurController::class, 'liensAccessibles']);
});

/*
|--------------------------------------------------------------------------
| Protected API Routes (authentification requise - à activer en production)
|--------------------------------------------------------------------------
*/

// Route::middleware('auth:sanctum')->group(function () {
//     // Routes protégées ici
// });
