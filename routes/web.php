<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Demo1Controller;
use App\Http\Controllers\Demo2Controller;

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

Route::get('/', function () {
    return view('welcome');
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

Route::get('/dashboard', function () {
    return view('pages.dashboard.index');
}); 

Route::get('/transactions', function () {
    return view('pages.transactions.index');
}); 

Route::get('/roles-et-permissions/gestion-roles', function () {
    return view('pages.roles_et_permissions.gestion_roles.index');
}); 

Route::get('/agents/liste-agents', function () {
    return view('pages.agents.liste_agents.index');
}); 

Route::get('/agents/soldes', function () {
    return view('pages.agents.solde.index');
}); 

Route::get('/utilisateurs', function () {
    return view('pages.utilisateurs.index');
}); 

Route::get('/rapports', function () {
    return view('pages.rapports.index');
}); 

Route::get('/operations-agence', function () {
    return view('pages.operation_agence.index');
}); 

Route::get('/roles-et-permissions/gestion-permissions', function () {
    return view('pages.roles_et_permissions.gestion_permissions.index');
}); 

Route::get('/roles-et-permissions/gestion-routes', function () {
    return view('pages.roles_et_permissions.gestion_routes.index');
}); 