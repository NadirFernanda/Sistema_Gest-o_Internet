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
     * Autenticação exclusivamente por formulário de login (/admin/login).
     * A sessão guarda apenas um flag booleano — nunca o valor do token.
     * Tokens NUNCA são aceites em URLs ou cabeçalhos HTTP (evita fuga via logs,
     * histórico do browser e cabeçalhos Referer).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se não houver token configurado no servidor, bloqueia TUDO por segurança.
        if ((string) config('services.sg.admin_token', '') === '') {
            abort(503, 'Admin token not configured on this server.');
        }

        if (! $request->session()->get('sg_admin_authenticated', false)) {
            return redirect()->route('admin.login')
                ->with('error', 'Por favor faça login para aceder ao painel.');
        }

        return $next($request);
    }
}
