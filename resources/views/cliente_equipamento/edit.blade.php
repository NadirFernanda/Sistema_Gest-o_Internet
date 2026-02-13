@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px; margin: 40px auto;">
    <h2>Editar Vínculo de Equipamento</h2>
    <div style="margin-bottom: 10px;"><strong>BI do Cliente:</strong> {{ $cliente->bi ?? '-' }}<br><strong>Nome:</strong> {{ $cliente->nome }}</div>
    <a href="{{ route('cliente_equipamento.create', $cliente->id) }}" class="btn-back-circle btn-ghost" title="Voltar" aria-label="Voltar">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <form action="{{ route('cliente_equipamento.update', [$cliente->id, $vinculo->id]) }}" method="POST" style="margin-top: 20px;">
        @csrf
        @method('PUT')
        <div class="form-group-custom">
            <label for="estoque_equipamento_id" class="form-label">Selecione o Equipamento</label>
            <select class="form-control" id="estoque_equipamento_id" name="estoque_equipamento_id" required>
                <option value="">-- Escolha um equipamento --</option>
                @foreach($equipamentos as $equipamento)
                    <option value="{{ $equipamento->id }}" {{ $vinculo->estoque_equipamento_id == $equipamento->id ? 'selected' : '' }}>{{ $equipamento->nome }} ({{ $equipamento->modelo }})</option>
                @endforeach
            </select>
            @if ($errors->has('estoque_equipamento_id'))
                <div class="text-danger small">{{ $errors->first('estoque_equipamento_id', 'Selecione um equipamento.') }}</div>
            @endif
        </div>
        <div class="form-group-custom">
            <label for="quantidade" class="form-label">Quantidade</label>
            <input type="number" class="form-control" id="quantidade" name="quantidade" min="1" value="{{ old('quantidade', $vinculo->quantidade ?? 1) }}" required>
            @if ($errors->has('quantidade'))
                <div class="text-danger small">{{ $errors->first('quantidade', 'Informe uma quantidade válida.') }}</div>
            @endif
        </div>
        <div class="form-group-custom">
            <label for="morada" class="form-label">Morada</label>
            <input type="text" class="form-control" id="morada" name="morada" value="{{ old('morada', $vinculo->morada) }}" required>
            @if ($errors->has('morada'))
                <div class="text-danger small">{{ $errors->first('morada', 'Informe a morada.') }}</div>
            @endif
        </div>
        <div class="form-group-custom">
            <label for="ponto_referencia" class="form-label">Ponto de Referência</label>
            <input type="text" class="form-control" id="ponto_referencia" name="ponto_referencia" value="{{ old('ponto_referencia', $vinculo->ponto_referencia) }}" required>
            @if ($errors->has('ponto_referencia'))
                <div class="text-danger small">{{ $errors->first('ponto_referencia', 'Informe o ponto de referência.') }}</div>
            @endif
        </div>
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </form>
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
            width: 'resolve',
            language: {
                noResults: function() {
                    return 'Nenhum resultado encontrado';
                }
            }
        });
    });
</script>
@endpush
