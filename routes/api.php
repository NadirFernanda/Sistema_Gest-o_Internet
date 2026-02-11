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
// Clients listing: protect with token middleware if `API_CLIENTES_TOKEN` is set; always apply rate limiting
Route::middleware(['verify_api_token', 'throttle:60,1'])->get('/clientes', [ClienteController::class, 'index']);
Route::put('/clientes/{id}', [ClienteController::class, 'update']);
Route::delete('/clientes/{id}', [ClienteController::class, 'destroy']);
Route::post('/alertas/disparar', [ClienteController::class, 'dispararAlertas']);