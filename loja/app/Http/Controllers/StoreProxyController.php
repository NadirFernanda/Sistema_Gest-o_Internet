<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class StoreProxyController extends Controller
{
    protected function getToken(): ?string
    {
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
                'timeout' => 8,
            ]);
            $body = json_decode((string) $res->getBody(), true);
            return $body['access_token'] ?? null;
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
                'timeout' => 8,
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
        $sg = rtrim(config('services.sg.url', env('SG_URL', 'http://127.0.0.1:8000')) , '/');
        $http = new Client(['base_uri' => $sg]);

        try {
            $res = $http->get('/api/plan-templates', [
                'query' => $request->query(),
                'http_errors' => false,
                'timeout' => 8,
            ]);

            return response($res->getBody(), $res->getStatusCode())
                ->header('Content-Type', $res->getHeaderLine('Content-Type') ?: 'application/json');
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
        $sg = rtrim(config('services.sg.url', env('SG_URL', 'http://127.0.0.1:8000')) , '/');
        $http = new Client(['base_uri' => $sg]);

        try {
            $res = $http->get('/api/equipment-catalog', [
                'query' => $request->query(),
                'http_errors' => false,
                'timeout' => 8,
            ]);

            return response($res->getBody(), $res->getStatusCode())
                ->header('Content-Type', $res->getHeaderLine('Content-Type') ?: 'application/json');
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

            $body = json_decode((string) $res->getBody(), true);

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
