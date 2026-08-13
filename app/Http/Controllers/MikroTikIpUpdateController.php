<?php

namespace App\Http\Controllers;

use App\Models\MikroTikSite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MikroTikIpUpdateController extends Controller
{
    /**
     * Chamado pelo script RouterOS quando o IP WAN muda.
     *
     * GET /api/mikrotik-ip-update?nome=SiteA&token=SECRET&ip=1.2.3.4
     *
     * O parâmetro "nome" identifica o site pelo campo "nome" na tabela mikrotik_sites.
     * O token deve ser igual a MIKROTIK_IP_UPDATE_SECRET no .env do SG.
     */
    public function update(Request $request)
    {
        $secret = config('services.mikrotik_ip_update.secret', '');

        if (empty($secret)) {
            return response('Service unavailable', 503);
        }

        $token = (string) $request->query('token', '');
        if (! hash_equals($secret, $token)) {
            Log::warning('MikroTik IP update: token inválido', [
                'ip_origem' => $request->ip(),
                'nome'      => $request->query('nome'),
            ]);
            return response('Unauthorized', 401);
        }

        $nome   = trim((string) $request->query('nome', ''));
        $novoIp = trim((string) $request->query('ip', ''));

        if (! $nome || ! filter_var($novoIp, FILTER_VALIDATE_IP)) {
            return response('Bad Request: nome e ip são obrigatórios', 400);
        }

        $site = MikroTikSite::where('nome', $nome)->first();

        if (! $site) {
            Log::warning('MikroTik IP update: site não encontrado', ['nome' => $nome]);
            return response('Site not found', 404);
        }

        $antigoIp = $site->host;

        if ($site->host === $novoIp) {
            return response('OK (sem alteração)', 200);
        }

        $site->host = $novoIp;
        $site->save();

        Log::info('MikroTik IP update: host actualizado', [
            'site'     => $nome,
            'antigo'   => $antigoIp,
            'novo'     => $novoIp,
            'ip_origem'=> $request->ip(),
        ]);

        return response('OK', 200);
    }
}
