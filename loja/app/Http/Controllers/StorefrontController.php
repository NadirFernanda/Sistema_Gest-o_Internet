<?php

namespace App\Http\Controllers;

use App\Models\AutovendaOrder;
use App\Services\AutovendaOrderService;
use Illuminate\Http\Request;
use Exception;

class StorefrontController extends Controller
{
    public function index()
    {
        // Planos individuais são sempre carregados da configuração local
        $individualPlans = config('store_plans.individual', []);

            // Planos familiares/empresariais carregados de forma assíncrona pelo JS
        // (evita bloquear o render da página enquanto espera a resposta do SG)
        return view('store.index', [
            'individualPlans' => $individualPlans,
        ]);
    }

    public function show($id)
    {
        // minimal detail page - for demo just show id and allow checkout
        return view('store.show', ['id' => $id]);
    }

    public function checkout(Request $request, $planId = null)
    {
        // Permite receber o identificador do plano tanto pela URL quanto por query string
        $planKey = $planId ?: $request->query('plan');

        $individualPlans = collect(config('store_plans.individual', []));
        $plan = $planKey ? $individualPlans->firstWhere('id', $planKey) : null;

        return view('store.checkout', [
            'plan' => $plan,
        ]);
    }
 
    public function processCheckout(Request $request, AutovendaOrderService $orderService)
    {
        $validated = $request->validate([
            'plan_id' => 'required|string',
            'nome' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'telefone' => 'nullable|string|max:50',
            'nif' => 'nullable|string|max:50',
            'payment_method' => 'required|string|in:'.AutovendaOrder::METHOD_MULTICAIXA.','.AutovendaOrder::METHOD_PAYPAL,
        ]);

        $individualPlans = collect(config('store_plans.individual', []));
        $plan = $individualPlans->firstWhere('id', $validated['plan_id']);

        if (!$plan) {
            return redirect()
                ->route('store.checkout')
                ->withErrors(['plan_id' => 'Plano inválido. Volte à página inicial e escolha novamente.']);
        }

        $customer = [
            'nome' => $validated['nome'] ?? '',
            'email' => $validated['email'] ?? '',
            'telefone' => $validated['telefone'] ?? '',
            'nif' => $validated['nif'] ?? null,
        ];

        // Cria uma ordem básica de autovenda em estado "pending".
        $order = AutovendaOrder::create([
            'plan_id' => $plan['id'],
            'plan_name' => $plan['name'],
            'plan_speed' => $plan['speed'] ?? null,
            'plan_duration_minutes' => $plan['duration_minutes'] ?? null,
            'quantity' => 1,
            'amount_aoa' => $plan['price_kwanza'],
            'currency' => 'AOA',
            'customer_name' => $customer['nome'],
            'customer_email' => $customer['email'],
            'customer_phone' => $customer['telefone'],
            'customer_nif' => $customer['nif'],
            'status' => AutovendaOrder::STATUS_AWAITING_PAYMENT,
            'payment_method' => $validated['payment_method'],
        ]);

        // Modo simulado: confirmamos o pagamento imediatamente e entregamos o código.
        // Em produção, isto será feito pelo callback/retorno do gateway.
        $orderService->confirmPaymentAndDeliver($order, 'SIMULATED');

        // Nesta fase ainda não chamamos o gateway nem geramos código WiFi.
        // A view de confirmação serve para validar o fluxo fim-a-fim do checkout rápido.
        return view('store.checkout-confirmation', [
            'plan' => $plan,
            'customer' => $customer,
            'order' => $order,
        ]);
    }
}
