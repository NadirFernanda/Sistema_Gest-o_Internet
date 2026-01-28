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
    Route::get('/clientes', [\App\Http\Controllers\ClienteController::class, 'index'])->name('clientes');
    Route::post('/clientes', [\App\Http\Controllers\ClienteController::class, 'store'])->name('clientes.store');
    Route::get('/clientes/{cliente}', [\App\Http\Controllers\ClienteController::class, 'show'])->name('clientes.show');
    Route::put('/clientes/{cliente}', [\App\Http\Controllers\ClienteController::class, 'update'])->name('clientes.update');
    Route::delete('/clientes/{cliente}', [\App\Http\Controllers\ClienteController::class, 'destroy'])->name('clientes.destroy');
    Route::get('/planos', fn () => view('planos'))->name('planos');
    Route::get('/alertas', fn () => view('alertas'))->name('alertas');

    // Relatório e cadastro de cobranças
    Route::get('/cobrancas', [\App\Http\Controllers\CobrancaController::class, 'index'])->name('cobrancas.index');
    Route::get('/cobrancas/export', [\App\Http\Controllers\CobrancaController::class, 'exportExcel'])->name('cobrancas.export');
    Route::get('/cobrancas/create', [\App\Http\Controllers\CobrancaController::class, 'create'])->name('cobrancas.create');
    Route::post('/cobrancas', [\App\Http\Controllers\CobrancaController::class, 'store'])->name('cobrancas.store');
    Route::get('/cobrancas/{id}', [\App\Http\Controllers\CobrancaController::class, 'show'])->name('cobrancas.show');


    // Rotas de equipamentos
    Route::get('/clientes/{cliente}/equipamentos/create', [\App\Http\Controllers\EquipamentoController::class, 'create'])->name('equipamentos.create');
    Route::post('/clientes/{cliente}/equipamentos', [\App\Http\Controllers\EquipamentoController::class, 'store'])->name('equipamentos.store');
});
// Rotas de Estoque de Equipamentos
Route::middleware('auth')->group(function () {
    Route::get('/estoque-equipamentos', [\App\Http\Controllers\EstoqueEquipamentoController::class, 'index'])->name('estoque_equipamentos.index');
    Route::get('/estoque-equipamentos/create', [\App\Http\Controllers\EstoqueEquipamentoController::class, 'create'])->name('estoque_equipamentos.create');
    Route::post('/estoque-equipamentos', [\App\Http\Controllers\EstoqueEquipamentoController::class, 'store'])->name('estoque_equipamentos.store');

    // Exportação específica do estoque de equipamentos
    Route::get('/estoque-equipamentos/export', function() {
        $equipamentos = \App\Models\EstoqueEquipamento::orderBy('nome')->get();
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\EstoqueEquipamentosExport($equipamentos), 'estoque_equipamentos.xlsx');
    })->name('estoque_equipamentos.export');

    // Vincular equipamentos do estoque a clientes
    Route::get('/clientes/{cliente}/vincular-equipamento', [\App\Http\Controllers\ClienteEquipamentoController::class, 'create'])->name('cliente_equipamento.create');
    Route::post('/clientes/{cliente}/vincular-equipamento', [\App\Http\Controllers\ClienteEquipamentoController::class, 'store'])->name('cliente_equipamento.store');
    Route::get('/clientes/{cliente}/vincular-equipamento/{vinculo}/editar', [\App\Http\Controllers\ClienteEquipamentoController::class, 'edit'])->name('cliente_equipamento.edit');
    Route::put('/clientes/{cliente}/vincular-equipamento/{vinculo}', [\App\Http\Controllers\ClienteEquipamentoController::class, 'update'])->name('cliente_equipamento.update');
    Route::delete('/clientes/{cliente}/vincular-equipamento/{vinculo}', [\App\Http\Controllers\ClienteEquipamentoController::class, 'destroy'])->name('cliente_equipamento.destroy');
});
