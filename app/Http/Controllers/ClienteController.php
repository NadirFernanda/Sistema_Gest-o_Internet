<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use App\Models\Cliente;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CompensacoesExport;

class ClienteController extends Controller
{
    /**
     * Compensa dias para o cliente (stub inicial).
     * POST /clientes/{cliente}/compensar-dias
     */
    public function compensarDias(Request $request, $cliente)
    {
        $request->validate([
            'dias_compensados' => 'required|integer|min:1|max:90',
        ]);

        $cliente = Cliente::findOrFail($cliente);
        // Busca o plano ativo mais recente (estado = 'Ativo' - tolerante a case e espaços - e ativo = true|null)
        $plano = $cliente->planos()
            ->whereRaw("LOWER(TRIM(COALESCE(estado, ''))) = ?", ['ativo'])
            ->where(function($q) {
                $q->where('ativo', true)->orWhereNull('ativo');
            })
            ->orderByDesc('data_ativacao')
            ->first();

        if (!$plano) {
            // Log para diagnóstico: lista planos do cliente e seus estados/flags
            try {
                $todos = $cliente->planos()->get(['id','nome','estado','ativo','data_ativacao'])->map(function($p){
                    return $p->toArray();
                });
                \Log::warning('compensarDias: nenhum plano ativo encontrado', ['cliente_id' => $cliente->id, 'planos' => $todos]);
            } catch (\Exception $e) {
                \Log::warning('compensarDias: falha ao listar planos para debug', ['cliente_id' => $cliente->id, 'err' => $e->getMessage()]);
            }

            return back()->with('error', 'Nenhum plano ativo encontrado para este cliente. Verifique estado/flag do plano.');
        }
        // Opção A: não alteramos o "ciclo" original — adicionamos dias à próxima renovação
        $hoje = Carbon::today();

        // Determina a data atual de próxima renovação: se já foi registrada, usa-a,
        // caso contrário calcula a partir de data_ativacao + ciclo - 1
        try {
            if (!empty($plano->proxima_renovacao)) {
                $currentNext = Carbon::parse($plano->proxima_renovacao);
            } elseif (!empty($plano->data_ativacao) && $plano->ciclo) {
                $cicloInt = intval(filter_var($plano->ciclo, FILTER_SANITIZE_NUMBER_INT));
                if ($cicloInt <= 0) {
                    // fallback: try casting normally
                    $cicloInt = (int)$plano->ciclo;
                }
                \Log::debug('compensarDias: cicloInt resolved', ['plano_id' => $plano->id, 'ciclo_raw' => $plano->ciclo, 'ciclo_int' => $cicloInt]);
                $currentNext = Carbon::parse($plano->data_ativacao)->addDays($cicloInt - 1);
            } else {
                // fallback para hoje
                $currentNext = $hoje;
            }
        } catch (\Exception $e) {
            \Log::warning('compensarDias: falha ao parsear datas do plano', ['plano_id' => $plano->id, 'err' => $e->getMessage()]);
            $currentNext = $hoje;
        }

        $anterior = $currentNext->toDateString();
        $diasComp = intval(filter_var($request->dias_compensados, FILTER_SANITIZE_NUMBER_INT));
        if ($diasComp <= 0) {
            $diasComp = (int) $request->dias_compensados;
        }
        \Log::debug('compensarDias: dias to add', ['cliente_id' => $cliente->id, 'plano_id' => $plano->id, 'dias_raw' => $request->dias_compensados, 'dias_int' => $diasComp]);
        $novo = $currentNext->copy()->addDays($diasComp)->toDateString();

        $plano->proxima_renovacao = $novo;
        $plano->save();

        // Registra compensação em tabela dedicada
        try {
            \DB::table('compensacoes')->insert([
                'plano_id' => $plano->id,
                'user_id' => auth()->id() ?? null,
                'dias_compensados' => (int) $request->dias_compensados,
                'anterior' => $anterior,
                'novo' => $novo,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::warning('compensarDias: falha ao gravar registro de compensacao', ['err' => $e->getMessage(), 'plano_id' => $plano->id]);
        }

        return back()->with('success', "Compensados {$request->dias_compensados} dias. Próxima renovação: " . Carbon::parse($novo)->format('d/m/Y'));
    }
    /**
     * Exibe o histórico de compensações de dias para os planos do cliente
     * GET /clientes/{cliente}/compensacoes
     */
    public function compensacoes($cliente)
    {
        $cliente = Cliente::with('planos')->findOrFail($cliente);

        $planoIds = $cliente->planos->pluck('id')->toArray();

        $compensacoes = collect(\DB::table('compensacoes')
            ->whereIn('plano_id', $planoIds ?: [0])
            ->orderByDesc('created_at')
            ->get()
        );

        $planoMap = $cliente->planos->keyBy('id');

        $userIds = $compensacoes->pluck('user_id')->filter()->unique()->values()->all();
        $users = collect([]);
        if (!empty($userIds)) {
            // Load Eloquent User models with roles (spatie) so the view can show role name and real name
            $users = User::whereIn('id', $userIds)->with('roles')->get()->keyBy('id');
        }

        return view('clientes.compensacoes', compact('cliente','compensacoes','planoMap','users'));
    }
    
    /**
     * Exporta o histórico de compensações para Excel
     */
    public function exportCompensacoes($cliente)
    {
        $cliente = Cliente::with('planos')->findOrFail($cliente);
        $planoIds = $cliente->planos->pluck('id')->toArray();

        $compensacoes = collect(\DB::table('compensacoes')
            ->whereIn('plano_id', $planoIds ?: [0])
            ->orderByDesc('created_at')
            ->get()
        );

        $planoMap = $cliente->planos->keyBy('id');

        $userIds = $compensacoes->pluck('user_id')->filter()->unique()->values()->all();
        $users = collect([]);
        if (!empty($userIds)) {
            $users = User::whereIn('id', $userIds)->with('roles')->get()->keyBy('id');
        }

        return Excel::download(new CompensacoesExport($compensacoes, $users, $planoMap), 'compensacoes.xlsx');
    }
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
            $dataTermino = \Carbon\Carbon::parse($plano->data_ativacao)->addDays((int)$plano->ciclo - 1)->startOfDay();
            $diasRestantes = $hoje->diffInDays($dataTermino, false);
            return $diasRestantes >= 0 && $diasRestantes <= $dias;
        });

        $alertas = $planos->map(function ($plano) use ($hoje) {
            $dataTermino = \Carbon\Carbon::parse($plano->data_ativacao)->addDays((int)$plano->ciclo - 1)->startOfDay();
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

    /**
     * Show the form to create a new Cliente (separate page)
     */
    public function create()
    {
        return view('clientes.create');
    }

    public function show($id)
    {
        $cliente = Cliente::with(['equipamentos', 'clienteEquipamentos.equipamento'])->findOrFail($id);
        return view('clientes', compact('cliente'));
    }

    /**
     * Edit form for a cliente. Reuses the same `clientes` view (ficha)
     * so the edit form (inline) can be displayed. Kept minimal to avoid
     * duplicating presentation logic.
     */
    public function edit($id)
    {
        // Return a dedicated edit view for the cliente. This is
        // cleaner than relying on redirect+hash and fragile JS.
        $cliente = Cliente::findOrFail($id);
        return view('clientes.edit', compact('cliente'));
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

        // Try DomPDF first if available; otherwise fall through to other generators (mPDF fallback).

        $output = null;
        // Ensure DomPDF uses a UTF-8 capable default font and remote assets
        try {
            Pdf::setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);
        } catch (\Exception $e) {
            \Log::warning('Pdf::setOptions failed', ['err' => $e->getMessage()]);
        }
        try {
            $pdf = Pdf::loadView('pdf.ficha_cliente', compact('cliente','logoData'));
            $output = $pdf->output();
        } catch (\Exception $e) {
            \Log::warning('DOMPDF exception generating ficha', ['error' => $e->getMessage()]);
            $output = null;
        }

        // fallback: if output appears too small or failed, try minimal DOMPDF template
        if (empty($output) || strlen($output) < 2000) {
            try {
                // Ensure DomPDF uses a UTF-8 capable default font and remote assets
                try {
                    Pdf::setOptions([
                        'defaultFont' => 'DejaVu Sans',
                        'isHtml5ParserEnabled' => true,
                        'isRemoteEnabled' => true,
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('Pdf::setOptions failed (minimal)', ['err' => $e->getMessage()]);
                }
                $pdf = Pdf::loadView('pdf.ficha_cliente_minimal', compact('cliente','logoData'));
                $output = $pdf->output();
            } catch (\Exception $e) {
                \Log::warning('DOMPDF exception generating minimal ficha', ['error' => $e->getMessage()]);
                $output = null;
            }
        }

        // If still empty, try mPDF as a robust fallback
        if (empty($output) || strlen($output) < 2000) {
            try {
                // Ensure DomPDF uses a UTF-8 capable default font and remote assets
                try {
                    Pdf::setOptions([
                        'defaultFont' => 'DejaVu Sans',
                        'isHtml5ParserEnabled' => true,
                        'isRemoteEnabled' => true,
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('Pdf::setOptions failed (email)', ['err' => $e->getMessage()]);
                }
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

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class) && !class_exists(Pdf::class)) {
            return redirect()->back()->with('error', 'Gerar PDF requer barryvdh/laravel-dompdf instalado.');
        }

        $output = null;
            try {
                $pdf = Pdf::loadView('pdf.ficha_cliente', compact('cliente', 'logoData'));
                $output = $pdf->output();
            } catch (\Exception $e) {
                \Log::warning('DOMPDF exception generating ficha for email', ['error' => $e->getMessage()]);
                $output = null;
            }
        if (empty($output) || strlen($output) < 2000) {
                try {
                    $pdf = Pdf::loadView('pdf.ficha_cliente_minimal', compact('cliente','logoData'));
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
                        try {
                            Pdf::setOptions([
                                'defaultFont' => 'DejaVu Sans',
                                'isHtml5ParserEnabled' => true,
                                'isRemoteEnabled' => true,
                            ]);
                        } catch (\Exception $e) {
                            \Log::warning('Pdf::setOptions failed (cobranca attachment)', ['err' => $e->getMessage()]);
                        }
                        $pdfC = Pdf::loadView('cobrancas.comprovante', compact('cobranca'));
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

    /**
     * Gera a ficha em PDF, envia por e-mail ao cliente e retorna o PDF para download
     * Este método combina as ações de `fichaPdf` e `sendFichaEmail` em um único clique.
     */
    public function fichaPdfAndSend(Request $request, $id)
    {
        $cliente = Cliente::with([
            'equipamentos',
            'planos',
            'clienteEquipamentos.equipamento',
            'cobrancas' => function($q) { $q->orderBy('data_vencimento', 'desc')->limit(50); }
        ])->findOrFail($id);

        \Log::info('fichaPdfAndSend called', ['cliente_id' => $id, 'user_id' => auth()->id() ?? null]);

        // prepare embedded logo data to avoid remote fetch issues in PDF renderers
        $logoData = null;
        $logoPath = public_path('img/logo2.jpeg');
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $data = base64_encode(file_get_contents($logoPath));
            $logoData = 'data:image/' . $type . ';base64,' . $data;
        }

        // Try to generate PDF using the same facade as CobrancaController (DomPDF)
        try {
            // check for the actual DomPDF facade class (Pdf) or the imported alias
            // Ensure DomPDF uses a UTF-8 capable default font and remote assets
            try {
                Pdf::setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                ]);
            } catch (\Exception $e) {
                \Log::warning('Pdf::setOptions failed (email direct)', ['err' => $e->getMessage()]);
            }
            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class) || class_exists(Pdf::class)) {
                $pdf = Pdf::loadView('pdf.ficha_cliente', compact('cliente','logoData'));
                $output = $pdf->output();
            } else {
                // fallback to existing generator helper which tries DomPDF, mPDF, etc.
                $result = $this->generateFichaPdfBytes($cliente);
                if (! $result['ok']) {
                    return response($result['message'], 500)->header('Content-Type', 'text/plain');
                }
                $output = $result['output'];
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao gerar PDF da ficha (fichaPdfAndSend)', ['erro' => $e->getMessage()]);
            return response('Erro ao gerar PDF. Verifique os logs do servidor.', 500)->header('Content-Type', 'text/plain');
        }

        $filename = 'ficha_cliente_'.$cliente->id.'.pdf';

        // Prepare attachments (attach ficha + recent cobrancas)
        $attachments = [];
        $attachments[] = ['content' => $output, 'name' => $filename, 'mime' => 'application/pdf'];

        $cobrancas = $cliente->cobrancas()->whereIn('status', ['pendente','atrasado'])->orderBy('data_vencimento', 'asc')->limit(10)->get();
        foreach ($cobrancas as $cobranca) {
            try {
                if (class_exists(\Mpdf\Mpdf::class)) {
                    $htmlC = view('cobrancas.comprovante', compact('cobranca'))->render();
                    $mpdfC = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir()]);
                    $mpdfC->WriteHTML($htmlC);
                    $attachments[] = ['content' => $mpdfC->Output('', \Mpdf\Output\Destination::STRING_RETURN), 'name' => 'cobranca_'.$cobranca->id.'.pdf', 'mime' => 'application/pdf'];
                } else {
                    try {
                        Pdf::setOptions([
                            'defaultFont' => 'DejaVu Sans',
                            'isHtml5ParserEnabled' => true,
                            'isRemoteEnabled' => true,
                        ]);
                    } catch (\Exception $e) {
                        \Log::warning('Pdf::setOptions failed (cobranca attachment 2)', ['err' => $e->getMessage()]);
                    }
                    $pdfC = Pdf::loadView('cobrancas.comprovante', compact('cobranca'));
                    $attachments[] = ['content' => $pdfC->output(), 'name' => 'cobranca_'.$cobranca->id.'.pdf', 'mime' => 'application/pdf'];
                }
            } catch (\Exception $e) {
                \Log::warning('Falha ao gerar PDF da cobranca para anexar (fichaPdfAndSend)', ['cobranca_id' => $cobranca->id, 'erro' => $e->getMessage()]);
            }
        }

        // Send email (best-effort) but do not block download on failure
        try {
            if (! empty($cliente->email) && filter_var($cliente->email, FILTER_VALIDATE_EMAIL)) {
                // Use Notification pattern (same as comprovante) which is already working in the app
                $cliente->notify(new \App\Notifications\FichaClienteEmail($cliente, $attachments));
            } else {
                \Log::warning('Cliente sem e-mail válido - não será enviado e-mail (fichaPdfAndSend)', ['cliente_id' => $cliente->id]);
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar ficha por email (fichaPdfAndSend)', ['erro' => $e->getMessage()]);
        }

        // Return download (behaves like CobrancaController::comprovante)
        try {
            if (isset($pdf) && method_exists($pdf, 'download')) {
                return $pdf->download($filename);
            }
        } catch (\Exception $e) {
            // ignore and fall through to stream download
        }

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Length' => strlen($output),
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->streamDownload(function () use ($output) {
            echo $output;
        }, $filename, $headers);
    }
    public function store(Request $request)
    {
        \Log::info('Entrou no método store do ClienteController', ['request' => $request->all()]);
        try {
            $validated = $request->validate([
                'bi_tipo' => 'required|string|in:BI,NIF,Outro',
                'bi_numero' => 'required|string|max:64',
                'bi_tipo_outro' => 'nullable|string|max:64',
                'nome' => 'required|string|max:255',
                'email' => 'required|email|unique:clientes,email',
                'contato' => 'required|string|max:20|unique:clientes,contato',
            ], [
                'bi_tipo.required' => 'Por favor selecione o tipo de documento.',
                'bi_numero.required' => 'Por favor preencha o número do BI/NIF.',
                'nome.required' => 'Por favor preencha o nome do cliente.',
                'email.required' => 'Por favor preencha o email do cliente.',
                'contato.required' => 'Por favor preencha o contato do cliente.',
            ]);

            $biValue = $validated['bi_numero'];
            if (isset($validated['bi_tipo']) && $validated['bi_tipo'] === 'Outro' && !empty($validated['bi_tipo_outro'])) {
                $biValue = $validated['bi_tipo_outro'] . ':' . $validated['bi_numero'];
            }

            $data = [
                'bi' => $biValue,
                'nome' => $validated['nome'],
                'email' => $validated['email'],
                'contato' => $validated['contato'],
            ];

            // Use transaction to ensure atomicity
            $cliente = null;
            \DB::beginTransaction();
            try {
                $cliente = Cliente::create($data);
                \Log::info('Cliente cadastrado', ['cliente' => $cliente]);
                \DB::commit();
            } catch (\Exception $e) {
                \DB::rollBack();
                throw $e;
            }

            // Se a requisição esperar JSON (AJAX/fetch), devolve JSON com URL de redirecionamento
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('clientes'),
                    'message' => 'Cliente cadastrado com sucesso!'
                ]);
            }

            // Redireciona para a página de clientes com mensagem de sucesso (form tradicional)
            return redirect()->route('clientes')->with('success', 'Cliente cadastrado com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors();
            // Se for erro de duplicidade, mostra mensagem amigável
            $mensagem = null;
            if ($errors->has('email')) {
                $mensagem = 'Este e-mail já está cadastrado.';
            } elseif ($errors->has('contato')) {
                $mensagem = 'Este contato já está cadastrado.';
            }
            // Retorna para a tela de cadastro com os erros
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => $errors->messages(), 'message' => $mensagem ?? 'Erro ao cadastrar cliente. Verifique os campos.'], 422);
            }
            return redirect()->back()
                ->withErrors($errors->messages())
                ->withInput()
                ->with('error', $mensagem ?? 'Erro ao cadastrar cliente. Verifique os campos.');
        } catch (\Exception $e) {
            // Erro inesperado
            \Log::error('Erro ao cadastrar cliente', ['exception' => $e]);
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Erro inesperado ao cadastrar cliente.'], 500);
            }
            return redirect()->route('clientes')->with('error', 'Erro inesperado ao cadastrar cliente.');
        }
    }

    public function update(Request $request, $id)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            // Support legacy split BI fields and a simple single `bi` field used by the inline edit form
            'bi' => 'sometimes|string|max:128',
            'bi_tipo' => 'sometimes|required_with:bi_numero|string|in:BI,NIF,Outro',
            'bi_numero' => 'sometimes|required_with:bi_tipo|string|max:64',
            'bi_tipo_outro' => 'nullable|string|max:64',
            'nome' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|email|unique:clientes,email,' . $id,
            'contato' => 'sometimes|required|string|max:20|unique:clientes,contato,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->messages()], 422);
        }

        $data = $validator->validated();

        // If the client sent a single `bi` field (inline edit), validate uniqueness and use it.
        if (isset($data['bi'])) {
            $biValue = trim($data['bi']);
            $exists = Cliente::where('bi', $biValue)->where('id', '!=', $id)->exists();
            if ($exists) {
                return response()->json(['success' => false, 'errors' => ['bi' => ['Já existe um cliente cadastrado com este documento.']]], 422);
            }
            $data['bi'] = $biValue;
        } elseif (isset($data['bi_tipo']) && isset($data['bi_numero'])) {
            $biValue = $data['bi_numero'];
            if ($data['bi_tipo'] === 'Outro' && !empty($data['bi_tipo_outro'])) {
                $biValue = $data['bi_tipo_outro'] . ':' . $data['bi_numero'];
            }
            $exists = Cliente::where('bi', $biValue)->where('id', '!=', $id)->exists();
            if ($exists) {
                return response()->json(['success' => false, 'errors' => ['bi_numero' => ['Já existe um cliente cadastrado com este documento.']]], 422);
            }
            $data['bi'] = $biValue;
        }

        // Only keep the allowed updatable fields for update
        $updatable = array_intersect_key($data, array_flip(['bi','nome','email','contato']));

        $cliente = Cliente::findOrFail($id);
        $cliente->update($updatable);

        return response()->json(['success' => true, 'cliente' => $cliente]);
    }

    public function destroy(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        // legacy deletion audits removed — no-op

        $cliente->delete();

        // respond appropriately for JSON/AJAX or form submit
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('clientes')->with('success', 'Cliente excluído com sucesso.');
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
