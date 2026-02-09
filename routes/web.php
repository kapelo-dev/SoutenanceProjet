<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Demo1Controller;
use App\Http\Controllers\Demo2Controller;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OperateurController;
use App\Http\Controllers\KiosqueController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AgentDashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\OperationsAgenceController;
use App\Http\Controllers\PublicPagesController;
use App\Http\Controllers\GestionEntrepriseController;
use App\Http\Controllers\ConfigAppMobileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/*
|--------------------------------------------------------------------------
| Public Pages Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/
Route::get('/documentation', [PublicPagesController::class, 'documentation'])->name('public.documentation');
Route::get('/faq', [PublicPagesController::class, 'faq'])->name('public.faq');
Route::get('/support', [PublicPagesController::class, 'support'])->name('public.support');
Route::get('/license', [PublicPagesController::class, 'license'])->name('public.license');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Routes pour le changement de mot de passe (première connexion)
// Ces routes ne nécessitent PAS le middleware require.password.change pour éviter les boucles de redirection
Route::middleware(['auth'])->group(function () {
    Route::get('/password/change', [AuthController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/password/change', [AuthController::class, 'changePassword']);
});

// Rediriger la racine vers le dashboard si connecté, sinon vers login
Route::get('/', function () {
    if (auth()->check()) {
        // Vérifier si c'est la première connexion (dernier_connexion est null)
        if (is_null(auth()->user()->dernier_connexion)) {
            return redirect()->route('password.change');
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Demo 1 routes
Route::get('/demo1', function () {
    return view('pages.demo1.index');
});

// Demo 2 routes
Route::get('/demo2', function () {
    return view('pages.demo2.index');
});

// Demo 3 routes
Route::get('/demo3', function () {
    return view('pages.demo3.index');
});

// Demo 4 routes
Route::get('/demo4', function () {
    return view('pages.demo4.index');
});

// Demo 5 routes
Route::get('/demo5', function () {
    return view('pages.demo5.index');
});

// Demo 6 routes
Route::get('/demo6', function () {
    return view('pages.demo6.index');
});

// Demo 7 routes
Route::get('/demo7', function () {
    return view('pages.demo7.index');
});

// Demo 8 routes
Route::get('/demo8', function () {
    return view('pages.demo8.index');
});

// Demo 9 routes
Route::get('/demo9', function () {
    return view('pages.demo9.index');
})->name('demo9.index');

Route::get('/demo9/profile', function () {
    return view('pages.demo9.profile');
})->name('demo9.profile');

// Demo 10 routes
Route::get('/demo10', function () {
    return view('pages.demo10.index');
});

/*
|--------------------------------------------------------------------------
| Application Routes (Protected)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'require.password.change'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/stats-temps-reel', [DashboardController::class, 'statsTempsReel']);
    Route::get('/api/dashboard/graphique-transactions', [DashboardController::class, 'graphiqueTransactions']);
    Route::get('/api/dashboard/stats-par-operateur', [DashboardController::class, 'statsParOperateur']);
    Route::get('/api/dashboard/carte-performance-mois', [DashboardController::class, 'cartePerformanceMois']);

    // Opérateurs
    Route::resource('operateurs', OperateurController::class);
    Route::post('/operateurs/{operateur}/toggle-status', [OperateurController::class, 'toggleStatus'])->name('operateurs.toggle-status');
    Route::get('/api/operateurs/{operateur}/statistiques', [OperateurController::class, 'statistiques']);

    // Kiosques
    Route::resource('kiosques', KiosqueController::class);
    Route::get('/kiosques-carte', [KiosqueController::class, 'carte'])->name('kiosques.carte');
    Route::get('/api/kiosques/proximite', [KiosqueController::class, 'proximite']);
    Route::get('/api/kiosques/carte-data', [KiosqueController::class, 'carteData']);
    Route::post('/kiosques/{kiosque}/assigner-agent', [KiosqueController::class, 'assignerAgent'])->name('kiosques.assigner-agent');
    Route::delete('/kiosques/{kiosque}/agents/{agent}', [KiosqueController::class, 'retirerAgent'])->name('kiosques.retirer-agent');

    // Agents
    // Routes spécifiques AVANT le resource pour éviter les conflits
    Route::get('/agent/dashboard', [AgentDashboardController::class, 'index'])->name('agent.dashboard');
    Route::get('/agents/liste-agents', [AgentController::class, 'index'])->name('agents.liste-agents');
    Route::get('/agents/soldes', [AgentController::class, 'soldes'])->name('agents.soldes');
    Route::get('/agents-soldes', [AgentController::class, 'soldes'])->name('agents.soldes-alt');

    // Resource routes (doit être après les routes spécifiques)
    Route::resource('agents', AgentController::class);
    Route::post('/agents/store-with-kiosque', [AgentController::class, 'storeWithKiosque'])->name('agents.store-with-kiosque');
    Route::post('/agents/{agent}/montants-initiaux', [AgentController::class, 'storeMontantsInitiaux'])->name('agents.store-montants-initiaux');
    Route::post('/agents/{agent}/update-solde', [AgentController::class, 'updateSolde'])->name('agents.update-solde');
    Route::get('/api/agents/{agent}/soldes', [AgentController::class, 'getSoldes']);
    Route::post('/agents/{agent}/change-statut', [AgentController::class, 'changeStatut'])->name('agents.change-statut');

    // Transactions
    Route::resource('transactions', TransactionController::class);
    Route::post('/transactions/{transaction}/annuler', [TransactionController::class, 'annuler'])->name('transactions.annuler');
    Route::get('/api/transactions/statistiques', [TransactionController::class, 'statistiques']);
    Route::get('/transactions/export', [TransactionController::class, 'export'])->name('transactions.export');

    // Utilisateurs
    Route::get('/api/utilisateurs/{utilisateur}', [UtilisateurController::class, 'show'])->name('api.utilisateurs.show');
    Route::resource('utilisateurs', UtilisateurController::class);
    Route::post('/utilisateurs/{utilisateur}/change-statut', [UtilisateurController::class, 'changeStatut'])->name('utilisateurs.change-statut');
    Route::get('/api/utilisateurs/{utilisateur}/liens', [UtilisateurController::class, 'liensAccessibles']);
    Route::post('/utilisateurs/{utilisateur}/reset-password', [UtilisateurController::class, 'resetPassword'])->name('utilisateurs.reset-password');
    Route::get('/api/my-permissions', [UtilisateurController::class, 'getMyPermissions'])->name('api.my-permissions');

    // Rôles (Profils)
    Route::get('/roles-et-permissions/gestion-roles', [RoleController::class, 'index'])->name('roles-et-permissions.gestion-roles');
    Route::post('/roles-et-permissions/gestion-roles', [RoleController::class, 'store'])->name('roles.store');
    Route::put('/roles-et-permissions/gestion-roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles-et-permissions/gestion-roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    Route::get('/roles-et-permissions/gestion-roles/{role}', [RoleController::class, 'show'])->name('roles.show'); 

    // Permissions
    Route::get('/roles-et-permissions/gestion-permissions', [PermissionController::class, 'index'])->name('roles-et-permissions.gestion-permissions');
    Route::post('/roles-et-permissions/gestion-permissions/toggle', [PermissionController::class, 'toggle'])->name('permissions.toggle');
    Route::post('/roles-et-permissions/gestion-permissions/save-all', [PermissionController::class, 'saveAll'])->name('permissions.save-all'); 

    Route::get('/roles-et-permissions/gestion-routes', [RouteController::class, 'index'])->name('roles-et-permissions.gestion-routes');
    Route::post('/roles-et-permissions/gestion-routes', [RouteController::class, 'store'])->name('routes.store');
    Route::put('/roles-et-permissions/gestion-routes/visibility', [RouteController::class, 'updateVisibility'])->name('routes.update-visibility'); 

    Route::get('/rapports', [RapportController::class, 'index'])->name('rapports.index'); 

    Route::get('/operations-agence', [OperationsAgenceController::class, 'index'])->name('operations-agence.index');
    Route::post('/operations-agence', [OperationsAgenceController::class, 'store'])->name('operations-agence.store');

    // Gestion d'entreprise (Salaires, Formules, Trésorerie)
    Route::get('/gestion-entreprise', [GestionEntrepriseController::class, 'index'])->name('gestion-entreprise.index');
    
    // Paramètres de salaire
    Route::post('/gestion-entreprise/parametres', [GestionEntrepriseController::class, 'storeParametre'])->name('gestion-entreprise.parametres.store');
    Route::put('/gestion-entreprise/parametres/{parametre}', [GestionEntrepriseController::class, 'updateParametre'])->name('gestion-entreprise.parametres.update');
    Route::delete('/gestion-entreprise/parametres/{parametre}', [GestionEntrepriseController::class, 'deleteParametre'])->name('gestion-entreprise.parametres.delete');
    
    // Salaires
    Route::post('/gestion-entreprise/salaires/generer', [GestionEntrepriseController::class, 'genererSalaires'])->name('gestion-entreprise.generer-salaires');
    Route::post('/gestion-entreprise/salaires/{salaire}/payer', [GestionEntrepriseController::class, 'payerSalaire'])->name('gestion-entreprise.salaires.payer');
    
    // Mouvements de trésorerie
    Route::post('/gestion-entreprise/mouvements', [GestionEntrepriseController::class, 'storeMouvement'])->name('gestion-entreprise.mouvements.store');

    // Configuration application Android (endpoint, token, numéro filtre SMS)
    Route::get('/parametres-app-mobile', [ConfigAppMobileController::class, 'index'])->name('parametres-app-mobile.index');
    Route::post('/parametres-app-mobile', [ConfigAppMobileController::class, 'store'])->name('parametres-app-mobile.store');
    Route::post('/parametres-app-mobile/generate-token', [ConfigAppMobileController::class, 'generateToken'])->name('parametres-app-mobile.generate-token');
});
