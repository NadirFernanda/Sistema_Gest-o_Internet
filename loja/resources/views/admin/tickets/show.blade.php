@extends('layouts.app')
@section('title', $ticket->ref . ' — Tickets Admin')

@push('styles')
<style>
:root{--a-bg:#f4f6f9;--a-surf:#fff;--a-border:#dde2ea;--a-text:#1a202c;--a-muted:#64748b;--a-faint:#9aa5b4;--a-brand:#f7b500;--a-green:#16a34a;--a-red:#dc2626;--a-amber:#d97706;}
.ap{font-family:Inter,system-ui,sans-serif;background:var(--a-bg);min-height:60vh;padding:2rem 0 4rem;color:var(--a-text);}
.ap-wrap{max-width:900px;margin:0 auto;padding:0 1.5rem;}
.ap-topbar{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;}
.ap-topbar h1{font-size:1.25rem;font-weight:800;margin:0 0 .2rem;}
.ap-sub{font-size:.78rem;color:var(--a-faint);}
.ap-back{font-size:.82rem;font-weight:600;color:var(--a-muted);text-decoration:none;padding:.4rem .85rem;border:1px solid var(--a-border);border-radius:7px;background:var(--a-surf);}
.ap-ok{background:#f0fdf4;border:1px solid #86efac;border-left:4px solid var(--a-green);color:#166534;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ap-err{background:#fef2f2;border:1px solid #fecaca;border-left:4px solid var(--a-red);color:#7f1d1d;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ap-card{background:var(--a-surf);border:1px solid var(--a-border);border-radius:11px;padding:1.4rem;margin-bottom:1rem;}
.ap-card-title{font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--a-faint);margin:0 0 .85rem;padding-bottom:.6rem;border-bottom:1px solid var(--a-border);}
.ap-dl{display:grid;grid-template-columns:1fr 1fr;gap:.5rem 1.5rem;font-size:.87rem;}
@media(max-width:540px){.ap-dl{grid-template-columns:1fr}}
.ap-dl dt{color:var(--a-muted);font-size:.78rem;margin-bottom:.1rem;}
.ap-dl dd{font-weight:600;margin:0;}
.badge{display:inline-block;padding:.22rem .65rem;border-radius:999px;font-size:.73rem;font-weight:700;}
.b-open{background:#dbeafe;color:#1d4ed8;}.b-prog{background:#fef3c7;color:#b45309;}
.b-resolved{background:#dcfce7;color:#15803d;}.b-closed{background:#f1f5f9;color:#475569;}
.b-cat{background:#ede9fe;color:#6d28d9;}
.reply{border-radius:10px;padding:1rem 1.1rem;margin-bottom:.65rem;}
.reply-client{background:#f8fafc;border:1px solid #e2e8f0;}
.reply-admin{background:#fffbeb;border:1px solid #fde68a;border-left:4px solid var(--a-brand);}
.reply-meta{font-size:.75rem;color:#94a3b8;margin-bottom:.4rem;}
.reply-admin .reply-meta{color:#b45309;font-weight:600;}
.tk-msg{white-space:pre-wrap;line-height:1.7;color:#374151;font-size:.875rem;margin:0;}
.ap-ctrl{width:100%;box-sizing:border-box;padding:.55rem .75rem;border:1.5px solid var(--a-border);border-radius:8px;font-size:.875rem;font-family:inherit;background:#f8fafc;outline:none;}
.ap-ctrl:focus{border-color:var(--a-brand);background:#fff;}
.ap-btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.55rem 1.2rem;border-radius:8px;font-size:.875rem;font-weight:700;border:none;cursor:pointer;font-family:inherit;text-decoration:none;white-space:nowrap;}
.ap-btn-primary{background:#f7b500;color:#1a202c;}.ap-btn-primary:hover{filter:brightness(.95);}
.ap-btn-sm{padding:.35rem .75rem;font-size:.8rem;}
.ap-label{display:block;font-size:.77rem;font-weight:600;color:#374151;margin-bottom:.3rem;}
</style>
@endpush

@section('content')
<div class="ap"><div class="ap-wrap">

  <div class="ap-topbar">
    <div>
      <h1>{{ $ticket->ref }} — {{ $ticket->subject }}</h1>
      <p class="ap-sub">Admin &rsaquo; <a href="{{ route('admin.tickets.index') }}" style="color:var(--a-faint);text-decoration:none;">Tickets</a> &rsaquo; {{ $ticket->ref }}</p>
    </div>
    <a href="{{ route('admin.tickets.index') }}" class="ap-back">&larr; Todos os tickets</a>
  </div>

  @if(session('success'))<div class="ap-ok">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="ap-err">{{ session('error') }}</div>@endif

  <div style="display:grid;grid-template-columns:1fr 320px;gap:1rem;align-items:start;">

    {{-- Coluna principal --}}
    <div>
      {{-- Mensagem original --}}
      <div class="ap-card">
        <p class="ap-card-title">Mensagem original — {{ $ticket->name }} &nbsp;·&nbsp; {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
        <p class="tk-msg">{{ $ticket->message }}</p>
      </div>

      {{-- Respostas --}}
      @foreach($ticket->replies as $reply)
        <div class="reply {{ $reply->is_admin ? 'reply-admin' : 'reply-client' }}">
          <div class="reply-meta">
            <strong>{{ $reply->is_admin ? 'AngolaWiFi Suporte' : $reply->author_name }}</strong>
            &nbsp;·&nbsp; {{ $reply->created_at->format('d/m/Y H:i') }}
          </div>
          <p class="tk-msg">{{ $reply->message }}</p>
        </div>
      @endforeach

      {{-- Resposta do admin --}}
      @if($ticket->isOpen())
        <div class="ap-card">
          <p class="ap-card-title">Responder ao cliente</p>
          <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}">
            @csrf
            <textarea name="message" rows="5" class="ap-ctrl" placeholder="Escreva a resposta para o cliente..." required style="resize:vertical;min-height:100px;"></textarea>
            <div style="display:flex;gap:.5rem;margin-top:.65rem;justify-content:flex-end;">
              <button type="submit" class="ap-btn ap-btn-primary">Enviar resposta</button>
            </div>
          </form>
        </div>
      @endif

      {{-- Link do ticket para o cliente --}}
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:9px;padding:.85rem 1rem;font-size:.82rem;color:#64748b;margin-top:.5rem;">
        Link do cliente: <a href="{{ route('tickets.show', $ticket->token) }}" target="_blank" style="color:#d97706;word-break:break-all;">{{ route('tickets.show', $ticket->token) }}</a>
      </div>
    </div>

    {{-- Sidebar --}}
    <div>
      {{-- Detalhes --}}
      <div class="ap-card">
        <p class="ap-card-title">Detalhes</p>
        <dl class="ap-dl" style="grid-template-columns:1fr;">
          <div><dt>Estado</dt><dd>
            <span class="badge {{ $ticket->status === 'open' ? 'b-open' : ($ticket->status === 'in_progress' ? 'b-prog' : ($ticket->status === 'resolved' ? 'b-resolved' : 'b-closed')) }}">
              {{ \App\Models\Ticket::statusLabel($ticket->status) }}
            </span>
          </dd></div>
          <div style="margin-top:.5rem;"><dt>Categoria</dt><dd><span class="badge b-cat">{{ \App\Models\Ticket::CATEGORIES[$ticket->category] ?? $ticket->category }}</span></dd></div>
          <div style="margin-top:.5rem;"><dt>Prioridade</dt><dd>{{ \App\Models\Ticket::priorityLabel($ticket->priority) }}</dd></div>
          <div style="margin-top:.5rem;"><dt>Cliente</dt><dd>{{ $ticket->name }}</dd></div>
          @if($ticket->phone)<div style="margin-top:.5rem;"><dt>Telefone</dt><dd>{{ $ticket->phone }}</dd></div>@endif
          @if($ticket->email)<div style="margin-top:.5rem;"><dt>E-mail</dt><dd style="word-break:break-all;">{{ $ticket->email }}</dd></div>@endif
          <div style="margin-top:.5rem;"><dt>Criado em</dt><dd>{{ $ticket->created_at->format('d/m/Y H:i') }}</dd></div>
          @if($ticket->resolved_at)<div style="margin-top:.5rem;"><dt>Resolvido em</dt><dd>{{ $ticket->resolved_at->format('d/m/Y H:i') }}</dd></div>@endif
        </dl>
      </div>

      {{-- Gerir estado --}}
      <div class="ap-card">
        <p class="ap-card-title">Gerir ticket</p>
        <form method="POST" action="{{ route('admin.tickets.status', $ticket) }}">
          @csrf @method('PATCH')
          <div style="margin-bottom:.75rem;">
            <label class="ap-label">Estado</label>
            <select name="status" class="ap-ctrl">
              @foreach(['open'=>'Aberto','in_progress'=>'Em análise','resolved'=>'Resolvido','closed'=>'Fechado'] as $v => $l)
                <option value="{{ $v }}" @selected($ticket->status === $v)>{{ $l }}</option>
              @endforeach
            </select>
          </div>
          <div style="margin-bottom:.75rem;">
            <label class="ap-label">Prioridade</label>
            <select name="priority" class="ap-ctrl">
              <option value="low"    @selected($ticket->priority === 'low')>Baixa</option>
              <option value="normal" @selected($ticket->priority === 'normal')>Normal</option>
              <option value="high"   @selected($ticket->priority === 'high')>Alta</option>
              <option value="urgent" @selected($ticket->priority === 'urgent')>Urgente</option>
            </select>
          </div>
          <div style="margin-bottom:.75rem;">
            <label class="ap-label">Notas internas</label>
            <textarea name="admin_notes" rows="3" class="ap-ctrl" placeholder="Notas visíveis apenas pelo admin...">{{ $ticket->admin_notes }}</textarea>
          </div>
          <button type="submit" class="ap-btn ap-btn-primary" style="width:100%;">Guardar</button>
        </form>
      </div>
    </div>

  </div>

</div></div>
@endsection
