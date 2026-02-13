@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 900px; margin: 40px auto;">
    
    <div class="back-wrap">
    <a href="{{ route('clientes.show', $cliente->id) }}" class="btn-back" aria-label="Voltar à Ficha do Cliente" title="Voltar à Ficha do Cliente" style="position:relative;left:auto;transform:none;margin-top:0;width:44px;height:44px;padding:0;border-radius:50%;background:#f7b500;color:#ffffff;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border:none;box-shadow:0 10px 28px rgba(224,161,1,0.18);">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;stroke:currentColor;">
            <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
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
            /* Custom Select2 highlight: project yellow */
            .select2-container .select2-results__option--highlighted,
            .select2-container--open .select2-results__option--highlighted,
            .select2-container .select2-results__option[aria-selected="true"] {
                background-color: #f7b500 !important;
                color: #111827 !important;
            }
            /* Ensure hovered option also shows the same style */
            .select2-container .select2-results__option:hover {
                background-color: #f7b500 !important;
                color: #111827 !important;
            }
        </style>
        @csrf
        <div class="form-group-custom">
            <label for="estoque_equipamento_id" class="form-label">Selecione o Equipamento <span class="required-asterisk">*</span></label>
            <select class="form-control" id="estoque_equipamento_id" name="estoque_equipamento_id" style="width:100%">
                <option value="">-- Escolha um equipamento --</option>
                @foreach($equipamentos as $equipamento)
                    <option value="{{ $equipamento->id }}" data-quantidade="{{ $equipamento->quantidade }}">{{ $equipamento->nome }} ({{ $equipamento->modelo }}) - em estoque: {{ $equipamento->quantidade }}</option>
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
            <div id="quantidade-error" class="text-danger small" style="display:none; margin-top:6px;"></div>
            <div id="estoque-info" role="status" aria-live="polite" style="margin-top:10px;">
                <div style="background:#fff8e1;border-left:4px solid #f7b500;padding:10px 12px;border-radius:8px;color:#374151;font-weight:600;">Quantidade disponível em estoque: <span id="estoque-quant">-</span></div>
            </div>
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
        <div class="form-actions" style="margin-top:16px; display:flex; gap:8px; align-items:center;">
            <button type="submit" class="btn btn-primary">Vou vincular</button>
            <a href="{{ route('clientes.show', $cliente->id) }}" class="btn btn-secondary">Cancelar</a>
        </div>
    <!-- moved JS to scripts stack so it runs after jQuery/Select2 load -->

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

<!-- jQuery must load before Select2 and before scripts that use $ -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>if(typeof jQuery === 'undefined'){console.error('jQuery failed to load — check CDN or network/integrity policy.');}</script>

<script>
jQuery(function($){
    function updateEstoqueInfo() {
        var opt = $('#estoque_equipamento_id option:selected');
        try { console.log('[updateEstoqueInfo] selected val:', opt.val(), 'optionCount:', opt.length); } catch(e){}

        if (!opt.length || !opt.val()) {
            $('#estoque-quant').text('-');
            $('#quantidade').removeAttr('max');
            return;
        }

        var avail = parseInt(opt.data('quantidade'), 10) || 0;
        try { console.log('[updateEstoqueInfo] avail:', avail); } catch(e){}

        $('#estoque-quant').text(avail);
        $('#quantidade').attr('max', avail);

        var cur = parseInt($('#quantidade').val(), 10) || 0;
        try { console.log('[updateEstoqueInfo] cur:', cur); } catch(e){}

        if (cur > avail) {
            $('#quantidade').val(avail);
            $('#quantidade-error')
                .text('A quantidade foi ajustada para a disponibilidade em estoque.')
                .show();

            var el = $('#quantidade')[0];
            if (el) {
                el.setCustomValidity('A quantidade não pode ser maior que ' + avail + '.');
            }

        } else {
            $('#quantidade-error').hide();
            var el = $('#quantidade')[0];
            if (el) { el.setCustomValidity(''); }
        }
    }

    // expose for external callers
    window.updateEstoqueInfo = updateEstoqueInfo;

    var sel = $('#estoque_equipamento_id');
    sel.select2({
        placeholder: 'Pesquise ou selecione um equipamento',
        allowClear: true,
        width: '100%'
    });

    // call initial update (in case select had value)
    if(window.updateEstoqueInfo){ window.updateEstoqueInfo(); }

    // listen to Select2-specific events and normal change
    sel.on('select2:select select2:unselect change', function(){
        if(window.updateEstoqueInfo){ window.updateEstoqueInfo(); }
    });
    $(document).on('change', '#estoque_equipamento_id', updateEstoqueInfo);

    // HTML5 validation messages in Portuguese for #quantidade
    var quantidadeEl = document.getElementById('quantidade');
    if(quantidadeEl){
        quantidadeEl.addEventListener('input', function(){
            this.setCustomValidity('');
            var max = parseInt(this.getAttribute('max') || '0', 10);
            var val = parseInt(this.value || '0', 10);
            if(max > 0 && val > max){
                this.setCustomValidity('A quantidade não pode ser maior que ' + max + '.');
            } else if(val < 1){
                this.setCustomValidity('A quantidade mínima é 1.');
            } else {
                this.setCustomValidity('');
            }
        });
        quantidadeEl.addEventListener('invalid', function(e){
            var max = parseInt(this.getAttribute('max') || '0', 10);
            if(this.validity.rangeOverflow && max > 0){
                this.setCustomValidity('A quantidade não pode ser maior que ' + max + '.');
            } else if(this.validity.rangeUnderflow){
                this.setCustomValidity('A quantidade mínima é 1.');
            }
        });
    }
});
</script>
@endpush
