<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;

class StoreProxyController extends Controller
{
    /** Strip UTF-8 BOM bytes emitted by the SG dev server, then json_decode. */
    private function decodeBody($body): ?array
    {
        $raw = ltrim(str_replace("\xEF\xBB\xBF", '', (string) $body));
        return json_decode($raw, true) ?: null;
    }

    protected function getToken(): ?string
    {
        // Cache do token OAuth por 50 minutos (tokens tipicamente duram 1 hora)
        if (Cache::has('sg_oauth_token')) {
            return Cache::get('sg_oauth_token');
        }

        $sg = rtrim(config('services.sg.url', env('SG_URL', '')) , '/');
        $tokenPath = env('SG_OAUTH_TOKEN_PATH', '/api/oauth/token');
        $clientId = env('SG_CLIENT_ID');
        $clientSecret = env('SG_CLIENT_SECRET');

        if (! $sg || ! $clientId || ! $clientSecret) return null;

        $http = new Client(['base_uri' => $sg]);
        try {
            $res = $http->post($tokenPath, [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                ],
                'http_errors' => false,
                'timeout' => 4,
            ]);
            $body = $this->decodeBody($res->getBody());
            $token = $body['access_token'] ?? null;
            if ($token) {
                Cache::put('sg_oauth_token', $token, now()->addMinutes(50));
            }
            return $token;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function plans(Request $request)
    {
        // Comunicação real com o sistema de gestão (API principal em PROJECTO)
        // A API de planos do sistema expõe /api/planos (PlanoController@index).
        // Aqui fazemos apenas um proxy HTTP, sem simulações nem mocks.

        $sg = rtrim(config('services.sg.url', env('SG_URL', 'http://127.0.0.1:8000')) , '/');
        $http = new Client(['base_uri' => $sg]);

        try {
            $res = $http->get('/api/planos', [
                'query' => $request->query(),
                'http_errors' => false,
                'timeout' => 4,
            ]);

            return response($res->getBody(), $res->getStatusCode())
                ->header('Content-Type', $res->getHeaderLine('Content-Type') ?: 'application/json');
        } catch (\Exception $e) {
            // Em caso de falha na comunicação, devolve erro explícito (sem esconder o problema)
            return response()->json([
                'success' => false,
                'error' => 'sg_unreachable',
                'message' => $e->getMessage(),
            ], 502);
        }
    }

    public function planTemplates(Request $request)
    {
        // Proxy para o catálogo público de templates de planos (familiares/empresariais)
        // Cache de 10 minutos para evitar chamadas repetidas ao SG
        $cacheKey = 'sg_plan_templates_' . md5(serialize($request->query()));
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return response($cached, 200)->header('Content-Type', 'application/json');
        }

        $sg = rtrim(config('services.sg.url', env('SG_URL', 'http://127.0.0.1:8000')) , '/');
        $http = new Client(['base_uri' => $sg]);

        try {
            $res = $http->get('/api/plan-templates', [
                'query' => $request->query(),
                'http_errors' => false,
                'timeout' => 4,
            ]);

            $clean = ltrim(str_replace("\xEF\xBB\xBF", '', (string) $res->getBody()));

            if ($res->getStatusCode() === 200) {
                Cache::put($cacheKey, $clean, now()->addMinutes(10));
            }

            return response($clean, $res->getStatusCode())
                ->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'sg_unreachable',
                'message' => $e->getMessage(),
            ], 502);
        }
    }

    public function equipmentCatalog(Request $request)
    {
        // Proxy para o catálogo público de equipamentos à venda (gerido no SG)
        // Cache de 10 minutos para evitar chamadas repetidas ao SG
        $cacheKey = 'sg_equipment_catalog_' . md5(serialize($request->query()));
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return response($cached, 200)->header('Content-Type', 'application/json');
        }

        $sg = rtrim(config('services.sg.url', env('SG_URL', 'http://127.0.0.1:8000')) , '/');
        $http = new Client(['base_uri' => $sg]);

        try {
            $res = $http->get('/api/equipment-catalog', [
                'query' => $request->query(),
                'http_errors' => false,
                'timeout' => 4,
            ]);

            $clean = ltrim(str_replace("\xEF\xBB\xBF", '', (string) $res->getBody()));

            if ($res->getStatusCode() === 200) {
                Cache::put($cacheKey, $clean, now()->addMinutes(10));
            }

            return response($clean, $res->getStatusCode())
                ->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'sg_unreachable',
                'message' => $e->getMessage(),
            ], 502);
        }
    }

    public function sendOrder(Request $request)
    {
        $token = $this->getToken();
        if (! $token) return response()->json(['error' => 'no_token'], 500);

        $payload = $request->all();
        $sg = rtrim(config('services.sg.url', env('SG_URL', '')) , '/');
        $http = new Client(['base_uri' => $sg]);
        $res = $http->post('/api/orders/sync', [
            'headers' => [
                'Authorization' => "Bearer $token",
                'Accept' => 'application/json',
            ],
            'json' => $payload,
            'http_errors' => false,
        ]);
        return response($res->getBody(), $res->getStatusCode())->header('Content-Type', $res->getHeaderLine('Content-Type'));
    }

    /**
     * lookupClienteSG
     * ───────────────
     * Searches the SG's clientes table by phone number.
     * Used as a fallback by the loja checkout form when the client is not found
     * in the local family_plan_requests table (e.g. existing SG clients who never
     * used the loja before).
     *
     * @return array ['found' => bool, 'name' => string, 'email' => string, 'nif' => string]
     */
    public function lookupClienteSG(string $phone): array
    {
        $sg = rtrim(config('services.sg.url', env('SG_URL', 'http://127.0.0.1:8000')), '/');
        $http = new Client(['base_uri' => $sg]);

        $headers = ['Accept' => 'application/json'];
        $apiToken = env('SG_API_TOKEN');
        if ($apiToken) {
            $headers['X-API-TOKEN'] = $apiToken;
        }

        try {
            $res = $http->get('/api/cliente-lookup', [
                'headers'     => $headers,
                'query'       => ['phone' => $phone],
                'http_errors' => false,
                'timeout'     => 6,
            ]);

            if ($res->getStatusCode() === 200) {
                $body = $this->decodeBody($res->getBody());
                return $body ?? ['found' => false];
            }
        } catch (\Exception $e) {
            \Log::debug('lookupClienteSG: SG unreachable', ['error' => $e->getMessage()]);
        }

        return ['found' => false];
    }

    /**
     * syncJanela
     * ──────────
     * Called by FamilyPlanRequestAdminController when admin confirms a payment.
     * POSTs to /api/janela-autovenda on the SG to find/create the client
     * and extend (or initialise) their plan window.
     *
     * @param  array  $data  Keys: nome, email, contato, nif, template_id, loja_request_id
     * @return array  ['success' => bool, 'data' => [...] | null, 'error' => string|null]
     */
    public function syncJanela(array $data): array
    {
        $sg = rtrim(config('services.sg.url', env('SG_URL', 'http://127.0.0.1:8000')), '/');
        $http = new Client(['base_uri' => $sg]);

        $headers = ['Accept' => 'application/json'];
        $apiToken = env('SG_API_TOKEN');
        if ($apiToken) {
            $headers['X-API-TOKEN'] = $apiToken;
        }

        try {
            $res = $http->post('/api/janela-autovenda', [
                'headers'     => $headers,
                'json'        => $data,
                'http_errors' => false,
                'timeout'     => 12,
            ]);

            $body = $this->decodeBody($res->getBody());

            if ($res->getStatusCode() >= 200 && $res->getStatusCode() < 300) {
                return ['success' => true, 'data' => $body, 'error' => null];
            }

            \Log::error('syncJanela: SG returned error', [
                'status' => $res->getStatusCode(),
                'body'   => $body,
                'data'   => $data,
            ]);

            return [
                'success' => false,
                'data'    => $body,
                'error'   => $body['message'] ?? 'SG returned HTTP ' . $res->getStatusCode(),
            ];
        } catch (\Exception $e) {
            \Log::error('syncJanela: could not reach SG', ['error' => $e->getMessage(), 'data' => $data]);
            return ['success' => false, 'data' => null, 'error' => 'sg_unreachable: ' . $e->getMessage()];
        }
    }
}

