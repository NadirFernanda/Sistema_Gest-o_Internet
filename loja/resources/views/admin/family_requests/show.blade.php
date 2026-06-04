@extends('layouts.app')
@section('title', 'Pedido #{{ $req->id }} — Admin')

@push('styles')
<style>
:root{--a-bg:#f4f6f9;--a-surf:#fff;--a-border:#dde2ea;--a-text:#1a202c;--a-muted:#64748b;--a-faint:#9aa5b4;--a-brand:#f7b500;--a-green:#16a34a;--a-amber:#d97706;--a-red:#dc2626;}
.ap{font-family:Inter,system-ui,sans-serif;background:var(--a-bg);min-height:60vh;padding:2rem 0 4rem;color:var(--a-text);}
.ap-wrap{max-width:860px;margin:0 auto;padding:0 1.5rem;}
.ap-topbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;}
.ap-topbar h1{font-size:1.3rem;font-weight:800;margin:0 0 .15rem;letter-spacing:-.02em;}
.ap-topbar .ap-sub{font-size:.78rem;color:var(--a-faint);}
.ap-back{font-size:.82rem;font-weight:600;color:var(--a-muted);text-decoration:none;padding:.4rem .85rem;border:1px solid var(--a-border);border-radius:7px;background:var(--a-surf);}
.ap-back:hover{background:var(--a-border);}
.ap-ok{background:#f0fdf4;border:1px solid #86efac;border-left:4px solid var(--a-green);color:#166534;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ap-err{background:#fef2f2;border:1px solid #fecaca;border-left:4px solid var(--a-red);color:#7f1d1d;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ap-card{background:var(--a-surf);border:1px solid var(--a-border);border-radius:11px;padding:1.4rem;margin-bottom:1.25rem;}
.ap-card-title{font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--a-faint);margin:0 0 1rem;padding-bottom:.6rem;border-bottom:1px solid var(--a-border);}
.ap-dl{display:grid;grid-template-columns:1fr 1fr;gap:.6rem 1.5rem;font-size:.88rem;}
@media(max-width:540px){.ap-dl{grid-template-columns:1fr}}
.ap-dl dt{color:var(--a-muted);font-size:.78rem;margin-bottom:.1rem;}
.ap-dl dd{font-weight:600;margin:0;word-break:break-word;}
.badge{display:inline-block;padding:.25rem .65rem;border-radius:999px;font-size:.75rem;font-weight:700;}
.bg-green{background:#dcfce7;color:#15803d;}.bg-amber{background:#fef3c7;color:#b45309;}
.bg-red{background:#fee2e2;color:#b91c1c;}.bg-gray{background:#f1f5f9;color:#475569;}
.bg-blue{background:#dbeafe;color:#1d4ed8;}.bg-orange{background:#ffedd5;color:#9a3412;}
.ap-btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.55rem 1.2rem;border-radius:8px;font-size:.875rem;font-weight:700;border:none;cursor:pointer;font-family:inherit;text-decoration:none;white-space:nowrap;}
.ap-btn-primary{background:#f7b500;color:#1a202c;}.ap-btn-primary:hover{filter:brightness(.95);}
.ap-btn-danger{background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;}.ap-btn-danger:hover{background:#fecaca;}
.ap-btn-outline{background:var(--a-surf);color:var(--a-muted);border:1.5px solid var(--a-border);}
.ap-actions{display:flex;gap:.65rem;flex-wrap:wrap;margin-top:1rem;}
.ap-notes{background:#f8fafc;border-left:4px solid var(--a-brand);border-radius:0 8px 8px 0;padding:.85rem 1rem;font-size:.85rem;color:#374151;word-break:break-word;line-height:1.65;}
</style>
@endpush

@section('content')
<div class="ap"><div class="ap-wrap">

  <div class="ap-topbar">
    <div>
      <h1>Pedido #{{ $req->id }}</h1>
      <p class="ap-sub">Admin &rsaquo; <a href="{{ route('admin.family_requests.index') }}" style="color:var(--a-faint);text-decoration:none;">Planos Familiares</a> &rsaquo; #{{ $req->id }}</p>
    </div>
    <a href="{{ route('admin.family_requests.index') }}" class="ap-back">&larr; Voltar à lista</a>
  </div>

  @if(session('success'))
    <div class="ap-ok">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="ap-err">{{ session('error') }}</div>
  @endif

  {{-- Estado actual --}}
  <div class="ap-card" style="border-left:4px solid
    @if($req->status === 'activated') #16a34a
    @elseif($req->status === 'pending') #dc2626
    @elseif($req->status === 'awaiting_payment') #d97706
    @else #dde2ea @endif;">
    <p class="ap-card-title">Estado do pedido</p>
    <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
      @if($req->status === 'activated')
        <span class="badge bg-green" style="font-size:.85rem;padding:.35rem .9rem;">Activado</span>
        <span style="font-size:.88rem;color:var(--a-muted);">Janela adicionada no SG com sucesso.</span>
      @elseif($req->status === 'pending')
        <span class="badge bg-red" style="font-size:.85rem;padding:.35rem .9rem;">Pendente ⚠</span>
        <span style="font-size:.88rem;color:var(--a-muted);">Pagamento confirmado mas activação no SG falhou. Clique "Activar no SG" abaixo.</span>
      @elseif($req->status === 'confirmed')
        <span class="badge bg-blue" style="font-size:.85rem;padding:.35rem .9rem;">Confirmado</span>
      @elseif($req->status === 'awaiting_payment')
        <span class="badge bg-amber" style="font-size:.85rem;padding:.35rem .9rem;">A aguardar pagamento</span>
      @elseif($req->status === 'cancelled')
        <span class="badge bg-gray" style="font-size:.85rem;padding:.35rem .9rem;">Cancelado</span>
      @else
        <span class="badge bg-gray">{{ $req->status }}</span>
      @endif
    </div>

    @if($req->notes)
      <div class="ap-notes" style="margin-top:1rem;">{{ $req->notes }}</div>
    @endif

    {{-- Acções --}}
    <div class="ap-actions">
      @if(in_array($req->status, ['pending', 'confirmed']))
        <form method="POST" action="{{ route('admin.family_requests.confirmar', $req) }}"
              onsubmit="return confirm('Activar pedido #{{ $req->id }} no SG?');">
          @csrf
          <button type="submit" class="ap-btn ap-btn-primary">Activar no SG</button>
        </form>
      @endif
      @if($req->status !== 'activated' && $req->status !== 'cancelled')
        <form method="POST" action="{{ route('admin.family_requests.cancelar', $req) }}"
              onsubmit="return confirm('Cancelar pedido #{{ $req->id }}?');">
          @csrf
          <button type="submit" class="ap-btn ap-btn-danger">Cancelar pedido</button>
        </form>
      @endif
    </div>
  </div>

  {{-- Dados do cliente --}}
  <div class="ap-card">
    <p class="ap-card-title">Cliente</p>
    <dl class="ap-dl">
      <div><dt>Nome</dt><dd>{{ $req->customer_name }}</dd></div>
      <div><dt>Telefone</dt><dd>{{ $req->customer_phone }}</dd></div>
      @if($req->customer_email)
      <div><dt>E-mail</dt><dd>{{ $req->customer_email }}</dd></div>
      @endif
      @if($req->customer_nif)
      <div><dt>NIF</dt><dd>{{ $req->customer_nif }}</dd></div>
      @endif
    </dl>
  </div>

  {{-- Dados do plano --}}
  <div class="ap-card">
    <p class="ap-card-title">Plano contratado</p>
    <dl class="ap-dl">
      <div><dt>Plano</dt><dd>{{ $req->plan_name }}</dd></div>
      @if($req->plan_preco)
      <div><dt>Valor mensal</dt><dd>{{ number_format($req->plan_preco, 0, ',', '.') }} AOA/mês</dd></div>
      @endif
      @if($req->plan_ciclo_dias)
      <div><dt>Ciclo</dt><dd>{{ $req->plan_ciclo_dias }} dias</dd></div>
      @endif
      <div><dt>ID do plano (SGA)</dt><dd class="dim">{{ $req->plan_id }}</dd></div>
    </dl>
  </div>

  {{-- Pagamento --}}
  <div class="ap-card">
    <p class="ap-card-title">Pagamento</p>
    <dl class="ap-dl">
      <div><dt>Método</dt><dd><span class="badge bg-amber">GPO / EMIS</span></dd></div>
      <div><dt>Referência GPO</dt><dd>{{ $req->gpo_reference ?? ($req->payment_reference ?? '—') }}</dd></div>
      <div><dt>Data do pedido</dt><dd>{{ $req->created_at->format('d/m/Y H:i') }}</dd></div>
      <div><dt>Última actualização</dt><dd>{{ $req->updated_at->format('d/m/Y H:i') }}</dd></div>
    </dl>
  </div>

</div></div>
@endsection
