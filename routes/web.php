<?php



use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\PasswordController;

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
    Route::post('/clientes', [\App\Http\Controllers\ClienteController::class, 'store'])->name('clientes.store')->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':clientes.create');
    Route::get('/clientes/{cliente}', [\App\Http\Controllers\ClienteController::class, 'show'])->name('clientes.show');
    Route::get('/clientes/{cliente}/ficha', [\App\Http\Controllers\ClienteController::class, 'ficha'])->name('clientes.ficha');
    Route::get('/clientes/{cliente}/ficha/pdf', [\App\Http\Controllers\ClienteController::class, 'fichaPdf'])->name('clientes.ficha.pdf');
    Route::post('/clientes/{cliente}/ficha/send', [\App\Http\Controllers\ClienteController::class, 'sendFichaEmail'])->name('clientes.ficha.send');
    // Gera uma URL assinada temporária para download sem sessão
    Route::get('/clientes/{cliente}/ficha/signed-url', [\App\Http\Controllers\ClienteController::class, 'createSignedUrl'])->name('clientes.ficha.signed.url');
    
    // Download latest automatic reports (diario|semanal|mensal) from storage/app/relatorios
    Route::get('/relatorios/download/{period}', [\App\Http\Controllers\RelatorioController::class, 'download'])
        ->name('relatorios.download');
    Route::put('/clientes/{cliente}', [\App\Http\Controllers\ClienteController::class, 'update'])->name('clientes.update')->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':clientes.edit');
    Route::delete('/clientes/{cliente}', [\App\Http\Controllers\ClienteController::class, 'destroy'])->name('clientes.destroy')->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':clientes.delete');
    Route::get('/planos', fn () => view('planos'))->name('planos');
    Route::get('/alertas', fn () => view('alertas'))->name('alertas');

    // Relatório e cadastro de cobranças
    Route::get('/cobrancas', [\App\Http\Controllers\CobrancaController::class, 'index'])->name('cobrancas.index');
    Route::get('/cobrancas/export', [\App\Http\Controllers\CobrancaController::class, 'exportExcel'])->name('cobrancas.export')->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':cobrancas.export');
    Route::get('/cobrancas/create', [\App\Http\Controllers\CobrancaController::class, 'create'])->name('cobrancas.create')->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':cobrancas.create');
    Route::post('/cobrancas', [\App\Http\Controllers\CobrancaController::class, 'store'])->name('cobrancas.store')->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':cobrancas.create');
    Route::get('/cobrancas/{id}', [\App\Http\Controllers\CobrancaController::class, 'show'])->name('cobrancas.show');
    Route::get('/cobrancas/{id}/comprovante', [\App\Http\Controllers\CobrancaController::class, 'comprovante'])->name('cobrancas.comprovante');
    Route::get('/cobrancas/{id}/edit', [\App\Http\Controllers\CobrancaController::class, 'edit'])->name('cobrancas.edit')->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':cobrancas.edit');
    Route::put('/cobrancas/{id}', [\App\Http\Controllers\CobrancaController::class, 'update'])->name('cobrancas.update')->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':cobrancas.edit');
    Route::delete('/cobrancas/{id}', [\App\Http\Controllers\CobrancaController::class, 'destroy'])->name('cobrancas.destroy')->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':cobrancas.delete');


    // Rotas de equipamentos
    Route::get('/clientes/{cliente}/equipamentos/create', [\App\Http\Controllers\EquipamentoController::class, 'create'])->name('equipamentos.create');
    Route::post('/clientes/{cliente}/equipamentos', [\App\Http\Controllers\EquipamentoController::class, 'store'])->name('equipamentos.store')->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':estoque.create');
    
    // Self-service password change
    Route::get('password/change', [PasswordController::class, 'edit'])->name('password.change');
    Route::post('password/change', [PasswordController::class, 'update'])->name('password.update');

    // User management (admin)
    Route::get('/admin/users/create', [\App\Http\Controllers\UserController::class, 'create'])
        ->name('admin.users.create')
        ->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':users.create');
    Route::post('/admin/users', [\App\Http\Controllers\UserController::class, 'store'])
        ->name('admin.users.store')
        ->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':users.create');
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

// Temporary probe route - remove after testing
Route::get('/_probe/hasroles', function () {
    return response()->json([
        'trait_exists' => trait_exists(\Spatie\Permission\Traits\HasRoles::class),
        'php_sapi' => php_sapi_name(),
        'app_env' => env('APP_ENV'),
    ]);
});

// Rota pública apenas via URL assinada para permitir download temporário sem sessão
Route::get('/clientes/{cliente}/ficha/signed/download', [\App\Http\Controllers\ClienteController::class, 'fichaPdfSigned'])
    ->name('clientes.ficha.signed')
    ->middleware('signed');
