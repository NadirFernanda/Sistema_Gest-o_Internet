@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px; margin: 40px auto;">
    <h2>Editar Vínculo de Equipamento</h2>
    <div style="margin-bottom: 10px;"><strong>BI do Cliente:</strong> {{ $cliente->bi ?? '-' }}<br><strong>Nome:</strong> {{ $cliente->nome }}</div>
    {{-- back button removed from header area --}}
    <form action="{{ route('cliente_equipamento.update', [$cliente->id, $vinculo->id]) }}" method="POST" style="margin-top: 20px;">
        @csrf
        @method('PUT')
        <div class="form-group-custom">
            <label for="estoque_equipamento_id" class="form-label">Selecione o Equipamento</label>
            <select class="form-control select" id="estoque_equipamento_id" name="estoque_equipamento_id" required data-no-choices="1">
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
        <div class="form-group-custom">
            <label for="forma_ligacao" class="form-label">Forma de Ligação</label>
            <select id="forma_ligacao" name="forma_ligacao" class="form-control" required>
                <option value="">-- Escolha forma de ligação --</option>
                <option value="Ponto a Ponto" {{ old('forma_ligacao', $vinculo->forma_ligacao) == 'Ponto a Ponto' ? 'selected' : '' }}>Ponto a Ponto</option>
                <option value="Multiponto" {{ old('forma_ligacao', $vinculo->forma_ligacao) == 'Multiponto' ? 'selected' : '' }}>Multiponto</option>
                <option value="Fibra" {{ old('forma_ligacao', $vinculo->forma_ligacao) == 'Fibra' ? 'selected' : '' }}>Fibra</option>
                <option value="V-Sat" {{ old('forma_ligacao', $vinculo->forma_ligacao) == 'V-Sat' ? 'selected' : '' }}>V-Sat</option>
            </select>
            @if ($errors->has('forma_ligacao'))
                <div class="text-danger small">{{ $errors->first('forma_ligacao') }}</div>
            @endif
        </div>
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </form>
</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- jQuery must load before Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
