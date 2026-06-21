<?php

namespace App\Http\Controllers;

use App\Models\Cobranca;
use App\Models\Pagamento;
use App\Models\Plano;
use App\Services\MikroTikService;
use App\Services\Pay4AllService;
use App\Services\PlanoRenovacaoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PagamentoController extends Controller
{
    public function __construct(private Pay4AllService $pay4all) {}

    /**
     * Exibe o formulário para iniciar pagamento via Multicaixa Express (GPO).
     */
    public function iniciar(Cobranca $cobranca)
    {
        // Verificar se já existe pagamento aprovado
        if ($cobranca->pagamentos()->where('status', 'aprovado')->exists()) {
            return redirect()->route('cobrancas.show', $cobranca)
                ->with('info', 'Esta cobrança já foi paga.');
        }

        $ultimoPagamento = $cobranca->ultimoPagamento;

        return view('pagamentos.iniciar', compact('cobranca', 'ultimoPagamento'));
    }

    /**
     * Processa o pedido de pagamento e envia para o gateway Pay4All.
     */
    public function processar(Request $request, Cobranca $cobranca)
    {
        $request->validate([
            'telefone' => ['required', 'string', 'regex:/^(244)?9[0-9]{8}$/'],
        ], [
            'telefone.required' => 'O número de telemóvel é obrigatório.',
            'telefone.regex'    => 'Número inválido. Use o formato: 9XXXXXXXX ou 2449XXXXXXXX',
        ]);

        // Verificar se já existe pagamento aprovado
        if ($cobranca->pagamentos()->where('status', 'aprovado')->exists()) {
            return redirect()->route('cobrancas.show', $cobranca)
                ->with('info', 'Esta cobrança já foi paga.');
        }

        // Gerar ID único da transação (max 15 chars alfanumérico)
        $merchantTxId = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', 'CB' . $cobranca->id . Str::random(6)), 0, 15));

        // Normalizar telefone (remover espaços e traços)
        $telefone = preg_replace('/\D/', '', $request->telefone);

        // Criar registo de pagamento local (pendente)
        $pagamento = Pagamento::create([
            'cobranca_id'            => $cobranca->id,
            'merchant_transaction_id' => $merchantTxId,
            'valor'                  => $cobranca->valor,
            'moeda'                  => 'AOA',
            'telefone'               => $telefone,
            'status'                 => 'processando',
        ]);

        try {
            $resposta = $this->pay4all->createCharge(
                amount:        (float) $cobranca->valor,
                phoneNumber:   $telefone,
                transactionId: $merchantTxId,
                description:   'Cobranca ' . $cobranca->id . ' SGA',
                customerName:  $cobranca->cliente->nome ?? null,
            );

            // A resposta 202 (async) não contém responseStatus — apenas regista o ID se presente
            $pagamento->update([
                'gateway_transaction_id' => $resposta['id'] ?? null,
                'gateway_status'         => $resposta['operationStatus'] ?? null,
            ]);

            return redirect()->route('pagamentos.aguardar', $pagamento)
                ->with('success', 'Pedido enviado! Autorize o pagamento na app Multicaixa Express.');

        } catch (\RuntimeException $e) {
            $pagamento->update(['status' => 'erro', 'gateway_message' => $e->getMessage()]);

            return back()->withErrors(['gateway' => $e->getMessage()]);
        }
    }

    /**
     * Página de espera / confirmação do pagamento.
     */
    public function aguardar(Pagamento $pagamento)
    {
        $cobranca = $pagamento->cobranca;
        return view('pagamentos.aguardar', compact('pagamento', 'cobranca'));
    }

    /**
     * Webhook assíncrono recebido do Pay4All após processamento.
     * URL pública, sem CSRF. Verificação pelo token de notificação.
     */
    public function webhook(Request $request)
    {
        // Verificar token de notificação enviado no header ou query param
        $token = $request->header('X-Notification-Token')
            ?? $request->header('Authorization')
            ?? $request->query('token');

        // Remover prefixo "Bearer " se presente
        $token = ltrim(str_replace('Bearer', '', $token ?? ''));
        $token = trim($token);

        if (!$this->pay4all->verifyNotificationToken($token)) {
            Log::warning('Pay4All webhook: token de notificação inválido', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $dados = $this->pay4all->parseWebhookPayload($request->all());

        Log::info('Pay4All webhook recebido', $dados);

        // Localizar pagamento pelo merchant_transaction_id
        $pagamento = Pagamento::where('merchant_transaction_id', $dados['merchant_tx_id'])->first();

        if (!$pagamento) {
            Log::warning('Pay4All webhook: transação não encontrada', ['merchant_tx_id' => $dados['merchant_tx_id']]);
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        // Mapear status normalizado (vem de parseWebhookPayload) para status local
        $statusLocal = match ($dados['status']) {
            'approved'  => 'aprovado',
            'rejected'  => 'recusado',
            'cancelled' => 'recusado',
            'failed'    => 'erro',
            default     => 'erro',
        };

        $pagamento->update([
            'gateway_transaction_id' => $dados['transaction_id'] ?? $pagamento->gateway_transaction_id,
            'status'                 => $statusLocal,
            'gateway_status'         => $dados['status'],
            'gateway_code'           => $dados['code'],
            'gateway_message'        => $dados['message'],
            'gateway_payload'        => $request->all(),
            'processado_em'          => now(),
        ]);

        // Se aprovado, marcar a cobrança como paga e avançar o plano
        if ($statusLocal === 'aprovado') {
            $cobranca = $pagamento->cobranca;
            $cobranca->update([
                'status'         => 'pago',
                'data_pagamento' => now()->toDateString(),
            ]);

            Log::info('Pay4All: cobrança marcada como paga', [
                'cobranca_id'  => $cobranca->id,
                'pagamento_id' => $pagamento->id,
            ]);

            PlanoRenovacaoService::avancarPlano($cobranca);
        }

        return response()->json(['received' => true]);
    }

    /**
     * Verifica o status atual de um pagamento (polling do frontend).
     */
    public function status(Pagamento $pagamento)
    {
        return response()->json([
            'status'    => $pagamento->status,
            'aprovado'  => $pagamento->isAprovado(),
            'mensagem'  => $pagamento->gateway_message,
        ]);
    }
}
