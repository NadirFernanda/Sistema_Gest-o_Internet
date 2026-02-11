@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Toolbar com ações acima do cartão (não aparece na impressão) --}}
    <div class="ficha-toolbar no-print">
        <a href="{{ route('clientes.ficha.pdf', $cliente->id) }}" class="btn btn-sm btn-secondary">Download PDF</a>
        <form action="{{ route('clientes.ficha.send', $cliente->id) }}" method="post" style="display:inline;">
            @csrf
            <button class="btn btn-sm btn-primary">Enviar por e-mail</button>
        </form>
    </div>

    {{-- Cabeçalho da ficha com logotipo --}}
    <div class="ficha-header" style="max-width:900px;margin:12px auto 0;text-align:center;">
        <img src="{{ asset('img/logo2.jpeg') }}" alt="Logotipo" class="ficha-logo">
        <h4 style="margin-top:8px;">Ficha do Cliente</h4>
        <p class="mb-0">Emitido: {{ now()->toDateString() }}</p>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Dados do Cliente</div>
                <div class="card-body">
                    <p><strong>ID:</strong> {{ $cliente->id }}</p>
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
                                <th>Nome / Modelo</th>
                                <th>Série</th>
                                <th>Quantidade / Morada</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cliente->equipamentos ?? [] as $eq)
                            <tr>
                                <td>{{ $eq->id }}</td>
                                <td>{{ $eq->nome ?? $eq->modelo ?? '-' }}</td>
                                <td>{{ $eq->numero_serie ?? '-' }}</td>
                                <td>{{ $eq->ponto_referencia ?? $eq->morada ?? '-' }}</td>
                            </tr>
                            @endforeach

                            @foreach($cliente->clienteEquipamentos ?? [] as $vinc)
                                @php $est = $vinc->equipamento; @endphp
                                <tr>
                                    <td>{{ $vinc->id }}</td>
                                    <td>{{ $est->nome ?? $est->modelo ?? 'Equipamento do estoque' }}</td>
                                    <td>{{ $est->numero_serie ?? '-' }}</td>
                                    <td>{{ $vinc->quantidade }}x • {{ $vinc->morada }}{{ $vinc->ponto_referencia ? ' (Ref: '.$vinc->ponto_referencia.')' : '' }}</td>
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
                                        <td>{{ optional($pl->data_ativacao)->toDateString() ?? '-' }}</td>
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
                                <td>{{ optional($c->data_vencimento)->toDateString() }}</td>
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
