<?php

namespace App\Http\Controllers;

use App\Models\AutovendaOrder;
use App\Models\SiteStat;
use App\Models\VoucherPlan;
use App\Services\AutovendaOrderService;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Exception;

class StorefrontController extends Controller
{
    public function index()
    {
        // Planos individuais carregados da base de dados (tabela voucher_plans)
        try {
            $individualPlans = VoucherPlan::active()->get();
        } catch (\Throwable $e) {
            $individualPlans = collect();
        }

        // Estatísticas dinâmicas para a barra de números no topo
        // Protegido contra tabela não existente (ex: primeiro deploy antes de migrate)
        try {
            $siteStats = \App\Models\SiteStat::orderBy('ordem')->get();
        } catch (\Throwable $e) {
            $siteStats = collect();
        }

        // Estatísticas live da loja (protegidas contra tabela inexistente)
        try {
            $vouchersSoldToday = AutovendaOrder::whereNotNull('delivered_at')
                ->whereDate('delivered_at', today())
                ->count();
            $totalDelivered = AutovendaOrder::whereNotNull('delivered_at')->count();
        } catch (\Throwable $e) {
            $vouchersSoldToday = null;
            $totalDelivered    = null;
        }

        return view('store.index', [
            'individualPlans'   => $individualPlans,
            'siteStats'         => $siteStats,
            'activeClientCount' => $this->fetchActiveClientCount(),
            'vouchersSoldToday' => $vouchersSoldToday,
            'totalDelivered'    => $totalDelivered,
        ]);
    }

    /** Endpoint JSON para polling live das estatísticas da loja. */
    public function liveStats(): JsonResponse
    {
        try {
            $vouchersSoldToday = AutovendaOrder::whereNotNull('delivered_at')
                ->whereDate('delivered_at', today())
                ->count();
            $totalDelivered = AutovendaOrder::whereNotNull('delivered_at')->count();
        } catch (\Throwable $e) {
            $vouchersSoldToday = null;
            $totalDelivered    = null;
        }

        return response()->json([
            'active_clients'    => $this->fetchActiveClientCount(),
            'vouchers_today'    => $vouchersSoldToday,
            'total_delivered'   => $totalDelivered,
        ]);
    }

    /**
     * Consulta o SG para obter o número actual de clientes activos.
     * Resultado em cache por 5 minutos para reflectir novas activações rapidamente.
     * Devolve null se o SG estiver inacessível (fallback silencioso na view).
     */
    private function fetchActiveClientCount(): ?int
    {
        if (Cache::has('sg_active_clients_count')) {
            return Cache::get('sg_active_clients_count');
        }

        $sg      = rtrim(config('services.sg.url', env('SG_URL', 'http://127.0.0.1:8000')), '/');
        $apiPath = config('services.sg.active_clients_path', '/api/stats/active-clients');
        $headers = ['Accept' => 'application/json'];
        $apiToken = config('services.sg.api_token');
        if ($apiToken) {
            $headers['X-API-TOKEN'] = $apiToken;
        }

        try {
            $res = (new Client(['timeout' => 4]))->get($sg . $apiPath, [
                'headers'     => $headers,
                'http_errors' => false,
            ]);
            if ($res->getStatusCode() === 200) {
                // Alguns ficheiros PHP no SG são gravados com BOM (EF BB BF),
                // que é emitido antes do JSON e faz json_decode retornar null.
                $raw   = ltrim(str_replace("\xEF\xBB\xBF", '', (string) $res->getBody()));
                $body  = json_decode($raw, true);
                // Suporta múltiplos formatos de resposta do SG
                $count = $body['active_clients']
                      ?? $body['count']
                      ?? $body['total']
                      ?? ($body['data']['count']          ?? null)
                      ?? ($body['data']['active_clients'] ?? null);
                if (is_numeric($count) && $count >= 0) {
                    $count = (int) $count;
                    Cache::put('sg_active_clients_count', $count, now()->addMinutes(5));
                    return $count;
                }
            }
        } catch (\Throwable $e) {
            // SG inacessível — a view usa o fallback silencioso
        }

        return null;
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

        $plan = $planKey ? VoucherPlan::where('slug', $planKey)->where('active', true)->first() : null;

        return view('store.checkout', [
            'plan' => $plan,
        ]);
    }
 
    public function processCheckout(Request $request, AutovendaOrderService $orderService)
    {
        $validated = $request->validate([
            'plan_id'        => 'required|string',
            'payment_method' => 'required|string|in:' . AutovendaOrder::METHOD_GPO,
            'customer_name'  => 'nullable|string|max:100',
            'customer_email' => 'nullable|email|max:150',
            'customer_phone' => ['nullable', 'regex:/^(244)?9[0-9]{8}$/'],
            'create_account' => 'nullable|boolean',
        ], [
            'customer_email.email' => 'Introduza um e-mail válido.',
            'customer_phone.regex' => 'Número inválido. Formato: 9XXXXXXXX ou 2449XXXXXXXX.',
        ]);

        $plan = VoucherPlan::where('slug', $validated['plan_id'])->where('active', true)->first();

        if (!$plan) {
            return redirect()
                ->route('store.checkout')
                ->withErrors(['plan_id' => 'Plano inválido. Volte à página inicial e escolha novamente.']);
        }

        $phone = $validated['customer_phone'] ?? null;
        if ($phone !== null && !str_starts_with($phone, '244')) {
            $phone = '244' . $phone;
        }

        $order = AutovendaOrder::create([
            'plan_id'               => $plan->slug,
            'plan_name'             => $plan->name,
            'plan_speed'            => $plan->speed_label,
            'plan_duration_minutes' => $plan->validity_minutes,
            'quantity'              => 1,
            'amount_aoa'            => $plan->price_public_aoa,
            'currency'              => 'AOA',
            'customer_name'         => $validated['customer_name'],
            'customer_email'        => $validated['customer_email'],
            'customer_phone'        => $phone,
            'customer_nif'          => null,
            'status'                => AutovendaOrder::STATUS_AWAITING_PAYMENT,
            'payment_method'        => $validated['payment_method'],
        ]);

        // Se o cliente pediu conta e forneceu email, guardar para auto-login após pagamento
        if (!empty($validated['create_account']) && !empty($validated['customer_email'])) {
            $request->session()->put('checkout_create_account_email', strtolower(trim($validated['customer_email'])));
        }

        return redirect()->route('gpo.show', $order);
    }

    public function checkoutConfirm(\App\Models\AutovendaOrder $order, \Illuminate\Http\Request $request)
    {
        // Apenas URLs assinadas são válidas — impede enumeração de IDs (IDOR)
        if (! $request->hasValidSignature()) {
            abort(403, 'Ligação de confirmação inválida. Por favor volte à página de pagamento.');
        }

        if (! $order->isPaid()) {
            return redirect()->route('gpo.show', $order);
        }

        $plan = \App\Models\VoucherPlan::where('slug', $order->plan_id)->first();

        // Auto-login se o cliente pediu conta durante o checkout
        $accountCreated = false;
        $pendingEmail   = $request->session()->pull('checkout_create_account_email');
        if ($pendingEmail && $order->customer_email
            && strtolower($order->customer_email) === $pendingEmail
        ) {
            $request->session()->put('customer_email', $pendingEmail);
            $accountCreated = true;
        }

        return view('store.checkout-confirmation', compact('plan', 'order', 'accountCreated'));
    }
}
