<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlanoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;

// Public catalog of plan templates (used by the loja to show family/business plans)
Route::get('/plan-templates', [\App\Http\Controllers\PlanTemplateCatalogController::class, 'index']);

// Contador público de clientes activos — usado pela loja na barra de estatísticas.
// Conta os planos com ativo=true; resultado em cache no SG por 2 minutos.
Route::get('/stats/active-clients', function () {
    $count = \Illuminate\Support\Facades\Cache::remember('sg_active_clients_count', 120, function () {
        return \App\Models\Plano::where('ativo', true)->count();
    });
    return response()->json(['active_clients' => $count]);
})->middleware('throttle:30,1');

// Public catalog of equipment for sale (used by the loja equipment page)
Route::get('/equipment-catalog', [\App\Http\Controllers\PublicEquipmentCatalogController::class, 'index']);

// Client lookup by phone — read-only, public (no token required).
// Used by the loja checkout form to pre-fill fields for returning customers.
Route::get('/cliente-lookup', [\App\Http\Controllers\AutovendaJanelaController::class, 'lookup'])->middleware('throttle:30,1');

// Loja autovenda: create/extend client plan window after payment confirmation
// Called by the loja webhook after GPO payment is confirmed.
Route::middleware([\App\Http\Middleware\VerifyApiToken::class])->group(function () {
    Route::post('/janela-autovenda', [\App\Http\Controllers\AutovendaJanelaController::class, 'store']);
});
// Clients endpoints: protect with VerifyApiToken middleware + rate limiting.
// Use fully-qualified class name to avoid relying on route middleware alias resolution.
Route::middleware([\App\Http\Middleware\VerifyApiToken::class, 'throttle:60,1'])->group(function () {
	Route::post('/clientes', [ClienteController::class, 'store']);
	Route::get('/clientes', [ClienteController::class, 'index']);
	Route::put('/clientes/{id}', [ClienteController::class, 'update']);
	Route::delete('/clientes/{id}', [ClienteController::class, 'destroy']);
	Route::post('/alertas/disparar', [ClienteController::class, 'dispararAlertas']);
	Route::get('/alertas', [ClienteController::class, 'listarAlertas']);
	Route::post('/planos', [PlanoController::class, 'store']);
	Route::get('/planos', [PlanoController::class, 'index']);
	Route::get('/planos/{id}', [PlanoController::class, 'show']);
	Route::put('/planos/{id}', [PlanoController::class, 'update']);
	Route::delete('/planos/{id}', [PlanoController::class, 'destroy']);
});