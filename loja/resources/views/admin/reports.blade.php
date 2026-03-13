@extends('layouts.app')

@section('content')
<style>
:root{--a-bg:#f4f6f9;--a-surf:#fff;--a-border:#dde2ea;--a-text:#1a202c;--a-muted:#64748b;--a-faint:#9aa5b4;--a-brand:#f7b500;--a-green:#16a34a;--a-amber:#d97706;--a-red:#dc2626;}
.ap{font-family:Inter,system-ui,sans-serif;background:var(--a-bg);min-height:60vh;padding:2rem 0 4rem;color:var(--a-text);}
.ap-wrap{max-width:1140px;margin:0 auto;padding:0 1.5rem;}
.ap-topbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1.75rem;}
.ap-topbar h1{font-size:1.35rem;font-weight:800;margin:0 0 .15rem;letter-spacing:-.02em;}
.ap-topbar .ap-sub{font-size:.78rem;color:var(--a-faint);}
.ap-back{font-size:.82rem;font-weight:600;color:var(--a-muted);text-decoration:none;padding:.4rem .85rem;border:1px solid var(--a-border);border-radius:7px;background:var(--a-surf);transition:background .15s;}
.ap-back:hover{background:var(--a-border);}
.ap-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1rem;}
.ap-card{background:var(--a-surf);border:1px solid var(--a-border);border-radius:11px;padding:1.4rem;}
.ap-card-title{font-size:.95rem;font-weight:700;margin:0 0 1rem;color:var(--a-text);}
.ap-list{list-style:none;margin:0;padding:0;}
.ap-list li{display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:1px solid var(--a-border);font-size:.88rem;}
.ap-list li:last-child{border-bottom:none;}
.badge{display:inline-block;padding:.2rem .6rem;border-radius:999px;font-size:.73rem;font-weight:700;white-space:nowrap;}
.bg-amber{background:#fef3c7;color:#b45309;}.bg-green{background:#dcfce7;color:#15803d;}.bg-gray{background:#f1f5f9;color:#475569;}.bg-red{background:#fee2e2;color:#b91c1c;}
</style>

<div class="ap"><div class="ap-wrap">

  <div class="ap-topbar">
    <div>
      <h1>Relat&oacute;rios de Recargas</h1>
      <p class="ap-sub">Admin &rsaquo; Resumo por estado e por dia</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="ap-back">&larr; Dashboard</a>
  </div>

  <div class="ap-cards">

    <div class="ap-card">
      <p class="ap-card-title">Por estado</p>
      @if($byStatus->isEmpty())
        <p style="color:var(--a-faint);font-size:.88rem;">Nenhuma ordem registada.</p>
      @else
        <ul class="ap-list">
          @foreach($byStatus as $row)
            <li>
              <span>
                @if($row->status === 'paid')
                  <span class="badge bg-green">Pago</span>
                @elseif($row->status === 'awaiting_payment')
                  <span class="badge bg-amber">Aguarda</span>
                @elseif($row->status === 'failed')
                  <span class="badge bg-red">Falhou</span>
                @else
                  <span class="badge bg-gray">{{ $row->status }}</span>
                @endif
              </span>
              <span style="color:var(--a-muted);">
                {{ $row->total }} ordens &nbsp;&mdash;&nbsp;
                <strong style="color:var(--a-text);">{{ number_format($row->total_amount ?? 0, 0, ',', '.') }} AOA</strong>
              </span>
            </li>
          @endforeach
        </ul>
      @endif
    </div>

    <div class="ap-card">
      <p class="ap-card-title">&Uacute;ltimos dias</p>
      @if($latestDays->isEmpty())
        <p style="color:var(--a-faint);font-size:.88rem;">Nenhuma ordem registada.</p>
      @else
        <ul class="ap-list">
          @foreach($latestDays as $row)
            <li>
              <span style="font-weight:600;">{{ \Carbon\Carbon::parse($row->day)->format('d/m/Y') }}</span>
              <span style="color:var(--a-muted);">
                {{ $row->total }} ordens &nbsp;&mdash;&nbsp;
                <strong style="color:var(--a-text);">{{ number_format($row->total_amount ?? 0, 0, ',', '.') }} AOA</strong>
              </span>
            </li>
          @endforeach
        </ul>
      @endif
    </div>

  </div>

</div></div>
@endsection
