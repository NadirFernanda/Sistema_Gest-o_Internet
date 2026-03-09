<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StoreProxyController;
use App\Models\FamilyPlanRequest;
use Illuminate\Http\Request;

/**
 * FamilyPlanRequestAdminController
 * ══════════════════════════════════
 * Manages family/business plan requests submitted via the loja checkout.
 *
 * Routes:
 *   GET  /admin/pedidos-planos-familiares            → index()
 *   POST /admin/pedidos-planos-familiares/{id}/confirmar  → confirmar()
 *   POST /admin/pedidos-planos-familiares/{id}/cancelar   → cancelar()
 */
class FamilyPlanRequestAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = FamilyPlanRequest::orderByDesc('created_at');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $requests  = $query->paginate(25)->withQueryString();
        $counts    = [
            'pending'   => FamilyPlanRequest::where('status', FamilyPlanRequest::STATUS_PENDING)->count(),
            'confirmed' => FamilyPlanRequest::where('status', FamilyPlanRequest::STATUS_CONFIRMED)->count(),
            'activated' => FamilyPlanRequest::where('status', FamilyPlanRequest::STATUS_ACTIVATED)->count(),
            'cancelled' => FamilyPlanRequest::where('status', FamilyPlanRequest::STATUS_CANCELLED)->count(),
        ];

        return view('admin.family_requests.index', compact('requests', 'counts', 'status'));
    }

    /**
     * Confirm a pending plan request:
     *  1. Call the SG API to find/create the client and add the janela.
     *  2. If successful, mark the request as ACTIVATED.
     *  3. Store the SG client_id and plano_id on the request for reference.
     */
    public function confirmar(FamilyPlanRequest $familyPlanRequest)
    {
        if (! in_array($familyPlanRequest->status, [
            FamilyPlanRequest::STATUS_PENDING,
            FamilyPlanRequest::STATUS_CONFIRMED,
        ])) {
            return back()->with('error', 'Este pedido não pode ser confirmado (estado: ' . $familyPlanRequest->status . ').');
        }

        $proxy  = app(StoreProxyController::class);
        $result = $proxy->syncJanela([
            'nome'            => $familyPlanRequest->customer_name,
            'email'           => $familyPlanRequest->customer_email,
            'contato'         => $familyPlanRequest->customer_phone,
            'nif'             => $familyPlanRequest->customer_nif,
            'template_id'     => $familyPlanRequest->plan_id,
            'loja_request_id' => $familyPlanRequest->id,
        ]);

        if (! $result['success']) {
            return back()->with('error',
                'Erro ao comunicar com o Sistema de Gestão: ' . ($result['error'] ?? 'desconhecido') .
                '. O pedido foi marcado como confirmado localmente mas a janela não foi adicionada no SG.'
            );
        }

        $sgData = $result['data'] ?? [];

        // Persist SG IDs in notes for traceability
        $nota = 'SG: cliente_id=' . ($sgData['cliente_id'] ?? '?')
              . ' | plano_id='   . ($sgData['plano_id']   ?? '?')
              . ' | proxima_renovacao=' . ($sgData['proxima_renovacao'] ?? '?')
              . ' | action=' . ($sgData['action'] ?? '?');

        $familyPlanRequest->update([
            'status' => FamilyPlanRequest::STATUS_ACTIVATED,
            'notes'  => $nota,
        ]);

        return back()->with('success',
            'Pedido confirmado. Janela adicionada no SG para o cliente ' . $familyPlanRequest->customer_name .
            '. Próxima renovação: ' . ($sgData['proxima_renovacao'] ?? 'N/D') . '.'
        );
    }

    public function cancelar(FamilyPlanRequest $familyPlanRequest)
    {
        if ($familyPlanRequest->status === FamilyPlanRequest::STATUS_ACTIVATED) {
            return back()->with('error', 'Não é possível cancelar um pedido já activado no SG.');
        }

        $familyPlanRequest->update(['status' => FamilyPlanRequest::STATUS_CANCELLED]);

        return back()->with('success', 'Pedido #' . $familyPlanRequest->id . ' cancelado.');
    }
}
