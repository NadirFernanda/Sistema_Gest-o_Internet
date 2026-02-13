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
            <div id="estoque-info" class="muted small" style="margin-top:6px;">Quantidade disponível: <span id="estoque-quant">-</span></div>
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

            function updateEstoqueInfo(){
                var sel = $('#estoque_equipamento_id');
                var opt = sel.find(':selected');
                var avail = opt.data('quantidade');
                if(!avail && avail !== 0){
                    $('#estoque-quant').text('-');
                    $('#quantidade').removeAttr('max');
                    return;
                }
                $('#estoque-quant').text(avail);
                $('#quantidade').attr('max', avail);
                // if current value > available, reduce it
                var cur = parseInt($('#quantidade').val() || '0', 10);
                if(cur > avail){
                    $('#quantidade').val(avail);
                    $('#quantidade-error').text('A quantidade foi ajustada para a disponibilidade em estoque.').show();
                } else {
                    $('#quantidade-error').hide();
                }
            }

            // on open/select change
            $('#estoque_equipamento_id').on('select2:select select2:clear change', function(){ updateEstoqueInfo(); });
            // initial
            updateEstoqueInfo();
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
<style>
    /* Inline override loaded after Select2 CSS to ensure project yellow is used */
    .select2-container--default .select2-results__option--highlighted,
    .select2-container--default .select2-results__option[aria-selected="true"] {
        background-color: var(--yellow-500) !important;
        color: #ffffff !important;
    }
    .select2-container--default .select2-results__option--highlighted:hover {
        background-color: var(--yellow-600) !important;
        color: #ffffff !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--gray-900); }
</style>
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
<script>
    // Fallback: ensure highlighted option is visibly styled for keyboard navigation
    (function(){
        function applyHighlightStyles(container) {
            var opts = container.querySelectorAll('.select2-results__option');
            opts.forEach(function(li){
                if (li.classList.contains('select2-results__option--highlighted') || li.getAttribute('aria-selected') === 'true') {
                    li.style.setProperty('background-color', '#f7b500', 'important');
                    li.style.setProperty('color', '#ffffff', 'important');
                    li.style.setProperty('box-shadow', 'inset 0 -2px 0 rgba(0,0,0,0.03), 0 6px 18px rgba(17,24,39,0.04)', 'important');
                } else {
                    li.style.removeProperty('background-color');
                    li.style.removeProperty('color');
                    li.style.removeProperty('box-shadow');
                }
            });
        }

        document.addEventListener('select2:open', function(e){
            // dropdown is appended to body; find nearest dropdown container
            var container = document.querySelector('.select2-container--open .select2-results');
            if (!container) return;
            applyHighlightStyles(container);

            // observe changes inside results (keyboard navigation/hover)
            var mo = new MutationObserver(function(){ applyHighlightStyles(container); });
            mo.observe(container, { attributes: true, childList: true, subtree: true });

            // when select2 closes, disconnect observer
            function closeHandler() { mo.disconnect(); document.removeEventListener('select2:closing', closeHandler); }
            document.addEventListener('select2:closing', closeHandler);
        });
    })();
</script>
<script>
    // Reinforced fallback: keep highlighted option visible and styled during keyboard navigation
    (function(){
        function ensureVisibleAndStyled(container){
            if(!container) return;
            var highlighted = container.querySelector('.select2-results__option--highlighted') || container.querySelector('[aria-selected="true"]');
            if(highlighted){
                try{
                    highlighted.style.setProperty('background-color', '#f7b500', 'important');
                    highlighted.style.setProperty('color', '#ffffff', 'important');
                    highlighted.style.setProperty('box-shadow', 'inset 0 -2px 0 rgba(0,0,0,0.03), 0 6px 18px rgba(17,24,39,0.04)', 'important');
                    // scroll into view if it's partially hidden
                    highlighted.scrollIntoView({ block: 'nearest', inline: 'nearest' });
                }catch(e){}
            }
        }

        document.addEventListener('select2:open', function(){
            var results = document.querySelector('.select2-container--open .select2-results');
            if(!results) return;

            // initial styling
            ensureVisibleAndStyled(results);

            // key navigation: listen on document for arrow keys while open
            function keyHandler(ev){
                if(ev.key === 'ArrowDown' || ev.key === 'ArrowUp'){
                    // slight delay to allow Select2 to update classes
                    setTimeout(function(){ ensureVisibleAndStyled(results); }, 10);
                }
            }

            // mouseover on options: style hovered option so it appears instead of disappearing
            function mouseHandler(e){
                var li = e.target.closest('.select2-results__option');
                if(!li) return;
                li.style.setProperty('background-color', '#f7b500', 'important');
                li.style.setProperty('color', '#ffffff', 'important');
            }

            document.addEventListener('keydown', keyHandler);
            results.addEventListener('mousemove', mouseHandler);

            function cleanup(){
                document.removeEventListener('keydown', keyHandler);
                results.removeEventListener('mousemove', mouseHandler);
                document.removeEventListener('select2:closing', cleanup);
            }
            document.addEventListener('select2:closing', cleanup);
        });
    })();
</script>
@endpush
