<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlanoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;

Route::get('/alertas', [ClienteController::class, 'listarAlertas']);

Route::post('/planos', [PlanoController::class, 'store']);
Route::get('/planos', [PlanoController::class, 'index']);
Route::get('/planos/{id}', [PlanoController::class, 'show']);
Route::put('/planos/{id}', [PlanoController::class, 'update']);
Route::delete('/planos/{id}', [PlanoController::class, 'destroy']);
Route::post('/login', [AuthController::class, 'login']);
// Clients endpoints: protect with VerifyApiToken middleware + rate limiting.
// Use fully-qualified class name to avoid relying on route middleware alias resolution.
Route::middleware([\App\Http\Middleware\VerifyApiToken::class, 'throttle:60,1'])->group(function () {
	Route::post('/clientes', [ClienteController::class, 'store']);
	Route::get('/clientes', [ClienteController::class, 'index']);
	Route::put('/clientes/{id}', [ClienteController::class, 'update']);
	Route::delete('/clientes/{id}', [ClienteController::class, 'destroy']);
});
Route::post('/alertas/disparar', [ClienteController::class, 'dispararAlertas']);