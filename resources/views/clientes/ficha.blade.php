@extends('layouts.app')

@section('content')
<div class="container">
    <style>
        @media print { .no-print { display: none !important; } }
        :root { --muted:#6b6b6b; --accent:#0d6efd; --soft:#f6f7fb; }
        .ficha-header { max-width:980px; margin:18px auto 6px; text-align:center; }
        .ficha-header .ficha-logo { display:block; margin:0 auto 6px; max-width:120px; height:auto; }
        .ficha-cliente { max-width:980px; margin:12px auto; }
        .cliente-dados-moderna { padding:18px 22px; }

        /* Card and header */
        .card { border:1px solid #e9ecef; border-radius:6px; overflow:hidden; background:#fff; }
        .card-header { background:#fbfbfd; color:#222; font-weight:700; padding:10px 14px; border-bottom:1px solid #eef0f3; }
        .card-body { padding:12px 14px; }

        /* Tables: fixed layout, readable spacing and wrapping */
        .table { width:100%; border-collapse:separate; border-spacing:0; table-layout:fixed; font-size:0.95rem; }
        .table th, .table td { padding:8px 10px; border:1px solid #f0f0f0; vertical-align:top; word-wrap:break-word; overflow-wrap:break-word; }
        .table thead th { background:var(--soft); font-weight:700; color:#222; }
        .table tbody tr td { background:#fff; }
        .table tbody tr:nth-child(odd) td { background:#fcfcfd; }

        /* Column helpers for better desktop widths */
        .col-id { width:6%; }
        .col-nome { width:28%; }
        .col-modelo { width:16%; }
        .col-serie { width:15%; }
        .col-quant { width:8%; text-align:center; }
        .col-morada { width:27%; }

        .section-title { font-weight:700; margin:8px 0 10px; text-align:left; }
        .muted { color:var(--muted); font-size:0.9rem; }
        /* Badges */
        .badge-planos, .badge-cobrancas { display:inline-block; padding:4px 8px; border-radius:999px; font-size:0.85rem; color:#fff; }
        .badge-planos { background:#0d6efd; }
        .badge-cobrancas { background:#dc3545; }
        .badge-cobrancas.pago { background:#28a745; }
        .badge-cobrancas.pendente { background:#ffc107; color:#222; }
    </style>
    {{-- Toolbar com ações acima do cartão (não aparece na impressão) --}}
    <div class="ficha-toolbar no-print" style="max-width:980px;margin:0 auto 12px;">
        <div style="display:flex;gap:10px;flex-direction:column;">
            <!-- Primary action replaced by a back button -->
            <a id="back-dashboard-btn" href="{{ url('/dashboard') }}" class="btn btn-secondary" style="padding:14px 18px; font-size:1.05rem; border-radius:8px; width:100%;">Voltar ao Dashboard</a>

            <!-- Compact secondary actions -->
            <div style="display:flex;gap:8px;align-items:center;justify-content:space-between;">
                <div style="display:flex;gap:8px;">
                    <button id="more-actions-toggle" class="btn btn-outline-secondary" style="padding:8px 12px;border-radius:8px;">Mais ações ▾</button>
                    <a href="#" id="download-ficha-inline" class="btn btn-light" style="padding:8px 12px;border-radius:8px;display:none;">Download sessão</a>
                </div>
                <form id="ficha-send-form" action="{{ route('clientes.ficha.send', $cliente->id) }}" method="post" style="display:inline-block;margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-success" style="padding:8px 12px;border-radius:8px;">Enviar por e‑mail</button>
                </form>
            </div>

            <!-- Hidden original authenticated download button (kept for JS handler) -->
            <button id="download-ficha-btn" data-url="{{ route('clientes.ficha.pdf', $cliente->id) }}" style="display:none;">AuthDownload</button>
        </div>
    </div>

    {{-- Cabeçalho da ficha com logotipo --}}
    <div class="ficha-header" style="max-width:900px;margin:12px auto 0;text-align:center;">
        <img src="{{ asset('img/logo2.jpeg') }}" alt="Logotipo" class="ficha-logo" style="max-width:120px;height:auto;display:block;margin:0 auto 8px;">
        <h4 style="margin-top:8px;">Ficha do Cliente</h4>
        <p class="mb-0">Emitido: {{ now()->toDateString() }}</p>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Dados do Cliente</div>
                <div class="card-body">
                    {{-- ID removido da ficha conforme solicitado --}}
                    <p><strong>Nome / Razão social:</strong> {{ $cliente->nome }}</p>
                    <p><strong>BI / NIF:</strong> {{ $cliente->bi }}</p>
                    <p><strong>Contacto (WhatsApp):</strong> {{ $cliente->contato }}</p>
                    <p><strong>Email:</strong> {{ $cliente->email }}</p>
                    <p><strong>Estado:</strong> {{ $cliente->estado ?? '—' }}</p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Observações</div>
                <div class="card-body">
                    <p>{!! nl2br(e($cliente->observacoes ?? '')) !!}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Equipamentos Associados</div>
                <div class="card-body p-0">
                    @if((isset($cliente->equipamentos) && $cliente->equipamentos->count()) || (isset($cliente->clienteEquipamentos) && $cliente->clienteEquipamentos->count()))
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th class="col-id">#</th>
                                <th class="col-nome">Nome</th>
                                <th class="col-modelo">Modelo</th>
                                <th class="col-serie">Série</th>
                                <th class="col-quant">Quantidade</th>
                                <th class="col-morada">Morada / Referência</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cliente->equipamentos ?? [] as $eq)
                                <tr>
                                    <td class="col-id">{{ $eq->id }}</td>
                                    <td class="col-nome">{{ $eq->nome ?? '-' }}</td>
                                    <td class="col-modelo">{{ $eq->modelo ?? '-' }}</td>
                                    <td class="col-serie">{{ $eq->numero_serie ?? '-' }}</td>
                                    <td class="col-quant">{{ $eq->quantidade ?? '1' }}</td>
                                    <td class="col-morada">{{ $eq->ponto_referencia ?? $eq->morada ?? '-' }}</td>
                                </tr>
                            @endforeach

                            @foreach($cliente->clienteEquipamentos ?? [] as $vinc)
                                @php $est = $vinc->equipamento; @endphp
                                <tr>
                                    <td class="col-id">{{ $vinc->id }}</td>
                                    <td class="col-nome">{{ $est->nome ?? '-' }}</td>
                                    <td class="col-modelo">{{ $est->modelo ?? '-' }}</td>
                                    <td class="col-serie">{{ $est->numero_serie ?? '-' }}</td>
                                    <td class="col-quant">{{ $vinc->quantidade ?? '1' }}</td>
                                    <td class="col-morada">{{ $vinc->morada ?? '—' }}{{ $vinc->ponto_referencia ? ' (Ref: '.$vinc->ponto_referencia.')' : '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                        <p class="p-3 mb-0">Nenhum equipamento cadastrado para este cliente.</p>
                    @endif
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">Planos Contratados</div>
                <div class="card-body p-0">
                    @if(isset($cliente->planos) && $cliente->planos->count())
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nome do Plano</th>
                                    <th>Data Ativação</th>
                                    <th>Ciclo (dias)</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cliente->planos as $pl)
                                    <tr>
                                        <td>{{ $pl->id }}</td>
                                        <td>{{ $pl->nome ?? '-' }}</td>
                                        <td>{{ $pl->data_ativacao ? \Carbon\Carbon::parse($pl->data_ativacao)->format('d/m/Y') : 'Sem data' }}</td>
                                        <td>{{ $pl->ciclo ?? '-' }}</td>
                                        <td><span class="badge-planos">{{ $pl->estado ?? '-' }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="p-3 mb-0">Nenhum plano contratado.</p>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Cobranças (pendentes / recentes)</div>
                <div class="card-body p-0">
                    @if($cliente->cobrancas && $cliente->cobrancas->count())
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Descrição</th>
                                <th>Valor</th>
                                <th>Vencimento</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cliente->cobrancas as $c)
                            <tr>
                                <td>{{ $c->id }}</td>
                                <td>{{ $c->descricao ?? '-' }}</td>
                                <td>{{ number_format($c->valor, 2, ',', '.') }} Kz</td>
                                <td>{{ $c->data_vencimento ? \Carbon\Carbon::parse($c->data_vencimento)->format('d/m/Y') : 'Sem data' }}</td>
                                <td>{{ $c->estado ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                        <p class="p-3 mb-0">Sem cobranças associadas.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const btn = document.getElementById('download-ficha-btn');
    const btnSigned = document.getElementById('download-ficha-signed-btn');
    const moreToggle = document.getElementById('more-actions-toggle');
    const inlineDownloadLink = document.getElementById('download-ficha-inline');
    if (!btn) return;
    btn.addEventListener('click', function(e){
        e.preventDefault();
        const url = btn.dataset.url;
        btn.disabled = true;
        fetch(url, { credentials: 'same-origin' })
            .then(resp => {
                if (!resp.ok) {
                    // if server returned HTML (login) redirect user to login
                    const ct = resp.headers.get('content-type') || '';
                    if (ct.indexOf('text/html') !== -1) {
                        window.location = '/login';
                        throw new Error('not-authenticated');
                    }
                    throw new Error('HTTP ' + resp.status);
                }
                return resp.blob();
            })
            .then(blob => {
                // Open generated PDF blob in a new tab so browser shows it inline
                const blobUrl = URL.createObjectURL(blob);
                window.open(blobUrl, '_blank');
                // schedule revoke after a short delay to allow tab to load
                setTimeout(() => URL.revokeObjectURL(blobUrl), 5000);
            })
            .catch(err => {
                if (err && err.message !== 'not-authenticated') alert('Erro ao baixar PDF: ' + err.message);
            })
            .finally(() => { btn.disabled = false; });
    });
    // Signed-URL button removed — no signed-download handler

    // More actions toggle: show inline download (authenticated) when clicked
    if (moreToggle) {
        moreToggle.addEventListener('click', function(e){
            e.preventDefault();
            if (inlineDownloadLink.style.display === 'none' || inlineDownloadLink.style.display === '') {
                inlineDownloadLink.style.display = 'inline-block';
                moreToggle.textContent = 'Mais ações ▴';
                // attach handler to inline link (uses same auth download logic)
                inlineDownloadLink.onclick = function(ev){ ev.preventDefault(); document.getElementById('download-ficha-btn').click(); };
            } else {
                inlineDownloadLink.style.display = 'none';
                moreToggle.textContent = 'Mais ações ▾';
            }
        });
    }
});
</script>
@endpush
