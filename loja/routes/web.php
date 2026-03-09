<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AutovendaOrderAdminController;
use App\Http\Controllers\Admin\ResellerAdminController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\Admin\EquipmentOrderAdminController;
use App\Http\Controllers\Admin\SiteStatsAdminController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\FamilyPlanPaymentController;
use App\Http\Controllers\FamilyPlanRequestController;
use App\Http\Controllers\ResellerPanelController;

Route::get('/', function () {
    return app(\App\Http\Controllers\StorefrontController::class)->index();
});

// Demo proxy endpoints to interact with SG for the loja prototype
Route::get('/sg/plans', [\App\Http\Controllers\StoreProxyController::class, 'plans']);
Route::get('/sg/plan-templates', [\App\Http\Controllers\StoreProxyController::class, 'planTemplates']);
Route::get('/sg/equipment-catalog', [\App\Http\Controllers\StoreProxyController::class, 'equipmentCatalog']);
Route::post('/sg/orders/sync', [\App\Http\Controllers\StoreProxyController::class, 'sendOrder']);

// Storefront routes
Route::get('/plan/{id}', [\App\Http\Controllers\StorefrontController::class, 'show']);
// Checkout rápido pode receber o plano pela URL ou por query string ?plan=
Route::get('/checkout/{plan?}', [\App\Http\Controllers\StorefrontController::class, 'checkout'])->name('store.checkout');
Route::post('/checkout', [\App\Http\Controllers\StorefrontController::class, 'processCheckout'])->name('store.checkout.process');

// Planos Familiares & Empresariais — checkout com identificação do cliente
// (NÃO confundir com individual plans — ver StorefrontController / autovenda_orders)
Route::get('/solicitar-plano', [FamilyPlanRequestController::class, 'show'])->name('family.request.show');
Route::post('/solicitar-plano', [FamilyPlanRequestController::class, 'store'])->name('family.request.store');
Route::get('/checkout/lookup', [FamilyPlanRequestController::class, 'lookup'])->name('family.request.lookup');

// Pagamento dos planos familiares/empresariais
// POST /payment/familia/webhook é CSRF-exempt (ver bootstrap/app.php)
Route::get('/pagar-plano/{id}', [FamilyPlanPaymentController::class, 'show'])->name('family.payment.show');
Route::post('/payment/familia/webhook', [FamilyPlanPaymentController::class, 'webhook'])->name('family.payment.webhook');
Route::get('/payment/familia/simular/{id}', [FamilyPlanPaymentController::class, 'simulateSuccess'])->name('family.payment.simulate');

// Módulo Revendedor - página de adesão
Route::get('/quero-ser-revendedor', [\App\Http\Controllers\ResellerController::class, 'showForm'])->name('reseller.apply');
Route::post('/quero-ser-revendedor', [\App\Http\Controllers\ResellerController::class, 'submit'])->name('reseller.apply.submit');
Route::get('/quero-ser-revendedor/obrigado', [\App\Http\Controllers\ResellerController::class, 'thankYou'])->name('reseller.apply.thankyou');

// Área do Revendedor (pós-aprovação)
Route::get('/painel-revendedor', [ResellerPanelController::class, 'index'])->name('reseller.panel');
Route::post('/painel-revendedor/login', [ResellerPanelController::class, 'login'])->name('reseller.panel.login');
Route::post('/painel-revendedor/logout', [ResellerPanelController::class, 'logout'])->name('reseller.panel.logout');
Route::post('/painel-revendedor/compras', [ResellerPanelController::class, 'storePurchase'])->name('reseller.panel.purchase');
Route::get('/painel-revendedor/compras/{purchase}/csv', [ResellerPanelController::class, 'downloadCsv'])->name('reseller.panel.purchase.csv');

// Protótipo de callback de pagamento (para testes sem gateway real)
Route::get('/autovenda/callback/simulate/{order}', [\App\Http\Controllers\PaymentCallbackController::class, 'simulateSuccess'])
    ->name('autovenda.callback.simulate');

// Static page: Quem Somos
Route::view('/quem-somos', 'pages.about');
// Static page: Como Comprar
Route::view('/como-comprar', 'pages.how-to-buy');

// ── Loja de Equipamentos / Produtos ──────────────────────────────────────────
Route::get('/equipamentos', [EquipmentController::class, 'index'])->name('equipment.index');
Route::get('/carrinho', [EquipmentController::class, 'cart'])->name('equipment.cart');
Route::post('/carrinho/adicionar', [EquipmentController::class, 'addToCart'])->name('equipment.cart.add');
Route::post('/carrinho/remover', [EquipmentController::class, 'removeFromCart'])->name('equipment.cart.remove');
Route::post('/carrinho/limpar', [EquipmentController::class, 'clearCart'])->name('equipment.cart.clear');
Route::get('/equipamentos/checkout', [EquipmentController::class, 'checkout'])->name('equipment.checkout');
Route::post('/equipamentos/checkout', [EquipmentController::class, 'processCheckout'])->name('equipment.checkout.process');
Route::get('/equipamentos/confirmacao/{id}', [EquipmentController::class, 'confirmation'])->name('equipment.confirmation');
// Must be last to avoid shadowing named routes above
Route::get('/equipamentos/{slug}', [EquipmentController::class, 'show'])->name('equipment.show');
// Área do Cliente (histórico de compras via e-mail)
Route::get('/minha-conta', [CustomerAccountController::class, 'index'])->name('account.index');
Route::post('/minha-conta/login', [CustomerAccountController::class, 'login'])->name('account.login');
Route::post('/minha-conta/logout', [CustomerAccountController::class, 'logout'])->name('account.logout');
Route::post('/minha-conta/orders/{order}/resend-email', [CustomerAccountController::class, 'resendEmail'])->name('account.orders.resend-email');
Route::get('/minha-conta/orders/{order}/whatsapp', [CustomerAccountController::class, 'openWhatsapp'])->name('account.orders.whatsapp');

// Painel Administrativo da Loja (autovenda + revenda + equipamentos)
// Protegido por middleware 'sg-admin', que valida token partilhado com o SG.
Route::prefix('admin')->middleware('sg-admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/recargas', [AutovendaOrderAdminController::class, 'index'])->name('admin.autovenda.index');
    Route::get('/relatorios', [AdminDashboardController::class, 'reports'])->name('admin.reports');
    Route::get('/revendedores', [ResellerAdminController::class, 'index'])->name('admin.resellers.index');

    // Gestão de produtos (equipamentos)
    Route::get('/equipamentos', [ProductAdminController::class, 'index'])->name('admin.equipment.products.index');
    Route::get('/equipamentos/criar', [ProductAdminController::class, 'create'])->name('admin.equipment.products.create');
    Route::post('/equipamentos', [ProductAdminController::class, 'store'])->name('admin.equipment.products.store');
    Route::get('/equipamentos/{id}/editar', [ProductAdminController::class, 'edit'])->name('admin.equipment.products.edit');
    Route::put('/equipamentos/{id}', [ProductAdminController::class, 'update'])->name('admin.equipment.products.update');
    Route::delete('/equipamentos/{id}', [ProductAdminController::class, 'destroy'])->name('admin.equipment.products.destroy');

    // Encomendas de equipamentos
    Route::get('/encomendas-equipamentos', [EquipmentOrderAdminController::class, 'index'])->name('admin.equipment.orders.index');
    Route::get('/encomendas-equipamentos/{id}', [EquipmentOrderAdminController::class, 'show'])->name('admin.equipment.orders.show');
    Route::patch('/encomendas-equipamentos/{id}/estado', [EquipmentOrderAdminController::class, 'updateStatus'])->name('admin.equipment.orders.status');

    // Gestão de stock de códigos WiFi (fornecidos pela operadora, importados pelo admin)
    Route::get('/wifi-codes', [\App\Http\Controllers\Admin\WifiCodeAdminController::class, 'index'])->name('admin.wifi_codes.index');
    Route::post('/wifi-codes/import-paste', [\App\Http\Controllers\Admin\WifiCodeAdminController::class, 'importPaste'])->name('admin.wifi_codes.import_paste');
    Route::post('/wifi-codes/import-csv', [\App\Http\Controllers\Admin\WifiCodeAdminController::class, 'importCsv'])->name('admin.wifi_codes.import_csv');
    Route::delete('/wifi-codes/{wifiCode}', [\App\Http\Controllers\Admin\WifiCodeAdminController::class, 'destroy'])->name('admin.wifi_codes.destroy');

    // Estatísticas da página inicial
    Route::get('/estatisticas', [SiteStatsAdminController::class, 'index'])->name('admin.site_stats.index');
    Route::put('/estatisticas/{stat}', [SiteStatsAdminController::class, 'update'])->name('admin.site_stats.update');

    // Pedidos de planos familiares / empresariais — confirmação activa janela no SG
    Route::get('/pedidos-planos-familiares', [\App\Http\Controllers\Admin\FamilyPlanRequestAdminController::class, 'index'])->name('admin.family_requests.index');
    Route::post('/pedidos-planos-familiares/{familyPlanRequest}/confirmar', [\App\Http\Controllers\Admin\FamilyPlanRequestAdminController::class, 'confirmar'])->name('admin.family_requests.confirmar');
    Route::post('/pedidos-planos-familiares/{familyPlanRequest}/cancelar', [\App\Http\Controllers\Admin\FamilyPlanRequestAdminController::class, 'cancelar'])->name('admin.family_requests.cancelar');
});
