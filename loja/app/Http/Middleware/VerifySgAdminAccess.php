<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySgAdminAccess
{
    /**
     * Verifica se o acesso ao painel da loja veio do SG (ou de quem conhece o token partilhado).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.sg.admin_token', '');

        // Se não houver token configurado, bloqueia por segurança
        if ($expected === '') {
            abort(403, 'Admin token not configured.');
        }

        // Token pode vir via query string (?token=...), header (X-SG-Admin-Token) ou sessão (autenticado anteriormente)
        $provided = (string) ($request->query('token')
            ?: $request->header('X-SG-Admin-Token', '')
            ?: $request->session()->get('sg_admin_token', ''));

        if ($provided === '' || ! hash_equals($expected, $provided)) {
            abort(403, 'Unauthorized access to loja admin.');
        }

        // Persiste na sessão para que as navegações seguintes não precisem do token na URL
        $request->session()->put('sg_admin_token', $expected);

        return $next($request);
    }
}
