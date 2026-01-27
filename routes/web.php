<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Login routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Redirecionar / para login ou dashboard
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

// Rotas protegidas por auth
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    Route::get('/clientes', fn () => view('clientes'))->name('clientes');
    Route::get('/planos', fn () => view('planos'))->name('planos');
    Route::get('/alertas', fn () => view('alertas'))->name('alertas');
});
