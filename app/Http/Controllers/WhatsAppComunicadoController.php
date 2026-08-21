<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\WhatsappLedger;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppComunicadoController extends Controller
{
    public function index()
    {
        $clientes = Cliente::whereNotNull('contato')
            ->where('contato', '!=', '')
            ->orderBy('nome')
            ->get(['id', 'nome', 'contato']);

        $saldo          = WhatsappLedger::saldoAtual();
        $custoPorMensagem = WhatsappLedger::CUSTO_POR_MENSAGEM;

        return view('admin.whatsapp-comunicado.index', compact('clientes', 'saldo', 'custoPorMensagem'));
    }

    public function enviar(Request $request)
    {
        $validated = $request->validate([
            'mensagem'      => ['required', 'string', 'min:5', 'max:4000'],
            'modo'          => ['required', 'in:todos,seleccionados,manuais'],
            'cliente_ids'   => ['array'],
            'cliente_ids.*' => ['integer', 'exists:clientes,id'],
            'numeros'       => ['nullable', 'string'],
        ]);

        $service  = new WhatsAppService();
        $mensagem = $validated['mensagem'];

        // Construir lista de destinatários
        $destinatarios = collect(); // [['nome'=>..., 'numero'=>...]]

        if ($validated['modo'] === 'todos') {
            Cliente::whereNotNull('contato')->where('contato', '!=', '')->get(['id', 'nome', 'contato'])
                ->each(fn($c) => $destinatarios->push(['nome' => $c->nome, 'numero' => $c->contato]));

        } elseif ($validated['modo'] === 'seleccionados') {
            if (! empty($validated['cliente_ids'])) {
                Cliente::whereIn('id', $validated['cliente_ids'])->get(['id', 'nome', 'contato'])
                    ->each(fn($c) => $destinatarios->push(['nome' => $c->nome, 'numero' => $c->contato]));
            }

        } elseif ($validated['modo'] === 'manuais') {
            $linhas = preg_split('/[\n,;]+/', $validated['numeros'] ?? '');
            foreach ($linhas as $linha) {
                $n = trim($linha);
                if ($n !== '') {
                    $destinatarios->push(['nome' => null, 'numero' => $n]);
                }
            }
        }

        if ($destinatarios->isEmpty()) {
            return back()->withErrors(['mensagem' => 'Nenhum destinatário seleccionado.'])->withInput();
        }

        // Evitar detecção de spam pela Meta: intervalo entre mensagens
        $delaySegundos = 4;

        // Garantir que o PHP não corta a execução em envios grandes
        set_time_limit(max(ini_get('max_execution_time'), $destinatarios->count() * ($delaySegundos + 5)));

        $enviados  = 0;
        $falhados  = 0;
        $erros     = [];
        $semSaldo  = false;
        $primeiro  = true;

        foreach ($destinatarios as $d) {
            // Pausa entre mensagens (excepto antes da primeira)
            if (!$primeiro) {
                sleep($delaySegundos);
            }
            $primeiro = false;

            try {
                $service->enviarMensagem($d['numero'], $mensagem, 'comunicado', $d['nome']);
                $enviados++;
            } catch (\App\Exceptions\SaldoWhatsAppInsuficienteException) {
                $semSaldo = true;
                break;
            } catch (\Throwable $e) {
                $falhados++;
                $erros[] = ($d['nome'] ?? $d['numero']) . ': ' . $e->getMessage();
                Log::warning('WhatsApp comunicado: falha', ['numero' => $d['numero'], 'erro' => $e->getMessage()]);
            }
        }

        $resumo = "Enviado para {$enviados} destinatário(s).";
        if ($falhados > 0) $resumo .= " {$falhados} falharam.";
        if ($semSaldo)     $resumo .= ' Saldo insuficiente — envio interrompido.';

        return back()->with('resultado', [
            'enviados' => $enviados,
            'falhados' => $falhados,
            'erros'    => array_slice($erros, 0, 10),
            'sem_saldo' => $semSaldo,
            'resumo'   => $resumo,
        ]);
    }
}
