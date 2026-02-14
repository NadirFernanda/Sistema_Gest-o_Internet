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

// Temporary hotfix: ensure a named route 'planos' exists so cached views
// calling route('planos') do not throw a RouteNotFoundException.
// This will be a no-op when the named route is already registered.
if (! app()->router->has('planos')) {
    Route::get('/planos', [\App\Http\Controllers\PlanoController::class, 'webIndex'])->name('planos');
}

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/clientes', [\App\Http\Controllers\ClienteController::class, 'index'])->name('clientes');
    Route::get('/clientes/create', [\App\Http\Controllers\ClienteController::class, 'create'])
        ->name('clientes.create')
        ->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':clientes.create');
    Route::post('/clientes', [\App\Http\Controllers\ClienteController::class, 'store'])->name('clientes.store')->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':clientes.create');
    Route::get('/clientes/{cliente}', [\App\Http\Controllers\ClienteController::class, 'show'])->name('clientes.show')->whereNumber('cliente');
    Route::get('/clientes/{cliente}/ficha', [\App\Http\Controllers\ClienteController::class, 'ficha'])->name('clientes.ficha');
    Route::get('/clientes/{cliente}/ficha/pdf', [\App\Http\Controllers\ClienteController::class, 'fichaPdf'])->name('clientes.ficha.pdf');
    Route::get('/clientes/{cliente}/ficha/download-send', [\App\Http\Controllers\ClienteController::class, 'fichaPdfAndSend'])->name('clientes.ficha.download_send');
    Route::post('/clientes/{cliente}/ficha/send', [\App\Http\Controllers\ClienteController::class, 'sendFichaEmail'])->name('clientes.ficha.send');
    // Ação combinada: envia a ficha por e-mail e retorna o PDF para download em um único clique
    Route::get('/clientes/{cliente}/ficha/download-send', [\App\Http\Controllers\ClienteController::class, 'fichaPdfAndSend'])->name('clientes.ficha.download_send');
    // Gera uma URL assinada temporária para download sem sessão
    Route::get('/clientes/{cliente}/ficha/signed-url', [\App\Http\Controllers\ClienteController::class, 'createSignedUrl'])->name('clientes.ficha.signed.url');
    
    // Página geral de relatórios multi-aba
    Route::get('/relatorios-gerais', [\App\Http\Controllers\RelatorioController::class, 'geral'])
        ->name('relatorios.gerais');
    // Download dos relatórios automáticos
    Route::get('/relatorios-gerais/download/{period}', [\App\Http\Controllers\RelatorioController::class, 'download'])
        ->name('relatorios.gerais.download');
    Route::put('/clientes/{cliente}', [\App\Http\Controllers\ClienteController::class, 'update'])->name('clientes.update')->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':clientes.edit');
    Route::delete('/clientes/{cliente}', [\App\Http\Controllers\ClienteController::class, 'destroy'])->name('clientes.destroy')->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':clientes.delete');
    Route::get('/planos', [\App\Http\Controllers\PlanoController::class, 'webIndex'])->name('planos');
    // Backwards-compatible alias: some views/compiled templates reference "planos.index"
    Route::get('/planos', [\App\Http\Controllers\PlanoController::class, 'webIndex'])->name('planos.index');
    Route::post('/planos', [\App\Http\Controllers\PlanoController::class, 'storeWeb'])
        ->name('planos.store')
        ->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':planos.create');
    // Show the create form for planos on a separate page
    Route::get('/planos/create', [\App\Http\Controllers\PlanoController::class, 'createWeb'])->name('planos.create');
    // Web routes for individual planos (prevent 404s from card actions)
    Route::get('/planos/{plano}', [\App\Http\Controllers\PlanoController::class, 'webShow'])->name('planos.show')->whereNumber('plano');
    Route::get('/planos/{plano}/edit', [\App\Http\Controllers\PlanoController::class, 'editWeb'])->name('planos.edit')->whereNumber('plano');
    Route::put('/planos/{plano}', [\App\Http\Controllers\PlanoController::class, 'update'])->name('planos.update')->whereNumber('plano')->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':planos.edit');
    Route::delete('/planos/{plano}', [\App\Http\Controllers\PlanoController::class, 'destroyWeb'])->name('planos.destroy')->whereNumber('plano')->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':planos.delete');
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
    
    // Plan templates (catalog of reusable plans)
    Route::get('/plan-templates', [\App\Http\Controllers\PlanTemplateController::class, 'index'])->name('plan-templates.index');
    Route::get('/plan-templates/create', [\App\Http\Controllers\PlanTemplateController::class, 'create'])->name('plan-templates.create');
    Route::post('/plan-templates', [\App\Http\Controllers\PlanTemplateController::class, 'store'])->name('plan-templates.store');
    Route::get('/plan-templates/{plan_template}/edit', [\App\Http\Controllers\PlanTemplateController::class, 'edit'])->name('plan-templates.edit');
    Route::put('/plan-templates/{plan_template}', [\App\Http\Controllers\PlanTemplateController::class, 'update'])->name('plan-templates.update');
    Route::delete('/plan-templates/{plan_template}', [\App\Http\Controllers\PlanTemplateController::class, 'destroy'])->name('plan-templates.destroy');
    // JSON endpoint
    Route::get('/plan-templates/{plan_template}/json', [\App\Http\Controllers\PlanTemplateController::class, 'json'])->name('plan-templates.json');
    // Backwards-compatible endpoints: some older assets request these paths
    Route::get('/plan-templates/list.json', [\App\Http\Controllers\PlanTemplateController::class, 'listJson']);
    Route::get('/plan-templates-list-json', [\App\Http\Controllers\PlanTemplateController::class, 'listJson'])->name('plan-templates.list.json');

    // Auditoria de exclusões (admin)
    Route::get('/audits', [\App\Http\Controllers\AuditController::class, 'index'])
        ->name('audits.index')
        ->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':audits.view');
});
// Rotas de Estoque de Equipamentos
Route::middleware('auth')->group(function () {
    Route::get('/estoque-equipamentos', [\App\Http\Controllers\EstoqueEquipamentoController::class, 'index'])->name('estoque_equipamentos.index');
    Route::get('/estoque-equipamentos/create', [\App\Http\Controllers\EstoqueEquipamentoController::class, 'create'])->name('estoque_equipamentos.create');
    Route::post('/estoque-equipamentos', [\App\Http\Controllers\EstoqueEquipamentoController::class, 'store'])->name('estoque_equipamentos.store');

    Route::get('/estoque-equipamentos/{equipamento}/edit', [\App\Http\Controllers\EstoqueEquipamentoController::class, 'edit'])->name('estoque_equipamentos.edit');
    Route::put('/estoque-equipamentos/{equipamento}', [\App\Http\Controllers\EstoqueEquipamentoController::class, 'update'])->name('estoque_equipamentos.update');
    Route::delete('/estoque-equipamentos/{equipamento}', [\App\Http\Controllers\EstoqueEquipamentoController::class, 'destroy'])->name('estoque_equipamentos.destroy');
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

// TEMPORARY public download for testing (no auth, remove after testing)
Route::get('/clientes/{cliente}/ficha/public-download', [\App\Http\Controllers\ClienteController::class, 'fichaPdfPublic'])
    ->name('clientes.ficha.public');
