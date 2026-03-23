<?php

namespace App\Http\Controllers;

use App\Models\AutovendaOrder;
use App\Models\SiteStat;
use App\Services\AutovendaOrderService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Exception;

class StorefrontController extends Controller
{
    public function index()
    {
        // Planos individuais são sempre carregados da configuração local
        $individualPlans = config('store_plans.individual', []);

        // Estatísticas dinâmicas para a barra de números no topo
        // Protegido contra tabela não existente (ex: primeiro deploy antes de migrate)
        try {
            $siteStats = \App\Models\SiteStat::orderBy('ordem')->get();
        } catch (\Throwable $e) {
            $siteStats = collect();
        }

        // Planos familiares/empresariais e contagem de clientes activos carregados
        // de forma assíncrona pelo JS — não bloqueiam o render da página.
        return view('store.index', [
            'individualPlans'   => $individualPlans,
            'siteStats'         => $siteStats,
            'activeClientCount' => null,
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
        $apiToken = env('SG_API_TOKEN');
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

        $individualPlans = collect(config('store_plans.individual', []));
        $plan = $planKey ? $individualPlans->firstWhere('id', $planKey) : null;

        return view('store.checkout', [
            'plan' => $plan,
        ]);
    }
 
    /**
     * AUTOVENDA — PLANOS INDIVIDUAIS (Dia, Semana, Mês)
     * ─────────────────────────────────────────────────
     * Esta acção é EXCLUSIVA para planos individuais vendidos directamente
     * pela loja, sem qualquer integração com o Sistema de Gestão (SG).
     *
     * Regras de negócio (doc: autovenda.md §3.1-a):
     *  - Os planos estão definidos localmente em config/store_plans.php — o SG
     *    não é consultado em nenhum passo deste fluxo.
     *  - Não são recolhidos dados pessoais do cliente (sem nome, e-mail, telefone).
     *  - O cliente escolhe apenas o plano e o método de pagamento.
     *  - Após confirmação do pagamento, o código WiFi é entregue directamente
     *    no ecrã de confirmação.
     *
     * NÃO CONFUNDIR com o checkout de planos familiares/empresariais:
     *  - Esses planos são carregados do SG via API (/sg/plan-templates).
     *  - Esse fluxo requer identificação do cliente (nome, e-mail, telefone).
     *  - Aqui: nada disso. Só plano + método de pagamento.
     */
    public function processCheckout(Request $request, AutovendaOrderService $orderService)
    {
        // Apenas plan_id e payment_method — sem dados pessoais para planos individuais.
        $validated = $request->validate([
            'plan_id'        => 'required|string',
            'payment_method' => 'required|string|in:' . AutovendaOrder::METHOD_MULTICAIXA . ',' . AutovendaOrder::METHOD_PAYPAL,
        ]);

        // Planos individuais são lidos exclusivamente da configuração local.
        // Os planos familiares/empresariais (SG) têm um fluxo completamente diferente.
        $individualPlans = collect(config('store_plans.individual', []));
        $plan = $individualPlans->firstWhere('id', $validated['plan_id']);

        if (!$plan) {
            return redirect()
                ->route('store.checkout')
                ->withErrors(['plan_id' => 'Plano inválido. Volte à página inicial e escolha novamente.']);
        }

        // SEGURANÇA: em produção, a simulação de pagamento está BLOQUEADA.
        // O gateway real ainda não está integrado — mostrar mensagem ao cliente.
        if (app()->isProduction()) {
            return redirect()
                ->route('store.checkout', ['plan' => $plan['id']])
                ->withErrors(['gateway' => 'O pagamento online para planos individuais está temporariamente indisponível. Dirija-se a um ponto de venda ou contacte o suporte (+244 949 364 505).']);
        }

        // Cria a ordem de autovenda em estado "awaiting_payment".
        // Sem dados de cliente — os planos individuais não requerem identificação.
        $order = AutovendaOrder::create([
            'plan_id'               => $plan['id'],
            'plan_name'             => $plan['name'],
            'plan_speed'            => $plan['speed'] ?? null,
            'plan_duration_minutes' => $plan['duration_minutes'] ?? null,
            'quantity'              => 1,
            'amount_aoa'            => $plan['price_kwanza'],
            'currency'              => 'AOA',
            'customer_name'         => null,
            'customer_email'        => null,
            'customer_phone'        => null,
            'customer_nif'          => null,
            'status'                => AutovendaOrder::STATUS_AWAITING_PAYMENT,
            'payment_method'        => $validated['payment_method'],
        ]);

        // PROTÓTIPO — gatilho de pagamento simulado.
        // Em produção este passo é substituído pelo redirect ao gateway (Multicaixa
        // Express ou PayPal). O código WiFi só é entregue após o callback do gateway
        // chamar PaymentCallbackController::simulateSuccess() — nunca aqui directamente.
        try {
            $orderService->confirmPaymentAndDeliver($order, 'SIMULATED');
        } catch (\Throwable $e) {
            // Sem stock de códigos WiFi disponíveis.
            return redirect()
                ->route('store.checkout', ['plan' => $plan['id']])
                ->withErrors(['stock' => 'Sem códigos WiFi disponíveis de momento. Tente novamente mais tarde ou contacte o suporte.']);
        }

        return view('store.checkout-confirmation', [
            'plan'  => $plan,
            'order' => $order,
        ]);
    }
}
