@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
    <style>
        .tk-header { max-width:900px; margin:20px auto 0; background:#fff; border-radius:12px;
                     padding:20px 24px; box-shadow:0 2px 12px rgba(0,0,0,.07); }
        .tk-meta   { display:flex; flex-wrap:wrap; gap:8px; align-items:center; margin-top:10px; }
        .tk-badge-lg { padding:4px 14px; border-radius:20px; font-size:0.82rem; font-weight:700; color:#fff; }
        .tk-pri-lg   { padding:4px 12px; border-radius:6px; font-size:0.8rem; font-weight:700; color:#fff; }
        .tk-cat      { padding:4px 12px; border-radius:6px; font-size:0.8rem; background:#f0f4f9; color:#555; font-weight:600; }

        .tk-thread { max-width:900px; margin:16px auto 0; display:flex; flex-direction:column; gap:12px; }
        .tk-msg { border-radius:10px; padding:16px 20px; }
        .tk-msg.admin   { background:#fff; box-shadow:0 2px 8px rgba(0,0,0,.06); border-left:4px solid #f5a623; }
        .tk-msg.cliente { background:#f0f7ff; box-shadow:0 2px 8px rgba(0,0,0,.04); border-left:4px solid #3b82f6; }
        .tk-msg-header  { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; }
        .tk-msg-autor   { font-weight:700; font-size:0.88rem; }
        .tk-msg-data    { font-size:0.78rem; color:#aaa; }
        .tk-msg-body    { font-size:0.9rem; color:#333; line-height:1.6; white-space:pre-wrap; }

        .tk-reply { max-width:900px; margin:16px auto 32px; background:#fff; border-radius:12px;
                    padding:20px 24px; box-shadow:0 2px 12px rgba(0,0,0,.07); }
        .tk-reply label { font-size:0.88rem; font-weight:600; color:#444; display:block; margin-bottom:6px; }
        .tk-reply textarea { width:100%; min-height:100px; padding:10px 14px; border:1px solid #dde3ec;
                             border-radius:8px; font-size:0.9rem; resize:vertical; box-sizing:border-box; }
        .tk-actions { max-width:900px; margin:0 auto 12px; display:flex; gap:8px; flex-wrap:wrap; align-items:center; }

        .tk-controls { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
        select.tk-sel { height:34px; padding:0 10px; border:1px solid #dde3ec; border-radius:7px;
                        font-size:0.86rem; background:#fff; cursor:pointer; }
    </style>
@endpush

@section('content')
<div class="estoque-container-moderna">

    {{-- Cabeçalho do ticket --}}
    <div class="tk-header">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
            <div style="flex:1; min-width:0;">
                <div style="font-size:0.8rem; color:#aaa; margin-bottom:4px;">
                    <a href="{{ route('tickets.index') }}" style="color:#aaa; text-decoration:none;">Tickets</a>
                    &nbsp;/&nbsp; #{{ $ticket->id }}
                </div>
                <h2 style="margin:0; font-size:1.15rem; font-weight:800; color:#1a1a2e;">{{ $ticket->assunto }}</h2>
                <div class="tk-meta">
                    <span class="tk-badge-lg" style="background:{{ \App\Models\Ticket::estadoCor($ticket->estado) }};">
                        {{ $ticket->estado }}
                    </span>
                    <span class="tk-pri-lg" style="background:{{ \App\Models\Ticket::prioridadeCor($ticket->prioridade) }};">
                        {{ $ticket->prioridade }}
                    </span>
                    <span class="tk-cat">{{ $ticket->categoria }}</span>
                    @if($ticket->cliente)
                    <a href="{{ route('clientes.show', $ticket->cliente) }}"
                       style="font-size:0.82rem; color:#555; text-decoration:none; background:#f5f5f5; padding:4px 10px; border-radius:6px;">
                        👤 {{ $ticket->cliente->nome }}
                    </a>
                    @endif
                    <span style="font-size:0.8rem; color:#aaa;">Criado {{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
            <div style="display:flex; gap:8px; flex-shrink:0; flex-wrap:wrap;">
                <a href="{{ route('tickets.index') }}" class="btn btn-ghost" style="height:34px; font-size:0.84rem; padding:0 14px; display:inline-flex; align-items:center;">← Voltar</a>
                <form method="POST" action="{{ route('tickets.destroy', $ticket) }}"
                      onsubmit="return confirm('Eliminar este ticket permanentemente?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-ghost" style="height:34px; font-size:0.84rem; padding:0 14px; color:#e05a4f; border-color:#e05a4f;">Eliminar</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Controlos de estado e prioridade --}}
    <div class="tk-actions">
        <div class="tk-controls">
            <form method="POST" action="{{ route('tickets.estado', $ticket) }}" style="display:flex; align-items:center; gap:6px;">
                @csrf @method('PATCH')
                <label style="font-size:0.82rem; color:#888; white-space:nowrap;">Estado:</label>
                <select name="estado" class="tk-sel" onchange="this.form.submit()">
                    @foreach(['Aberto','Em Andamento','Resolvido','Fechado'] as $e)
                    <option value="{{ $e }}" {{ $ticket->estado === $e ? 'selected' : '' }}>{{ $e }}</option>
                    @endforeach
                </select>
            </form>
            <form method="POST" action="{{ route('tickets.prioridade', $ticket) }}" style="display:flex; align-items:center; gap:6px;">
                @csrf @method('PATCH')
                <label style="font-size:0.82rem; color:#888; white-space:nowrap;">Prioridade:</label>
                <select name="prioridade" class="tk-sel" onchange="this.form.submit()">
                    @foreach(['Baixa','Normal','Alta','Urgente'] as $p)
                    <option value="{{ $p }}" {{ $ticket->prioridade === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        @if(session('success'))
        <span style="font-size:0.83rem; color:#2a8a55; background:#eafaf1; padding:4px 12px; border-radius:6px;">
            ✓ {{ session('success') }}
        </span>
        @endif
    </div>

    {{-- Fio de mensagens --}}
    <div class="tk-thread">
        @forelse($ticket->mensagens as $msg)
        <div class="tk-msg {{ $msg->autor_tipo }}">
            <div class="tk-msg-header">
                <span class="tk-msg-autor">
                    @if($msg->isAdmin())
                        🛡 {{ $msg->user?->name ?? 'Admin' }}
                    @else
                        👤 {{ $ticket->cliente?->nome ?? 'Cliente' }}
                    @endif
                </span>
                <span class="tk-msg-data">{{ $msg->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="tk-msg-body">{{ $msg->mensagem }}</div>
        </div>
        @empty
        <div style="text-align:center; color:#bbb; padding:30px; font-size:0.9rem;">Sem mensagens neste ticket.</div>
        @endforelse
    </div>

    {{-- Caixa de resposta --}}
    @if($ticket->isAberto())
    <div class="tk-reply">
        <label>Adicionar resposta</label>
        <form method="POST" action="{{ route('tickets.reply', $ticket) }}">
            @csrf
            @error('mensagem')
            <div style="color:#e05a4f; font-size:0.85rem; margin-bottom:6px;">{{ $message }}</div>
            @enderror
            <textarea name="mensagem" placeholder="Escreva a resposta…" required>{{ old('mensagem') }}</textarea>
            <div style="margin-top:10px; display:flex; gap:8px;">
                <button type="submit" class="btn btn-cta" style="height:38px;">Enviar resposta</button>
                <button type="button" class="btn btn-ghost" style="height:38px;"
                        onclick="document.querySelector('[name=estado]').value='Resolvido'; document.querySelector('[name=estado]').form.submit();">
                    Marcar como Resolvido
                </button>
            </div>
        </form>
    </div>
    @else
    <div style="max-width:900px; margin:16px auto 32px; text-align:center; color:#aaa; font-size:0.88rem; padding:16px;">
        Este ticket está {{ strtolower($ticket->estado) }} — para reabrir, muda o estado para "Aberto".
    </div>
    @endif

</div>
@endsection
