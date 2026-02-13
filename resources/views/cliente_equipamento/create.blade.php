@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 900px; margin: 40px auto;">
    <div class="back-wrap">
    <a href="{{ route('clientes.show', $cliente->id) }}" class="btn-back">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;margin-right:6px;">
            <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Voltar à Ficha do Cliente
    </a>
    </div>
    <h2>Vincular Equipamento ao Cliente</h2>
    <div style="margin-bottom: 10px;"><strong>BI do Cliente:</strong> {{ $cliente->bi ?? '-' }}<br><strong>Nome:</strong> {{ $cliente->nome }}</div>
<form action="{{ route('cliente_equipamento.store', $cliente->id) }}" method="POST" style="margin-top: 20px;">
        <style>
            .form-label {
                font-weight: 500;
                margin-bottom: 6px;
            }
            .required-asterisk {
                color: red;
                margin-left: 2px;
            }
            .form-control {
                min-height: 40px;
                font-size: 1rem;
            }
            .select2-container .select2-selection--single {
                height: 40px;
                padding: 6px 12px;
                font-size: 1rem;
            }
        </style>
        @csrf
        <div class="form-group-custom">
            <label for="estoque_equipamento_id" class="form-label">Selecione o Equipamento <span class="required-asterisk">*</span></label>
            <select class="form-control" id="estoque_equipamento_id" name="estoque_equipamento_id" style="width:100%">
                <option value="">-- Escolha um equipamento --</option>
                @foreach($equipamentos as $equipamento)
                    <option value="{{ $equipamento->id }}">{{ $equipamento->nome }} ({{ $equipamento->modelo }})</option>
                @endforeach
            </select>
            @if ($errors->has('estoque_equipamento_id'))
                @php
                    $msg = $errors->first('estoque_equipamento_id');
                @endphp
                @if (str_contains($msg, 'já foi vinculado a esse cliente') || str_contains($msg, 'já foi vinculado a este cliente'))
                    @php $equipamentoDuplicadoMsg = $msg; @endphp
                @else
                    <div class="text-danger small">{{ $msg }}</div>
                @endif
            @endif
        </div>
        <div class="form-group-custom">
            <label for="quantidade" class="form-label">Quantidade <span class="required-asterisk">*</span></label>
            <input type="number" class="form-control" id="quantidade" name="quantidade" min="1" value="1">
            @if ($errors->has('quantidade'))
                <div class="text-danger small">{{ $errors->first('quantidade', 'Informe uma quantidade válida.') }}</div>
            @endif
        </div>
        <div class="form-group-custom">
            <label for="morada" class="form-label">Morada <span class="required-asterisk">*</span></label>
            <input type="text" class="form-control" id="morada" name="morada">
            @if ($errors->has('morada'))
                <div class="text-danger small">{{ $errors->first('morada', 'Informe a morada.') }}</div>
            @endif
        </div>
        <div class="form-group-custom">
            <label for="ponto_referencia" class="form-label">Ponto de Referência <span class="required-asterisk">*</span></label>
            <input type="text" class="form-control" id="ponto_referencia" name="ponto_referencia">
            @if ($errors->has('ponto_referencia'))
                <div class="text-danger small">{{ $errors->first('ponto_referencia', 'Informe o ponto de referência.') }}</div>
            @endif
        </div>
        <button type="submit" class="btn btn-primary">Vincular Equipamento</button>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#estoque_equipamento_id').select2({
                placeholder: '-- Escolha um equipamento --',
                allowClear: true,
                width: 'resolve',
                language: {
                    noResults: function() {
                        return 'Nenhum resultado encontrado';
                    }
                }
            });
        });
    </script>

@if(isset($equipamentoDuplicadoMsg))
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var msg = @json($equipamentoDuplicadoMsg);
            alert(msg);
        });
    </script>
    @endpush
@endif
    </form>
    <hr>
    <h3>Equipamentos já vinculados a {{ $cliente->nome }}</h3>
    @if($vinculados->count())
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Modelo</th>
                    <th>Morada</th>
                    <th>Ponto de Referência</th>
                    <th>Quantidade</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vinculados as $v)
                    <tr>
                        <td>{{ $v->equipamento->nome }}</td>
                        <td>{{ $v->equipamento->modelo }}</td>
                        <td>{{ $v->morada }}</td>
                        <td>{{ $v->ponto_referencia }}</td>
                        <td>{{ $v->quantidade ?? 1 }}</td>
                        <td>
                            <a href="{{ route('cliente_equipamento.edit', [$cliente->id, $v->id]) }}" class="btn-icon btn-warning" title="Editar" aria-label="Editar">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                            </a>
                            <form action="{{ route('cliente_equipamento.destroy', [$cliente->id, $v->id]) }}" method="POST" style="display:inline-block; margin-left:6px;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon btn-danger" title="Eliminar" aria-label="Eliminar" onclick="return confirm('Tem certeza que deseja remover este equipamento?')">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Nenhum equipamento vinculado a este cliente.</p>
    @endif
</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#estoque_equipamento_id').select2({
            placeholder: 'Pesquise ou selecione um equipamento',
            allowClear: true,
            width: 'resolve'
        });
    });
</script>
@endpush
