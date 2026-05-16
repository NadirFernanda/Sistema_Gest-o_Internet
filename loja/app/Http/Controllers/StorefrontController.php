<?php

namespace App\Http\Controllers;

use App\Models\AutovendaOrder;
use App\Models\SiteStat;
use App\Models\VoucherPlan;
use App\Services\AutovendaOrderService;
use GuzzleHttp\Client;
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

        // Planos familiares/empresariais e contagem de clientes activos carregados
        // do SG via API (cache de 5 minutos). Null se o SG estiver inacessível.
        return view('store.index', [
            'individualPlans'   => $individualPlans,
            'siteStats'         => $siteStats,
            'activeClientCount' => $this->fetchActiveClientCount(),
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
            'customer_name'  => 'required|string|max:100',
            'customer_email' => 'required|email|max:150',
            'customer_phone' => ['required', 'regex:/^(244)?9[0-9]{8}$/'],
        ], [
            'customer_name.required'  => 'O nome é obrigatório.',
            'customer_email.required' => 'O e-mail é obrigatório.',
            'customer_email.email'    => 'Introduza um e-mail válido.',
            'customer_phone.required' => 'O número de telemóvel é obrigatório.',
            'customer_phone.regex'    => 'Número inválido. Formato: 9XXXXXXXX ou 2449XXXXXXXX.',
        ]);

        $plan = VoucherPlan::where('slug', $validated['plan_id'])->where('active', true)->first();

        if (!$plan) {
            return redirect()
                ->route('store.checkout')
                ->withErrors(['plan_id' => 'Plano inválido. Volte à página inicial e escolha novamente.']);
        }

        $phone = $validated['customer_phone'];
        if (!str_starts_with($phone, '244')) {
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

        return redirect()->route('gpo.show', $order);
    }

    public function checkoutConfirm(\App\Models\AutovendaOrder $order)
    {
        if (! $order->isPaid()) {
            return redirect()->route('gpo.show', $order);
        }

        $plan = \App\Models\VoucherPlan::where('slug', $order->plan_id)->first();

        return view('store.checkout-confirmation', compact('plan', 'order'));
    }
}
