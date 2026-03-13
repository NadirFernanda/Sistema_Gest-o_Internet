@extends('layouts.app')

@section('title', 'Planos Familiares &amp; Empresariais &mdash; Admin')

@section('content')
<style>
:root{--a-bg:#f4f6f9;--a-surf:#fff;--a-border:#dde2ea;--a-text:#1a202c;--a-muted:#64748b;--a-faint:#9aa5b4;--a-brand:#f7b500;--a-green:#16a34a;--a-amber:#d97706;--a-red:#dc2626;}
.ap{font-family:Inter,system-ui,sans-serif;background:var(--a-bg);min-height:60vh;padding:2rem 0 4rem;color:var(--a-text);}
.ap-wrap{max-width:1140px;margin:0 auto;padding:0 1.5rem;}
.ap-topbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;}
.ap-topbar h1{font-size:1.35rem;font-weight:800;margin:0 0 .15rem;letter-spacing:-.02em;}
.ap-topbar .ap-sub{font-size:.78rem;color:var(--a-faint);}
.ap-back{font-size:.82rem;font-weight:600;color:var(--a-muted);text-decoration:none;padding:.4rem .85rem;border:1px solid var(--a-border);border-radius:7px;background:var(--a-surf);transition:background .15s;}
.ap-back:hover{background:var(--a-border);}
.ap-ok{background:#f0fdf4;border:1px solid #86efac;border-left:4px solid var(--a-green);color:#166534;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ap-err{background:#fef2f2;border:1px solid #fecaca;border-left:4px solid var(--a-red);color:#7f1d1d;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ap-note{background:#fffbeb;border:1px solid #fde68a;border-left:4px solid var(--a-brand);color:#78350f;padding:.75rem 1rem;border-radius:8px;font-size:.85rem;margin-bottom:1.25rem;}
.ap-stats{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:.65rem;margin-bottom:1.5rem;}
.ap-stat{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;padding:.85rem 1rem;text-decoration:none;transition:border-color .15s;}
.ap-stat:hover{border-color:var(--a-brand);}
.ap-stat-val{font-size:1.6rem;font-weight:800;line-height:1;margin:0 0 .2rem;}
.ap-stat-lbl{font-size:.75rem;color:var(--a-muted);font-weight:500;}
.ap-stat.active{border-color:var(--a-brand);}
.ap-btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.45rem 1rem;border-radius:8px;font-size:.82rem;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:filter .15s;text-decoration:none;white-space:nowrap;}
.ap-btn-primary{background:#f7b500;color:#1a202c;}.ap-btn-primary:hover{filter:brightness(.95);}
.ap-btn-danger{background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;}.ap-btn-danger:hover{background:#fecaca;}
.ap-tcard{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;overflow:hidden;}
.ap-table{width:100%;border-collapse:collapse;font-size:.845rem;}
.ap-table thead{background:#f8fafc;}
.ap-table th{text-align:left;padding:.65rem 1rem;font-size:.69rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--a-faint);border-bottom:1px solid var(--a-border);white-space:nowrap;}
.ap-table td{padding:.6rem 1rem;border-bottom:1px solid #f4f6f9;vertical-align:middle;color:#374151;}
.ap-table tbody tr:last-child td{border-bottom:none;}
.ap-table tbody tr:hover td{background:#fafbff;}
.ap-table .dim{color:var(--a-faint);font-size:.82rem;}
.badge{display:inline-block;padding:.2rem .6rem;border-radius:999px;font-size:.73rem;font-weight:700;white-space:nowrap;}
.bg-amber{background:#fef3c7;color:#b45309;}.bg-green{background:#dcfce7;color:#15803d;}
.bg-gray{background:#f1f5f9;color:#475569;}.bg-red{background:#fee2e2;color:#b91c1c;}.bg-blue{background:#dbeafe;color:#1d4ed8;}
.ap-pager{padding:.7rem 1rem;border-top:1px solid var(--a-border);background:#f8fafc;}
.ap-empty{padding:3rem 1rem;text-align:center;color:var(--a-faint);}
.ap-empty-t{font-size:.95rem;font-weight:700;color:var(--a-muted);margin:0 0 .3rem;}
.ap-empty-s{font-size:.82rem;margin:0;}
</style>

<div class="ap"><div class="ap-wrap">

  <div class="ap-topbar">
    <div>
      <h1>Planos Familiares &amp; Empresariais</h1>
      <p class="ap-sub">Admin &rsaquo; Pedidos de planos com gest&atilde;o via SG</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="ap-back">&larr; Dashboard</a>
  </div>

  @if(session('success'))
    <div class="ap-ok">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="ap-err">{{ session('error') }}</div>
  @endif

  <div class="ap-note">
    A janela de acesso &eacute; adicionada <strong>automaticamente no SG</strong> quando o cliente submete o formul&aacute;rio.
    O seu papel aqui &eacute; <strong>verificar se o pagamento foi recebido</strong> e cancelar casos em que n&atilde;o foi.
    O bot&atilde;o &ldquo;Activar no SG&rdquo; s&oacute; aparece quando a activa&ccedil;&atilde;o autom&aacute;tica falhou.
  </div>

  {{-- Contadores por estado --}}
  @php
    $filterLabels = [
      'pending'   => ['label' => 'Pendentes',   'val' => $counts['pending']   ?? 0],
      'confirmed' => ['label' => 'Confirmados', 'val' => $counts['confirmed'] ?? 0],
      'activated' => ['label' => 'Activados',   'val' => $counts['activated'] ?? 0],
      'cancelled' => ['label' => 'Cancelados',  'val' => $counts['cancelled'] ?? 0],
    ];
    $total = array_sum(array_column($filterLabels, 'val'));
  @endphp
  <div class="ap-stats">
    @foreach($filterLabels as $key => $meta)
      <a href="{{ route('admin.family_requests.index', ['status' => $key]) }}"
         class="ap-stat {{ $status === $key ? 'active' : '' }}" style="display:block;color:inherit;">
        <p class="ap-stat-val">{{ $meta['val'] }}</p>
        <p class="ap-stat-lbl">{{ $meta['label'] }}</p>
      </a>
    @endforeach
    <a href="{{ route('admin.family_requests.index') }}"
       class="ap-stat {{ $status === 'all' ? 'active' : '' }}" style="display:block;color:inherit;">
      <p class="ap-stat-val">{{ $total }}</p>
      <p class="ap-stat-lbl">Todos</p>
    </a>
  </div>

  <div class="ap-tcard">
    <table class="ap-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Plano</th>
          <th>Cliente</th>
          <th>Contacto</th>
          <th>Pagamento</th>
          <th>Estado</th>
          <th>Data</th>
          <th>Ac&ccedil;&otilde;es</th>
        </tr>
      </thead>
      <tbody>
        @forelse($requests as $req)
          <tr>
            <td class="dim">{{ $req->id }}</td>
            <td style="font-weight:600;">{{ $req->plan_name }}</td>
            <td>
              {{ $req->customer_name }}
              @if($req->customer_email)
                <br><span class="dim">{{ $req->customer_email }}</span>
              @endif
            </td>
            <td>{{ $req->customer_phone }}</td>
            <td>{{ str_replace('_', ' ', $req->payment_method) }}</td>
            <td>
              @if($req->status === 'activated')
                <span class="badge bg-green">Activado</span>
              @elseif($req->status === 'confirmed')
                <span class="badge bg-blue">Confirmado</span>
              @elseif($req->status === 'pending')
                <span class="badge bg-amber">Pendente</span>
              @elseif($req->status === 'cancelled')
                <span class="badge bg-red">Cancelado</span>
              @else
                <span class="badge bg-gray">{{ $req->status }}</span>
              @endif
            </td>
            <td class="dim" style="white-space:nowrap;">{{ $req->created_at->format('d/m/Y H:i') }}</td>
            <td>
              @if(in_array($req->status, ['pending', 'confirmed']))
                <form method="POST" action="{{ route('admin.family_requests.confirmar', $req) }}"
                      style="display:inline;"
                      onsubmit="return confirm('Activar pedido #{{ $req->id }} manualmente no SG?');">
                  @csrf
                  <button type="submit" class="ap-btn ap-btn-primary">Activar no SG</button>
                </form>
                <form method="POST" action="{{ route('admin.family_requests.cancelar', $req) }}"
                      style="display:inline;margin-left:.35rem;"
                      onsubmit="return confirm('Cancelar pedido #{{ $req->id }}?');">
                  @csrf
                  <button type="submit" class="ap-btn ap-btn-danger">Cancelar</button>
                </form>
              @elseif($req->status === 'activated')
                <span class="badge bg-green">Janela adicionada no SG</span>
                @if($req->notes)
                  <br><span class="dim" style="font-size:.75rem;">{{ $req->notes }}</span>
                @endif
              @else
                <span class="dim">&mdash;</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8">
              <div class="ap-empty">
                <p class="ap-empty-t">Nenhum pedido encontrado</p>
                <p class="ap-empty-s">Nenhum resultado para o filtro seleccionado.</p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
    <div class="ap-pager">{{ $requests->links() }}</div>
  </div>

</div></div>
@endsection
