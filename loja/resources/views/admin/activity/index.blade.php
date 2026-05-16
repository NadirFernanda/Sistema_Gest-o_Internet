@extends('layouts.app')

@section('title', 'Actividade da Loja — Admin')

@section('content')
<style>
:root{--a-bg:#f4f6f9;--a-surf:#fff;--a-border:#dde2ea;--a-text:#1a202c;--a-muted:#64748b;--a-faint:#9aa5b4;--a-brand:#f7b500;--a-green:#16a34a;--a-amber:#d97706;--a-red:#dc2626;--a-purple:#7c3aed;--a-teal:#0d9488;}
.ac{font-family:Inter,system-ui,sans-serif;background:var(--a-bg);min-height:60vh;padding:2rem 0 4rem;color:var(--a-text);}
.ac-wrap{max-width:1200px;margin:0 auto;padding:0 1.5rem;}
.ac-topbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;}
.ac-topbar h1{font-size:1.35rem;font-weight:800;margin:0 0 .15rem;letter-spacing:-.02em;}
.ac-topbar .ac-sub{font-size:.78rem;color:var(--a-faint);margin:0;}
.ac-back{font-size:.82rem;font-weight:600;color:var(--a-muted);text-decoration:none;padding:.4rem .85rem;border:1px solid var(--a-border);border-radius:7px;background:var(--a-surf);transition:background .15s;}
.ac-back:hover{background:var(--a-border);}

/* Nav */
.ac-nav{display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:1.75rem;}
.ac-nav a{font-size:.8rem;font-weight:600;padding:.38rem .85rem;border-radius:7px;border:1px solid var(--a-border);background:var(--a-surf);color:var(--a-muted);text-decoration:none;}
.ac-nav a:hover,.ac-nav a.here{background:rgba(247,181,0,.13);border-color:var(--a-brand);color:#7a4f00;}

/* Period switcher */
.ac-periods{display:flex;gap:.4rem;flex-wrap:wrap;}
.ac-period{padding:.35rem .85rem;border-radius:8px;border:1.5px solid var(--a-border);background:var(--a-surf);color:var(--a-muted);font-size:.8rem;font-weight:700;cursor:pointer;font-family:inherit;transition:all .15s;}
.ac-period:hover{border-color:var(--a-brand);color:#7a4f00;}
.ac-period.active{background:var(--a-brand);border-color:var(--a-brand);color:#1a202c;}

/* KPI strip */
.ac-kpis{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:.75rem;margin-bottom:1.5rem;}
.ac-kpi{background:var(--a-surf);border:1px solid var(--a-border);border-radius:12px;padding:1.1rem 1.2rem;border-top:3px solid var(--a-border);transition:border-top-color .3s;}
.ac-kpi.k-visitors{border-top-color:var(--a-teal);}
.ac-kpi.k-orders{border-top-color:var(--a-brand);}
.ac-kpi.k-family{border-top-color:var(--a-green);}
.ac-kpi.k-appts{border-top-color:var(--a-purple);}
.ac-kpi.k-equip{border-top-color:var(--a-red);}
.ac-kpi.k-online{border-top-color:#0ea5e9;}
.ac-kpi-val{font-size:1.9rem;font-weight:900;line-height:1;margin:0 0 .25rem;letter-spacing:-.03em;}
.ac-kpi-lbl{font-size:.72rem;color:var(--a-muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em;}
.ac-kpi-sub{font-size:.7rem;color:var(--a-faint);margin:.3rem 0 0;}

/* Chart card */
.ac-chart-card{background:var(--a-surf);border:1px solid var(--a-border);border-radius:14px;padding:1.5rem 1.5rem 1rem;margin-bottom:1.5rem;}
.ac-chart-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;}
.ac-chart-title{font-size:.92rem;font-weight:800;color:var(--a-text);margin:0;}
.ac-chart-legend{display:flex;flex-wrap:wrap;gap:.75rem;}
.ac-legend-item{display:flex;align-items:center;gap:.35rem;font-size:.72rem;font-weight:600;color:var(--a-muted);}
.ac-legend-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}

.ac-canvas-wrap{position:relative;height:260px;}

/* Breakdown table */
.ac-breakdown{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.75rem;}
.ac-bk-card{background:var(--a-surf);border:1px solid var(--a-border);border-radius:12px;padding:1.1rem 1.25rem;}
.ac-bk-header{display:flex;align-items:center;gap:.6rem;margin-bottom:.85rem;}
.ac-bk-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;}
.ac-bk-title{font-size:.8rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--a-muted);}
.ac-bk-val{font-size:2rem;font-weight:900;line-height:1;letter-spacing:-.03em;margin:0;}
.ac-bk-sub{font-size:.72rem;color:var(--a-faint);margin:.2rem 0 0;}

/* Loading overlay */
.ac-loading{display:none;position:absolute;inset:0;background:rgba(255,255,255,.75);backdrop-filter:blur(2px);border-radius:8px;align-items:center;justify-content:center;}
.ac-loading.show{display:flex;}
@keyframes ac-spin{to{transform:rotate(360deg);}}
.ac-spinner{width:24px;height:24px;border:3px solid #e2e8f0;border-top-color:var(--a-brand);border-radius:50%;animation:ac-spin .7s linear infinite;}

.ac-online-badge{display:inline-flex;align-items:center;gap:.35rem;padding:.25rem .65rem;background:#e0f2fe;border:1px solid #7dd3fc;border-radius:999px;font-size:.72rem;font-weight:700;color:#0369a1;}
.ac-online-dot{width:7px;height:7px;background:#0ea5e9;border-radius:50%;animation:ac-pulse 1.5s ease-in-out infinite;}
@keyframes ac-pulse{0%,100%{opacity:1;}50%{opacity:.35;}}

@media(max-width:600px){.ac-canvas-wrap{height:200px;} .ac-kpi-val{font-size:1.55rem;}}
</style>

<div class="ac"><div class="ac-wrap">

  {{-- Topbar --}}
  <div class="ac-topbar">
    <div>
      <h1>Actividade da Loja</h1>
      <p class="ac-sub">Histórico de visitas e transacções — actualizado em tempo real</p>
    </div>
    <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
      <span class="ac-online-badge">
        <span class="ac-online-dot"></span>
        {{ $onlineNow }} online agora
      </span>
      <a href="{{ route('admin.dashboard') }}" class="ac-back">&larr; Dashboard</a>
    </div>
  </div>

  {{-- Nav --}}
  <nav class="ac-nav">
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <a href="{{ route('admin.autovenda.index') }}">Recargas</a>
    <a href="{{ route('admin.wifi_codes.index') }}">C&oacute;digos WiFi</a>
    <a href="{{ route('admin.voucher_plans.index') }}">Planos Voucher</a>
    <a href="{{ route('admin.manual_voucher_sale.create') }}">Venda Manual</a>
    <a href="{{ route('admin.resellers.index') }}">Revendedores</a>
    <a href="{{ route('admin.appointments.index') }}">Agendamentos</a>
    <a href="{{ route('admin.equipment.orders.index') }}">Encomendas</a>
    <a href="{{ route('admin.equipment.products.index') }}">Produtos</a>
    <a href="{{ route('admin.family_requests.index') }}">Planos</a>
    <a href="{{ route('admin.site_stats.index') }}">Estat&iacute;sticas</a>
    <a href="{{ route('admin.activity.index') }}" class="here">Actividade</a>
    <a href="{{ route('admin.reports') }}">Relat&oacute;rios</a>
  </nav>

  {{-- Period switcher --}}
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1.25rem;">
    <p style="font-size:.8rem;font-weight:700;color:var(--a-muted);margin:0;">Período de análise:</p>
    <div class="ac-periods">
      <button class="ac-period active" data-period="24h">Últimas 24h</button>
      <button class="ac-period" data-period="7d">7 dias</button>
      <button class="ac-period" data-period="30d">30 dias</button>
      <button class="ac-period" data-period="90d">3 meses</button>
    </div>
  </div>

  {{-- KPI strip --}}
  <div class="ac-kpis" id="kpiStrip">
    <div class="ac-kpi k-visitors">
      <div class="ac-kpi-val" id="kv-visitors">—</div>
      <div class="ac-kpi-lbl">Visitantes únicos</div>
      <div class="ac-kpi-sub" id="ks-visitors">no período</div>
    </div>
    <div class="ac-kpi k-orders">
      <div class="ac-kpi-val" id="kv-orders">—</div>
      <div class="ac-kpi-lbl">Recargas WiFi</div>
      <div class="ac-kpi-sub">autovenda</div>
    </div>
    <div class="ac-kpi k-family">
      <div class="ac-kpi-val" id="kv-family">—</div>
      <div class="ac-kpi-lbl">Planos família</div>
      <div class="ac-kpi-sub">pedidos</div>
    </div>
    <div class="ac-kpi k-appts">
      <div class="ac-kpi-val" id="kv-appts">—</div>
      <div class="ac-kpi-lbl">Agendamentos</div>
      <div class="ac-kpi-sub">instalação</div>
    </div>
    <div class="ac-kpi k-equip">
      <div class="ac-kpi-val" id="kv-equip">—</div>
      <div class="ac-kpi-lbl">Encomendas</div>
      <div class="ac-kpi-sub">equipamentos</div>
    </div>
    <div class="ac-kpi k-online">
      <div class="ac-kpi-val">{{ $onlineNow }}</div>
      <div class="ac-kpi-lbl">Online agora</div>
      <div class="ac-kpi-sub">últimos 5 min</div>
    </div>
  </div>

  {{-- Main chart --}}
  <div class="ac-chart-card">
    <div class="ac-chart-head">
      <h3 class="ac-chart-title" id="chartTitle">Actividade — últimas 24 horas</h3>
      <div class="ac-chart-legend">
        <span class="ac-legend-item"><span class="ac-legend-dot" style="background:#0d9488"></span>Visitantes</span>
        <span class="ac-legend-item"><span class="ac-legend-dot" style="background:#f7b500"></span>Recargas</span>
        <span class="ac-legend-item"><span class="ac-legend-dot" style="background:#16a34a"></span>Planos fam.</span>
        <span class="ac-legend-item"><span class="ac-legend-dot" style="background:#7c3aed"></span>Agendamentos</span>
        <span class="ac-legend-item"><span class="ac-legend-dot" style="background:#dc2626"></span>Encomendas</span>
      </div>
    </div>
    <div class="ac-canvas-wrap">
      <canvas id="activityChart"></canvas>
      <div class="ac-loading" id="chartLoader"><div class="ac-spinner"></div></div>
    </div>
  </div>

  {{-- Breakdown cards --}}
  <div class="ac-breakdown" id="breakdownGrid">
    <div class="ac-bk-card">
      <div class="ac-bk-header"><span class="ac-bk-dot" style="background:#0d9488"></span><span class="ac-bk-title">Visitantes únicos</span></div>
      <div class="ac-bk-val" id="bk-visitors">—</div>
      <div class="ac-bk-sub">sessões registadas no período</div>
    </div>
    <div class="ac-bk-card">
      <div class="ac-bk-header"><span class="ac-bk-dot" style="background:#f7b500"></span><span class="ac-bk-title">Recargas WiFi</span></div>
      <div class="ac-bk-val" id="bk-orders">—</div>
      <div class="ac-bk-sub">pedidos de autovenda</div>
    </div>
    <div class="ac-bk-card">
      <div class="ac-bk-header"><span class="ac-bk-dot" style="background:#16a34a"></span><span class="ac-bk-title">Planos Família</span></div>
      <div class="ac-bk-val" id="bk-family">—</div>
      <div class="ac-bk-sub">planos familiares / empresariais</div>
    </div>
    <div class="ac-bk-card">
      <div class="ac-bk-header"><span class="ac-bk-dot" style="background:#7c3aed"></span><span class="ac-bk-title">Agendamentos</span></div>
      <div class="ac-bk-val" id="bk-appts">—</div>
      <div class="ac-bk-sub">pedidos de instalação</div>
    </div>
    <div class="ac-bk-card">
      <div class="ac-bk-header"><span class="ac-bk-dot" style="background:#dc2626"></span><span class="ac-bk-title">Encomendas</span></div>
      <div class="ac-bk-val" id="bk-equip">—</div>
      <div class="ac-bk-sub">equipamentos encomendados</div>
    </div>
  </div>

</div></div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {

  var dataUrl   = '{{ route('admin.activity.data') }}';
  var chartInst = null;
  var curPeriod = '24h';

  var periodTitles = {
    '24h': 'Actividade — últimas 24 horas',
    '7d':  'Actividade — últimos 7 dias',
    '30d': 'Actividade — últimos 30 dias',
    '90d': 'Actividade — últimos 3 meses',
  };

  var COLORS = {
    visitors: { line: '#0d9488', fill: 'rgba(13,148,136,.08)' },
    orders:   { line: '#f7b500', fill: 'rgba(247,181,0,.10)' },
    family:   { line: '#16a34a', fill: 'rgba(22,163,74,.08)' },
    appts:    { line: '#7c3aed', fill: 'rgba(124,58,237,.08)' },
    equip:    { line: '#dc2626', fill: 'rgba(220,38,38,.07)' },
  };

  function buildDatasets(json) {
    return [
      { label: 'Visitantes', data: json.visitors, borderColor: COLORS.visitors.line, backgroundColor: COLORS.visitors.fill, tension: .35, fill: true, pointRadius: 3 },
      { label: 'Recargas',   data: json.orders,   borderColor: COLORS.orders.line,   backgroundColor: COLORS.orders.fill,   tension: .35, fill: false, pointRadius: 3 },
      { label: 'Planos fam.',data: json.family,   borderColor: COLORS.family.line,   backgroundColor: COLORS.family.fill,   tension: .35, fill: false, pointRadius: 3 },
      { label: 'Agendamentos',data: json.appts,   borderColor: COLORS.appts.line,    backgroundColor: COLORS.appts.fill,    tension: .35, fill: false, pointRadius: 3 },
      { label: 'Encomendas', data: json.equipment,borderColor: COLORS.equip.line,    backgroundColor: COLORS.equip.fill,    tension: .35, fill: false, pointRadius: 3 },
    ];
  }

  function initChart(labels, datasets) {
    var ctx = document.getElementById('activityChart').getContext('2d');
    if (chartInst) { chartInst.destroy(); }
    chartInst = new Chart(ctx, {
      type: 'line',
      data: { labels: labels, datasets: datasets },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#1a202c',
            titleColor: '#f7b500',
            bodyColor: '#e2e8f0',
            borderColor: '#374151',
            borderWidth: 1,
            padding: 10,
            callbacks: {
              title: function (items) { return items[0].label; }
            }
          }
        },
        scales: {
          x: {
            grid: { color: '#f1f5f9', drawBorder: false },
            ticks: { color: '#94a3b8', font: { size: 11 }, maxRotation: 0,
              maxTicksLimit: curPeriod === '24h' ? 12 : (curPeriod === '90d' ? 13 : 15) }
          },
          y: {
            beginAtZero: true,
            grid: { color: '#f1f5f9', drawBorder: false },
            ticks: { color: '#94a3b8', font: { size: 11 }, precision: 0 }
          }
        }
      }
    });
  }

  function updateKpis(totals, period) {
    document.getElementById('kv-visitors').textContent = totals.visitors.toLocaleString('pt-PT');
    document.getElementById('kv-orders').textContent   = totals.orders.toLocaleString('pt-PT');
    document.getElementById('kv-family').textContent   = totals.family.toLocaleString('pt-PT');
    document.getElementById('kv-appts').textContent    = totals.appts.toLocaleString('pt-PT');
    document.getElementById('kv-equip').textContent    = totals.equipment.toLocaleString('pt-PT');
    document.getElementById('bk-visitors').textContent = totals.visitors.toLocaleString('pt-PT');
    document.getElementById('bk-orders').textContent   = totals.orders.toLocaleString('pt-PT');
    document.getElementById('bk-family').textContent   = totals.family.toLocaleString('pt-PT');
    document.getElementById('bk-appts').textContent    = totals.appts.toLocaleString('pt-PT');
    document.getElementById('bk-equip').textContent    = totals.equipment.toLocaleString('pt-PT');
    document.getElementById('chartTitle').textContent  = periodTitles[period] || 'Actividade';
  }

  function setLoading(on) {
    document.getElementById('chartLoader').classList.toggle('show', on);
  }

  function loadData(period) {
    curPeriod = period;
    setLoading(true);

    fetch(dataUrl + '?period=' + period, { headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (json) {
        initChart(json.labels, buildDatasets(json));
        updateKpis(json.totals, period);
      })
      .catch(function () {
        document.getElementById('chartTitle').textContent = 'Erro ao carregar dados';
      })
      .finally(function () { setLoading(false); });
  }

  // Period switcher
  document.querySelectorAll('.ac-period').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.ac-period').forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
      loadData(btn.dataset.period);
    });
  });

  // Initial load
  loadData('24h');

  // Auto-refresh every 2 min
  setInterval(function () { loadData(curPeriod); }, 120000);

})();
</script>
@endpush

@endsection
