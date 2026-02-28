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

// Temporary debug endpoint to diagnose search issues.
// Enabled only when APP_DEBUG=true or when called from localhost.
Route::get('/_debug/planos', function (\Illuminate\Http\Request $request) {
	if (!config('app.debug') && $request->ip() !== '127.0.0.1') {
		abort(404);
	}
	$busca = (string) $request->query('busca', '');
	$buscaParam = mb_strtolower(trim($busca));
	if ($buscaParam === '') {
		return response()->json(['error' => 'missing busca param'], 400);
	}

	$bp = "%{$buscaParam}%";
	try {
		$driver = \Illuminate\Support\Facades\DB::getDriverName();
	} catch (\Exception $e) {
		$driver = config('database.default');
	}
	if ($driver === 'pgsql' || str_contains($driver, 'pgsql')) {
		$precoCast = 'CAST(preco AS TEXT)';
		$cicloCast = 'CAST(ciclo AS TEXT)';
	} else {
		$precoCast = 'CAST(preco AS CHAR)';
		$cicloCast = 'CAST(ciclo AS CHAR)';
	}

	$counts = [];
	$counts['planos_nome'] = \Illuminate\Support\Facades\DB::table('planos')->whereRaw('LOWER(planos.nome) LIKE ?', [$bp])->count();
	$counts['planos_descricao'] = \Illuminate\Support\Facades\DB::table('planos')->whereRaw('LOWER(planos.descricao) LIKE ?', [$bp])->count();
	$counts['template'] = \Illuminate\Support\Facades\DB::table('plan_templates')->whereRaw('LOWER(name) LIKE ? OR LOWER(COALESCE(description, \'\')) LIKE ?', [$bp,$bp])->count();
	$counts['cliente'] = \Illuminate\Support\Facades\DB::table('clientes')->whereRaw('LOWER(nome) LIKE ? OR LOWER(COALESCE(bi, \'\')) LIKE ?', [$bp,$bp])->count();
	$counts['preco'] = \Illuminate\Support\Facades\DB::table('planos')->whereRaw("LOWER({$precoCast}) LIKE ?", [$bp])->count();
	$counts['ciclo'] = \Illuminate\Support\Facades\DB::table('planos')->whereRaw("LOWER({$cicloCast}) LIKE ?", [$bp])->count();

	// Final rows matched by the controller-like query (approximation)
	$planosQuery = \App\Models\Plano::query();
	$planosQuery->where(function ($q) use ($bp) {
		$q->whereRaw('LOWER(planos.nome) LIKE ?', [$bp])
		  ->orWhereRaw('LOWER(planos.descricao) LIKE ?', [$bp])
		  ->orWhereRaw('LOWER(planos.estado) LIKE ?', [$bp]);
	});
	$planosQuery->orWhereHas('template', function ($q) use ($bp) {
		$q->whereRaw('LOWER(name) LIKE ?', [$bp])
		  ->orWhereRaw("LOWER(COALESCE(description, '')) LIKE ?", [$bp]);
	});
	$planosQuery->orWhereHas('cliente', function ($q) use ($bp) {
		$q->whereRaw('LOWER(nome) LIKE ?', [$bp])
		  ->orWhereRaw("LOWER(COALESCE(bi, '')) LIKE ?", [$bp]);
	});
	$planosQuery->orWhereRaw("LOWER({$precoCast}) LIKE ?", [$bp])
				->orWhereRaw("LOWER({$cicloCast}) LIKE ?", [$bp]);
	$counts['final_query_count'] = $planosQuery->count();

	return response()->json([
		'busca' => $busca,
		'driver' => $driver,
		'counts' => $counts,
	]);
});