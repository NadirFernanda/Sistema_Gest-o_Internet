<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Models\Cliente;

class ClienteController extends Controller
{
    /**
     * Retorna planos elegíveis para alerta de vencimento (para exibir na lista do frontend)
     * GET /api/alertas?dias=5
     */
    public function listarAlertas(Request $request)
    {
        $dias = $request->input('dias', 5);
        $hoje = now()->startOfDay();

        $planosRaw = \App\Models\Plano::with('cliente')
            ->where('estado', 'Ativo')
            ->get();

        $planos = $planosRaw->filter(function ($plano) use ($dias, $hoje) {
            if (!$plano->data_ativacao || !$plano->ciclo) {
                return false;
            }
            $dataTermino = \Carbon\Carbon::parse($plano->data_ativacao)->addDays($plano->ciclo - 1)->startOfDay();
            $diasRestantes = $hoje->diffInDays($dataTermino, false);
            return $diasRestantes >= 0 && $diasRestantes <= $dias;
        });

        $alertas = $planos->map(function ($plano) use ($hoje) {
            $dataTermino = \Carbon\Carbon::parse($plano->data_ativacao)->addDays($plano->ciclo - 1)->startOfDay();
            $diasRestantes = $hoje->diffInDays($dataTermino, false);
            return [
                'plano_id' => $plano->id,
                'cliente_id' => $plano->cliente?->id,
                'cliente_nome' => $plano->cliente?->nome,
                'data_termino' => $dataTermino->toDateString(),
                'dias_restantes' => $diasRestantes,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'alertas' => $alertas,
            'total' => $planos->count()
        ]);
    }

    /**
     * Método utilitário para testar envio de alerta de vencimento por e-mail para um cliente.
     * Exemplo de uso no Tinker:
     *   App\Http\Controllers\ClienteController::enviarAlertaEmailTeste(1, 3)
     */
    public static function enviarAlertaEmailTeste($clienteId, $diasRestantes = 3)
    {
        $cliente = \App\Models\Cliente::findOrFail($clienteId);
        $cliente->notify(new \App\Notifications\ClienteVencimentoAlert($cliente, $diasRestantes));
        return 'Alerta de vencimento enviado para ' . $cliente->email;
    }
    public function index()
    {
        $query = Cliente::query();
        if ($busca = request('busca')) {
            $query->where(function($q) use ($busca) {
                $q->where('nome', 'like', "%$busca%")
                  ->orWhere('bi', 'like', "%$busca%")
                  ->orWhere('email', 'like', "%$busca%")
                  ->orWhere('contato', 'like', "%$busca%");
            });
        }
        // Se for chamada via API/AJAX (ex.: /api/clientes), retorna lista simples em JSON
        if (request()->wantsJson() || request()->is('api/*')) {
            $clientes = $query->orderBy('nome')->get();
            return response()->json($clientes);
        }

        // Caso contrário, usa paginação para a listagem na view web
        $clientes = $query->orderBy('nome')->paginate(12)->withQueryString();
        return view('clientes', compact('clientes'));
    }

    public function show($id)
    {
        $cliente = Cliente::with(['equipamentos', 'clienteEquipamentos.equipamento'])->findOrFail($id);
        return view('clientes', compact('cliente'));
    }

    /**
     * Exibe a ficha detalhada imprimível do cliente
     */
    public function ficha($id)
    {
        $cliente = Cliente::with([
            'equipamentos',
            'planos',
            'clienteEquipamentos.equipamento',
            'cobrancas' => function($q) { $q->orderBy('data_vencimento', 'desc')->limit(20); }
        ])->findOrFail($id);
        return view('clientes.ficha', compact('cliente'));
    }

    /**
     * Retorna a ficha em PDF (requer barryvdh/laravel-dompdf instalado)
     */
    public function fichaPdf($id)
    {
        $cliente = Cliente::with([
            'equipamentos',
            'planos',
            'clienteEquipamentos.equipamento',
            'cobrancas' => function($q) { $q->orderBy('data_vencimento', 'desc')->limit(50); }
        ])->findOrFail($id);

        \Log::info('fichaPdf called', ['cliente_id' => $id, 'user_id' => auth()->id() ?? null]);

        $result = $this->generateFichaPdfBytes($cliente);
        if (! $result['ok']) {
            return redirect()->back()->with('error', $result['message']);
        }

        $filename = $result['filename'];
        $output = $result['output'];
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Length' => strlen($output),
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->streamDownload(function () use ($output) {
            echo $output;
        }, $filename, $headers);
    }

    /**
     * Helper: generate PDF bytes for a given Cliente and return status + output.
     */
    private function generateFichaPdfBytes(Cliente $cliente)
    {
        // prepare embedded logo data to avoid remote fetch issues in PDF renderers
        $logoData = null;
        $logoPath = public_path('img/logo2.jpeg');
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $data = base64_encode(file_get_contents($logoPath));
            $logoData = 'data:image/' . $type . ';base64,' . $data;
        }

        if (!class_exists(\Barryvdh\DomPDF\Facade::class)) {
            return ['ok' => false, 'message' => 'Gerar PDF requer barryvdh/laravel-dompdf instalado.', 'output' => null, 'filename' => null];
        }

        $output = null;
        try {
            $pdf = \Barryvdh\DomPDF\Facade::loadView('pdf.ficha_cliente', compact('cliente','logoData'));
            $output = $pdf->output();
        } catch (\Exception $e) {
            \Log::warning('DOMPDF exception generating ficha', ['error' => $e->getMessage()]);
            $output = null;
        }

        // fallback: if output appears too small or failed, try minimal DOMPDF template
        if (empty($output) || strlen($output) < 2000) {
            try {
                $pdf = \Barryvdh\DomPDF\Facade::loadView('pdf.ficha_cliente_minimal', compact('cliente','logoData'));
                $output = $pdf->output();
            } catch (\Exception $e) {
                \Log::warning('DOMPDF exception generating minimal ficha', ['error' => $e->getMessage()]);
                $output = null;
            }
        }

        // If still empty, try mPDF as a robust fallback
        if (empty($output) || strlen($output) < 2000) {
            try {
                if (class_exists('\Mpdf\Mpdf')) {
                    $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir()]);
                    $html = view('pdf.ficha_cliente_minimal', compact('cliente','logoData'))->render();
                    $mpdf->WriteHTML($html);
                    $output = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
                } else {
                    \Log::warning('mPDF not available for fallback generation.');
                }
            } catch (\Exception $e) {
                \Log::error('mPDF exception generating ficha', ['error' => $e->getMessage()]);
                $output = null;
            }
        }

        if (empty($output) || strlen($output) < 2000) {
            \Log::error('Failed to generate ficha PDF - empty output', ['cliente_id' => $cliente->id]);
            return ['ok' => false, 'message' => 'Erro ao gerar PDF. Verifique os logs do servidor.', 'output' => null, 'filename' => null];
        }

        $filename = 'ficha_cliente_'.$cliente->id.'.pdf';
        return ['ok' => true, 'message' => 'ok', 'output' => $output, 'filename' => $filename];
    }

    /**
     * Gera a ficha em PDF e envia por e-mail para o cliente
     */
    public function sendFichaEmail(Request $request, $id)
    {
        $cliente = Cliente::with([
            'equipamentos',
            'planos',
            'clienteEquipamentos.equipamento',
            'cobrancas' => function($q) { $q->orderBy('data_vencimento', 'desc')->limit(50); }
        ])->findOrFail($id);
        \Log::info('sendFichaEmail called', ['cliente_id' => $id, 'user_id' => auth()->id() ?? null]);
        if (empty($cliente->email) || !filter_var($cliente->email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Cliente sem e-mail válido.');
        }
        // prepare embedded logo data for reliable PDF rendering
        $logoData = null;
        $logoPath = public_path('img/logo2.jpeg');
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $data = base64_encode(file_get_contents($logoPath));
            $logoData = 'data:image/' . $type . ';base64,' . $data;
        }

        if (!class_exists(\Barryvdh\DomPDF\Facade::class)) {
            return redirect()->back()->with('error', 'Gerar PDF requer barryvdh/laravel-dompdf instalado.');
        }

        $output = null;
        try {
            $pdf = \Barryvdh\DomPDF\Facade::loadView('pdf.ficha_cliente', compact('cliente', 'logoData'));
            $output = $pdf->output();
        } catch (\Exception $e) {
            \Log::warning('DOMPDF exception generating ficha for email', ['error' => $e->getMessage()]);
            $output = null;
        }
        if (empty($output) || strlen($output) < 2000) {
            try {
                $pdf = \Barryvdh\DomPDF\Facade::loadView('pdf.ficha_cliente_minimal', compact('cliente','logoData'));
                $output = $pdf->output();
            } catch (\Exception $e) {
                \Log::warning('DOMPDF minimal template failed for email', ['error' => $e->getMessage()]);
                $output = null;
            }
        }
        if (empty($output) || strlen($output) < 2000) {
            try {
                if (class_exists('\Mpdf\Mpdf')) {
                    $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir()]);
                    $html = view('pdf.ficha_cliente_minimal', compact('cliente','logoData'))->render();
                    $mpdf->WriteHTML($html);
                    $output = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
                } else {
                    \Log::warning('mPDF not available for email fallback generation.');
                }
            } catch (\Exception $e) {
                \Log::error('mPDF exception generating ficha for email', ['error' => $e->getMessage()]);
                $output = null;
            }
        }
        $filename = 'ficha_cliente_'.$cliente->id.'.pdf';
        $attachments = [];
        $attachments[] = ['content' => $output, 'name' => $filename, 'mime' => 'application/pdf'];

        // attach recent/pending cobrancas as PDFs (limit 10)
        $cobrancas = $cliente->cobrancas()->whereIn('status', ['pendente','atrasado'])->orderBy('data_vencimento', 'asc')->limit(10)->get();
        foreach ($cobrancas as $cobranca) {
                try {
                    if (class_exists(\Mpdf\Mpdf::class)) {
                        $htmlC = view('cobrancas.comprovante', compact('cobranca'))->render();
                        $mpdfC = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir()]);
                        $mpdfC->WriteHTML($htmlC);
                        $attachments[] = ['content' => $mpdfC->Output('', \Mpdf\Output\Destination::STRING_RETURN), 'name' => 'cobranca_'.$cobranca->id.'.pdf', 'mime' => 'application/pdf'];
                    } else {
                        $pdfC = \Barryvdh\DomPDF\Facade::loadView('cobrancas.comprovante', compact('cobranca'));
                        $attachments[] = ['content' => $pdfC->output(), 'name' => 'cobranca_'.$cobranca->id.'.pdf', 'mime' => 'application/pdf'];
                    }
                } catch (\Exception $e) {
                    \Log::warning('Falha ao gerar PDF da cobranca para anexar', ['cobranca_id' => $cobranca->id, 'erro' => $e->getMessage()]);
                }
        }

        try {
            \Mail::to($cliente->email)->send(new \App\Mail\FichaClienteMail($cliente, $attachments));
            return redirect()->back()->with('success', 'Ficha enviada por e-mail para ' . $cliente->email);
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar ficha por email', ['erro' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erro ao enviar e-mail.');
        }
    }
    public function store(Request $request)
    {
        \Log::info('Entrou no método store do ClienteController', ['request' => $request->all()]);
        try {
            $validated = $request->validate([
                'bi' => 'required|string|max:32|unique:clientes,bi',
                'nome' => 'required|string|max:255',
                'email' => 'required|email|unique:clientes,email',
                'contato' => 'required|string|max:20|unique:clientes,contato',
            ]);

            $cliente = Cliente::create($validated);
            \Log::info('Cliente cadastrado', ['cliente' => $cliente]);

            return response()->json([
                'success' => true,
                'cliente' => $cliente
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors();
            $customMessages = [];
            if ($errors->has('email')) {
                $customMessages['email'] = 'Já existe um cliente cadastrado com este e-mail.';
            }
            if ($errors->has('contato')) {
                $customMessages['contato'] = 'Já existe um cliente cadastrado com este contato.';
            }
            $defaultMessages = $errors->messages();
            $allMessages = array_merge($defaultMessages, $customMessages);
            return response()->json([
                'success' => false,
                'errors' => $allMessages
            ], 422);
        }
    }

    public function update(Request $request, $id)
    {

        $validated = $request->validate([
            'nome' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|email|unique:clientes,email,' . $id,
            'contato' => 'sometimes|required|string|max:20|unique:clientes,contato,' . $id,
            'bi' => 'sometimes|nullable|string|max:32',
        ]);

        $cliente = Cliente::findOrFail($id);
        $cliente->update($validated);

        return response()->json(['success' => true, 'cliente' => $cliente]);
    }

    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Cria uma URL assinada temporária para download da ficha (válida por X minutos).
     * Rota protegida por `auth` — retorna a URL que pode ser usada por cURL/browser.
     */
    public function createSignedUrl($id)
    {
        // gera URL assinada para a rota nomeada 'clientes.ficha.signed'
        $url = URL::temporarySignedRoute('clientes.ficha.signed', now()->addMinutes(10), ['cliente' => $id]);
        return response()->json(['url' => $url]);
    }

    /**
     * Serve a ficha em PDF através de uma rota assinada (ignora sessão, mas requer assinatura válida).
     * Esta função delega para `fichaPdf` para reutilizar a lógica de geração.
     */
    public function fichaPdfSigned(Request $request, $id)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'URL inválida ou expirada');
        }
        // Generate PDF bytes directly and return with enforced PDF headers
        $cliente = Cliente::with([
            'equipamentos',
            'planos',
            'clienteEquipamentos.equipamento',
            'cobrancas' => function($q) { $q->orderBy('data_vencimento', 'desc')->limit(50); }
        ])->findOrFail($id);

        $result = $this->generateFichaPdfBytes($cliente);
        if (! $result['ok']) {
            return response($result['message'], 500)->header('Content-Type', 'text/plain');
        }

        $output = $result['output'];
        $filename = $result['filename'];
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Length' => strlen($output),
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($output) {
            echo $output;
        }, 200, $headers);
    }

    /**
     * TEMPORARY: Serve ficha PDF publicly for testing (no auth, no signature).
     * REMOVE after verification.
     */
    public function fichaPdfPublic($id)
    {
        // Note: this intentionally bypasses auth for quick testing only.
        // Call the main generator but if it returns a RedirectResponse (redirect()->back()),
        // convert that into a 500 error so curl/tests receive a clear non-HTML PDF response.
        $response = $this->fichaPdf($id);
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            \Log::warning('Public ficha download attempted but generator returned a redirect', ['cliente_id' => $id]);
            return response('Erro ao gerar PDF (ver logs do servidor).', 500)
                ->header('Content-Type', 'text/plain');
        }
        return $response;
    }
}
