@extends('layouts.app')
@section('title', 'Ticket ' . $ticket->ref . ' — AngolaWiFi')

@push('styles')
<style>
.tk{font-family:Inter,system-ui,sans-serif;background:#f4f6f9;min-height:70vh;padding:2.5rem 0 5rem;}
.tk-wrap{max-width:720px;margin:0 auto;padding:0 1.25rem;}
.tk-topbar{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;}
.tk-topbar h1{font-size:1.25rem;font-weight:800;color:#1a202c;margin:0 0 .2rem;}
.tk-card{background:#fff;border:1px solid #dde2ea;border-radius:12px;padding:1.4rem;margin-bottom:1rem;}
.tk-meta{display:flex;flex-wrap:wrap;gap:.5rem .9rem;font-size:.82rem;color:#64748b;margin-top:.4rem;}
.badge{display:inline-block;padding:.22rem .65rem;border-radius:999px;font-size:.73rem;font-weight:700;}
.b-open{background:#dbeafe;color:#1d4ed8;}.b-prog{background:#fef3c7;color:#b45309;}
.b-resolved{background:#dcfce7;color:#15803d;}.b-closed{background:#f1f5f9;color:#475569;}
.b-cat{background:#ede9fe;color:#6d28d9;}
.tk-msg{white-space:pre-wrap;line-height:1.7;color:#374151;font-size:.9rem;}
.reply{border-radius:10px;padding:1rem 1.1rem;margin-bottom:.75rem;}
.reply-client{background:#f8fafc;border:1px solid #e2e8f0;}
.reply-admin{background:#fffbeb;border:1px solid #fde68a;border-left:4px solid #f7b500;}
.reply-meta{font-size:.75rem;color:#94a3b8;margin-bottom:.4rem;}
.reply-admin .reply-meta{color:#b45309;}
.tk-input{width:100%;box-sizing:border-box;padding:.6rem .85rem;border:1.5px solid #dde2ea;border-radius:9px;font-size:.9rem;font-family:inherit;background:#f8fafc;outline:none;resize:vertical;}
.tk-input:focus{border-color:#f7b500;background:#fff;}
.tk-btn{display:inline-flex;align-items:center;justify-content:center;padding:.6rem 1.5rem;background:#f7b500;color:#1a202c;font-weight:800;font-size:.9rem;border:none;border-radius:9px;cursor:pointer;font-family:inherit;transition:filter .15s;}
.tk-btn:hover{filter:brightness(.95);}
.tk-link{display:inline-block;padding:.35rem .8rem;background:#f1f5f9;border:1px solid #dde2ea;border-radius:7px;font-size:.78rem;font-family:monospace;word-break:break-all;color:#374151;margin-top:.5rem;}
</style>
@endpush

@section('content')
<div class="tk"><div class="tk-wrap">

  <div class="tk-topbar">
    <div>
      <h1>{{ $ticket->ref }} — {{ $ticket->subject }}</h1>
      <div class="tk-meta">
        <span class="badge {{
          $ticket->status === 'open' ? 'b-open' :
          ($ticket->status === 'in_progress' ? 'b-prog' :
          ($ticket->status === 'resolved' ? 'b-resolved' : 'b-closed')) }}">
          {{ \App\Models\Ticket::statusLabel($ticket->status) }}
        </span>
        <span class="badge b-cat">{{ \App\Models\Ticket::CATEGORIES[$ticket->category] ?? $ticket->category }}</span>
        <span>Aberto em {{ $ticket->created_at->format('d/m/Y H:i') }}</span>
      </div>
    </div>
    <a href="{{ route('tickets.create') }}" style="font-size:.82rem;color:#64748b;text-decoration:none;padding:.4rem .85rem;border:1px solid #dde2ea;border-radius:7px;background:#fff;">+ Novo ticket</a>
  </div>

  @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #86efac;border-left:4px solid #16a34a;color:#166534;padding:.75rem 1rem;border-radius:9px;margin-bottom:1rem;font-size:.875rem;">{{ session('success') }}</div>
  @endif

  {{-- Mensagem original --}}
  <div class="tk-card">
    <div style="font-size:.78rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.6rem;">Mensagem original — {{ $ticket->name }}</div>
    <p class="tk-msg">{{ $ticket->message }}</p>
    <div class="tk-meta" style="margin-top:.75rem;">
      @if($ticket->phone)<span>Tel: {{ $ticket->phone }}</span>@endif
      @if($ticket->email)<span>{{ $ticket->email }}</span>@endif
    </div>
  </div>

  {{-- Respostas --}}
  @foreach($ticket->replies as $reply)
    <div class="reply {{ $reply->is_admin ? 'reply-admin' : 'reply-client' }}">
      <div class="reply-meta">
        <strong>{{ $reply->is_admin ? 'AngolaWiFi Suporte' : $reply->author_name }}</strong>
        &nbsp;·&nbsp; {{ $reply->created_at->format('d/m/Y H:i') }}
      </div>
      <p class="tk-msg" style="margin:0;">{{ $reply->message }}</p>
    </div>
  @endforeach

  {{-- Guardar link --}}
  <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:1rem 1.1rem;margin-bottom:1.25rem;font-size:.83rem;color:#64748b;">
    Guarde este link para acompanhar o ticket sem necessidade de conta:
    <div class="tk-link">{{ url()->current() }}</div>
  </div>

  {{-- Formulário de resposta --}}
  @if($ticket->isOpen())
    <div class="tk-card">
      <p style="font-size:.85rem;font-weight:700;color:#374151;margin:0 0 .75rem;">Adicionar resposta</p>
      <form method="POST" action="{{ route('tickets.reply', $ticket->token) }}">
        @csrf
        <textarea name="message" rows="4" class="tk-input" placeholder="Escreva a sua resposta..." required></textarea>
        @error('message')<p style="font-size:.78rem;color:#dc2626;margin:.25rem 0 0;">{{ $message }}</p>@enderror
        <button type="submit" class="tk-btn" style="margin-top:.65rem;">Enviar resposta</button>
      </form>
    </div>
  @else
    <div style="text-align:center;padding:1.25rem;color:#94a3b8;font-size:.88rem;">
      Este ticket está <strong>{{ \App\Models\Ticket::statusLabel($ticket->status) }}</strong> e não aceita mais respostas.
      <br><a href="{{ route('tickets.create') }}" style="color:#d97706;">Abrir novo ticket</a>
    </div>
  @endif

</div></div>
@endsection
