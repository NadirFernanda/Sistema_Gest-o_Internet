@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
    <style>
        .tk-form label { font-size:0.88rem; font-weight:600; color:#444; display:block; margin-bottom:4px; }
        .tk-form .form-control { width:100%; }
        .tk-form textarea.form-control { resize:vertical; min-height:120px; }
        select.form-control { height:40px; }
    </style>
@endpush

@section('content')
<div class="estoque-container-moderna">
    <div style="max-width:680px; margin:24px auto 0;">

        <div style="display:flex; align-items:center; gap:12px; margin-bottom:18px;">
            <a href="{{ route('tickets.index') }}" class="btn btn-ghost" style="height:36px; display:inline-flex; align-items:center; gap:6px; font-size:0.9rem;">
                ← Voltar
            </a>
            <div>
                <h2 style="margin:0; font-size:1.2rem; font-weight:700;">Novo Ticket</h2>
                <p style="margin:0; font-size:0.85rem; color:#888;">Registar pedido ou ocorrência de um cliente</p>
            </div>
        </div>

        <div style="background:#fff; border-radius:12px; padding:28px 32px; box-shadow:0 4px 14px rgba(0,0,0,0.07);">

            @if($errors->any())
            <div style="background:#fdecea; border-left:4px solid #e05a4f; padding:10px 14px; border-radius:6px; margin-bottom:16px; font-size:0.9rem; color:#c0392b;">
                @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('tickets.store') }}" class="tk-form">
                @csrf

                <div style="display:grid; gap:16px;">

                    <div>
                        <label>Cliente</label>
                        <select name="cliente_id" class="form-control">
                            <option value="">— Sem cliente associado —</option>
                            @foreach($clientes as $c)
                            <option value="{{ $c->id }}" {{ (old('cliente_id', $clienteId) == $c->id) ? 'selected' : '' }}>
                                {{ $c->nome }} @if($c->contato) — {{ $c->contato }} @endif
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Assunto *</label>
                        <input type="text" name="assunto" value="{{ old('assunto') }}" class="form-control"
                               placeholder="Descreva o problema resumidamente" required>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label>Categoria *</label>
                            <select name="categoria" class="form-control" required>
                                @foreach(['Técnico','Cobrança','Equipamento','Plano','Outro'] as $cat)
                                <option value="{{ $cat }}" {{ old('categoria') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>Prioridade *</label>
                            <select name="prioridade" class="form-control" required>
                                @foreach(['Baixa','Normal','Alta','Urgente'] as $pri)
                                <option value="{{ $pri }}" {{ old('prioridade', 'Normal') === $pri ? 'selected' : '' }}>{{ $pri }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label>Descrição / Primeira mensagem *</label>
                        <textarea name="mensagem" class="form-control" required
                                  placeholder="Descreva detalhadamente o problema ou pedido do cliente…">{{ old('mensagem') }}</textarea>
                    </div>

                    <div style="display:flex; gap:10px; margin-top:4px;">
                        <button type="submit" class="btn btn-cta" style="height:40px;">Criar Ticket</button>
                        <a href="{{ route('tickets.index') }}" class="btn btn-ghost" style="height:40px; display:inline-flex; align-items:center;">Cancelar</a>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
@endsection
