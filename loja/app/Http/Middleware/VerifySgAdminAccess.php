<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySgAdminAccess
{
    /**
     * Protege o painel Admin da loja.
     *
     * Autenticação por formulário de login (/admin/login) ou por token SSO
     * enviado pelo SG via query string (?sg_sso=TOKEN).
     * A sessão guarda apenas um flag booleano — nunca o valor do token.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se não houver token configurado no servidor, bloqueia TUDO por segurança.
        $expected = (string) config('services.sg.admin_token', '');
        if ($expected === '') {
            abort(503, 'Admin token not configured on this server.');
        }

        // Bypass SSO via URL: aceite em qualquer ambiente (HTTPS em produção).
        // Redireciona para o mesmo caminho SEM query string para não poluir logs/histórico.
        $incomingToken = $request->query('token', $request->query('sg_sso', ''));
        if ($incomingToken !== '' && hash_equals($expected, (string) $incomingToken)) {
            $request->session()->put('sg_admin_authenticated', true);
            return redirect($request->url()); // url() exclui query string
        }

        if (! $request->session()->get('sg_admin_authenticated', false)) {
            return redirect()->route('admin.login')
                ->with('error', 'Por favor faça login para aceder ao painel.');
        }

        return $next($request);
    }
}
