@extends('layouts.app')

@section('content')
<div class="container">
    <style>
        @media print { .no-print { display: none !important; } }
        .ficha-header { max-width:900px; margin:12px auto 0; text-align:center; }
        .ficha-header .ficha-logo { display:block; margin:0 auto 8px; max-width:120px; height:auto; }
        .ficha-cliente { max-width:900px; margin:12px auto; }
        .cliente-dados-moderna { padding:18px 24px; }
        .ficha-equip-table table, .ficha-plano-table table { width:100%; border-collapse:separate; border-spacing:0; }
        .ficha-equip-table th, .ficha-equip-table td, .ficha-plano-table th, .ficha-plano-table td { padding:8px 10px; border:1px solid #eee; vertical-align:top; }
        .ficha-equip-table thead th, .ficha-plano-table thead th { background:#fff9e6; font-weight:700; }
        .ficha-equip-table td, .ficha-plano-table td { font-size:0.95rem; }
        .section-title { font-weight:700; margin:10px 0; text-align:center; }
    </style>
    {{-- Toolbar com ações acima do cartão (não aparece na impressão) --}}
    <div class="ficha-toolbar no-print">
        <button id="download-ficha-btn" data-url="{{ route('clientes.ficha.pdf', $cliente->id) }}" class="btn btn-sm btn-secondary">Download PDF</button>
        <button id="download-ficha-signed-btn" class="btn btn-sm btn-info">Download (signed URL)</button>
        <form id="ficha-send-form" action="{{ route('clientes.ficha.send', $cliente->id) }}" method="post" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-sm btn-primary">Enviar por e-mail</button>
        </form>
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
                                <th>#</th>
                                <th>Nome</th>
                                <th>Modelo</th>
                                <th>Série</th>
                                <th>Quantidade</th>
                                <th>Morada / Referência</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cliente->equipamentos ?? [] as $eq)
                            <tr>
                                <td>{{ $eq->id }}</td>
                                <td>{{ $eq->nome ?? '-' }}</td>
                                <td>{{ $eq->modelo ?? '-' }}</td>
                                <td>{{ $eq->numero_serie ?? '-' }}</td>
                                <td>{{ $eq->quantidade ?? '1' }}</td>
                                <td>{{ $eq->ponto_referencia ?? $eq->morada ?? '-' }}</td>
                            </tr>
                            @endforeach

                            @foreach($cliente->clienteEquipamentos ?? [] as $vinc)
                                @php $est = $vinc->equipamento; @endphp
                                <tr>
                                    <td>{{ $vinc->id }}</td>
                                    <td>{{ $est->nome ?? '-' }}</td>
                                    <td>{{ $est->modelo ?? '-' }}</td>
                                    <td>{{ $est->numero_serie ?? '-' }}</td>
                                    <td>{{ $vinc->quantidade ?? '1' }}</td>
                                    <td>{{ $vinc->morada ?? '—' }}{{ $vinc->ponto_referencia ? ' (Ref: '.$vinc->ponto_referencia.')' : '' }}</td>
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
                                        <td>{{ $pl->estado ?? '-' }}</td>
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
    if (btnSigned) {
        btnSigned.addEventListener('click', function(e){
            e.preventDefault();
            btnSigned.disabled = true;
            fetch(`/clientes/{{ $cliente->id }}/ficha/signed-url`, { credentials: 'same-origin' })
                .then(r => {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(json => {
                    if (json.url) {
                        // fetch signed URL and open PDF blob to avoid landing on HTML/login pages
                        return fetch(json.url).then(r => {
                            if (!r.ok) throw new Error('HTTP ' + r.status);
                            const ct = r.headers.get('content-type') || '';
                            if (ct.indexOf('application/pdf') === -1) {
                                // server returned HTML or error page
                                return r.text().then(txt => { throw new Error('unexpected content'); });
                            }
                            return r.blob();
                        }).then(blob => {
                            const blobUrl = URL.createObjectURL(blob);
                            window.open(blobUrl, '_blank');
                            setTimeout(() => URL.revokeObjectURL(blobUrl), 5000);
                        });
                    } else {
                        alert('Não foi possível gerar a URL assinada');
                    }
                })
                .catch(err => alert('Erro ao solicitar URL assinada: ' + err.message))
                .finally(() => btnSigned.disabled = false);
        });
    }
});
</script>
@endpush
