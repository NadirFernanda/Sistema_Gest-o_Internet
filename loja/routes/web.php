<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AutovendaOrderAdminController;
use App\Http\Controllers\Admin\ResellerAdminController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\ResellerPanelController;

Route::get('/', function () {
    return app(\App\Http\Controllers\StorefrontController::class)->index();
});

// Demo proxy endpoints to interact with SG for the loja prototype
Route::get('/sg/plans', [\App\Http\Controllers\StoreProxyController::class, 'plans']);
Route::post('/sg/orders/sync', [\App\Http\Controllers\StoreProxyController::class, 'sendOrder']);

// Storefront routes
Route::get('/plan/{id}', [\App\Http\Controllers\StorefrontController::class, 'show']);
// Checkout rápido pode receber o plano pela URL ou por query string ?plan=
Route::get('/checkout/{plan?}', [\App\Http\Controllers\StorefrontController::class, 'checkout'])->name('store.checkout');
Route::post('/checkout', [\App\Http\Controllers\StorefrontController::class, 'processCheckout'])->name('store.checkout.process');

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
// Área do Cliente (histórico de compras via e-mail)
Route::get('/minha-conta', [CustomerAccountController::class, 'index'])->name('account.index');
Route::post('/minha-conta/login', [CustomerAccountController::class, 'login'])->name('account.login');
Route::post('/minha-conta/logout', [CustomerAccountController::class, 'logout'])->name('account.logout');
Route::post('/minha-conta/orders/{order}/resend-email', [CustomerAccountController::class, 'resendEmail'])->name('account.orders.resend-email');
Route::get('/minha-conta/orders/{order}/whatsapp', [CustomerAccountController::class, 'openWhatsapp'])->name('account.orders.whatsapp');

// Painel Administrativo da Loja (autovenda + revenda)
// Protegido por middleware 'sg-admin', que valida token partilhado com o SG.
Route::prefix('admin')->middleware('sg-admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/recargas', [AutovendaOrderAdminController::class, 'index'])->name('admin.autovenda.index');
    Route::get('/relatorios', [AdminDashboardController::class, 'reports'])->name('admin.reports');
    Route::get('/revendedores', [ResellerAdminController::class, 'index'])->name('admin.resellers.index');
});
