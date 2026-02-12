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
Route::post('/clientes', [ClienteController::class, 'store']);
// Clients listing: (TEMP) remove token middleware for debugging; keep rate limit
// To re-enable token protection, restore 'verify_api_token' in the middleware array.
Route::middleware(['throttle:60,1'])->get('/clientes', [ClienteController::class, 'index']);
Route::put('/clientes/{id}', [ClienteController::class, 'update']);
Route::delete('/clientes/{id}', [ClienteController::class, 'destroy']);
Route::post('/alertas/disparar', [ClienteController::class, 'dispararAlertas']);