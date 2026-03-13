@extends('layouts.app')

@section('content')
<style>
/* ── Admin WiFi Codes ─────────────────────────────────── */
:root {
  --a-bg:     #f4f6f9;
  --a-surf:   #ffffff;
  --a-border: #dde2ea;
  --a-text:   #1a202c;
  --a-muted:  #64748b;
  --a-faint:  #9aa5b4;
  --a-blue:   #3b82f6;
  --a-indigo: #4f46e5;
  --a-green:  #16a34a;
  --a-amber:  #d97706;
  --a-red:    #dc2626;
  --a-purple: #7c3aed;
}
.ap { font-family: Inter, system-ui, sans-serif; background: var(--a-bg); min-height: 60vh; padding: 2rem 0 4rem; color: var(--a-text); }
.ap-wrap { max-width: 1140px; margin: 0 auto; padding: 0 1.5rem; }

/* header */
.ap-topbar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .75rem; margin-bottom: 1.75rem; }
.ap-topbar h1 { font-size: 1.35rem; font-weight: 800; margin: 0 0 .15rem; letter-spacing: -.02em; color: var(--a-text); }
.ap-topbar .ap-sub { font-size: .78rem; color: var(--a-faint); }
.ap-back { font-size: .82rem; font-weight: 600; color: var(--a-muted); text-decoration: none; padding: .4rem .85rem; border: 1px solid var(--a-border); border-radius: 7px; background: var(--a-surf); transition: background .15s; }
.ap-back:hover { background: var(--a-border); color: var(--a-text); }

/* alerts */
.ap-ok  { background: #f0fdf4; border: 1px solid #86efac; border-left: 4px solid var(--a-green); color: #166534; padding: .75rem 1rem; border-radius: 8px; font-size: .875rem; margin-bottom: 1rem; }
.ap-err { background: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid var(--a-red);   color: #7f1d1d; padding: .75rem 1rem; border-radius: 8px; font-size: .875rem; margin-bottom: 1rem; }

/* stat row */
.ap-stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: .75rem; margin-bottom: .85rem; }
.ap-stat { background: var(--a-surf); border: 1px solid var(--a-border); border-radius: 10px; padding: 1rem 1.1rem; }
.ap-stat-val { font-size: 1.7rem; font-weight: 800; line-height: 1; margin: 0 0 .2rem; }
.ap-stat-lbl { font-size: .75rem; color: var(--a-muted); font-weight: 500; }

/* plan stat cards */
.ap-plans { display: grid; grid-template-columns: repeat(3, 1fr); gap: .75rem; margin-bottom: 2rem; }
@media (max-width: 600px) { .ap-plans { grid-template-columns: 1fr; } }
.ap-plan { background: var(--a-surf); border: 1px solid var(--a-border); border-radius: 10px; padding: 1.1rem 1.25rem; border-top: 3px solid; }
.ap-plan-diario  { border-top-color: var(--a-blue); }
.ap-plan-semanal { border-top-color: var(--a-purple); }
.ap-plan-mensal  { border-top-color: var(--a-amber); }
.ap-plan-name { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--a-faint); margin: 0 0 .35rem; }
.ap-plan-val  { font-size: 2rem; font-weight: 800; line-height: 1; margin: 0; }
.ap-plan-note { font-size: .78rem; margin: .3rem 0 0; }
.c-ok  { color: var(--a-green); }
.c-low { color: var(--a-amber); }
.c-out { color: var(--a-red);   }
.c-dim { color: var(--a-muted); }

/* section label */
.ap-sec { font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: var(--a-faint); margin: 0 0 .85rem; display: flex; align-items: center; gap: .6rem; }
.ap-sec::after { content: ''; flex: 1; height: 1px; background: var(--a-border); }

/* import grid */
.ap-import-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(290px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.ap-card { background: var(--a-surf); border: 1px solid var(--a-border); border-radius: 11px; padding: 1.4rem; display: flex; flex-direction: column; }
.ap-card-title { font-size: .92rem; font-weight: 700; margin: 0 0 .2rem; color: var(--a-text); }
.ap-card-sub   { font-size: .78rem; color: var(--a-muted); margin: 0 0 1rem; }
.ap-card form  { flex: 1; display: flex; flex-direction: column; }
.ap-card-body  { flex: 1; }

/* fields */
.ap-field { margin-bottom: .8rem; }
.ap-label { display: block; font-size: .77rem; font-weight: 600; color: #374151; margin-bottom: .3rem; }
.ap-label sup { color: var(--a-red); }
.ap-ctrl  { width: 100%; box-sizing: border-box; padding: .55rem .75rem; border: 1.5px solid var(--a-border); border-radius: 8px; font-size: .875rem; color: var(--a-text); background: #f8fafc; font-family: inherit; outline: none; transition: border-color .15s, box-shadow .15s; }
.ap-ctrl:focus { border-color: #818cf8; box-shadow: 0 0 0 3px rgba(99,102,241,.12); background: #fff; }
.ap-ctrl-mono { font-family: 'Courier New', monospace; }
.ap-file-zone { border: 2px dashed var(--a-border); border-radius: 9px; padding: 1.5rem 1rem; text-align: center; background: #f8fafc; cursor: pointer; transition: border-color .2s, background .2s; position: relative; margin-top: .3rem; }
.ap-file-zone:hover, .ap-file-zone.ready { border-color: #818cf8; background: #eef2ff; }
.ap-file-zone input[type=file] { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
.ap-file-label  { font-size: .82rem; color: var(--a-muted); }
.ap-file-hint   { font-size: .72rem; color: var(--a-faint); margin-top: .25rem; }
.ap-file-chosen { font-size: .82rem; color: var(--a-indigo); font-weight: 600; margin-top: .25rem; display: none; }

/* button */
.ap-foot { margin-top: auto; padding-top: 1rem; }
.ap-btn  { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; padding: .6rem 1.2rem; border-radius: 8px; font-size: .875rem; font-weight: 700; border: none; cursor: pointer; font-family: inherit; transition: background .15s; text-decoration: none; white-space: nowrap; }
.ap-btn-primary { background: var(--a-indigo); color: #fff; width: 100%; justify-content: center; }
.ap-btn-primary:hover { background: #4338ca; }
.ap-btn-outline { background: var(--a-surf); color: var(--a-muted); border: 1.5px solid var(--a-border); }
.ap-btn-outline:hover { background: var(--a-border); color: var(--a-text); }
.ap-btn-sm { padding: .35rem .75rem; font-size: .8rem; }
.ap-btn-del { background: none; border: none; color: var(--a-red); cursor: pointer; font-size: .8rem; padding: .25rem .45rem; border-radius: 5px; font-family: inherit; }
.ap-btn-del:hover { background: #fee2e2; }

/* filter bar */
.ap-filters { background: var(--a-surf); border: 1px solid var(--a-border); border-radius: 10px; padding: .9rem 1.1rem; display: flex; flex-wrap: wrap; gap: .65rem; align-items: flex-end; margin-bottom: 1.1rem; }
.ap-fg { display: flex; flex-direction: column; gap: .25rem; }
.ap-fg.grow { flex: 1; min-width: 170px; }

/* table */
.ap-tcard { background: var(--a-surf); border: 1px solid var(--a-border); border-radius: 10px; overflow: hidden; }
.ap-table { width: 100%; border-collapse: collapse; font-size: .845rem; }
.ap-table thead { background: #f8fafc; }
.ap-table th { text-align: left; padding: .65rem 1rem; font-size: .69rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--a-faint); border-bottom: 1px solid var(--a-border); white-space: nowrap; }
.ap-table td { padding: .6rem 1rem; border-bottom: 1px solid #f4f6f9; vertical-align: middle; color: #374151; }
.ap-table tbody tr:last-child td { border-bottom: none; }
.ap-table tbody tr:hover td { background: #fafbff; }
.ap-table .mono { font-family: 'Courier New', monospace; font-weight: 700; font-size: .82rem; }
.ap-table .dim  { color: var(--a-faint); font-size: .82rem; }

/* badges */
.badge { display: inline-block; padding: .2rem .6rem; border-radius: 999px; font-size: .73rem; font-weight: 700; white-space: nowrap; }
.bg-blue   { background: #dbeafe; color: #1d4ed8; }
.bg-purple { background: #ede9fe; color: #6d28d9; }
.bg-amber  { background: #fef3c7; color: #b45309; }
.bg-green  { background: #dcfce7; color: #15803d; }
.bg-gray   { background: #f1f5f9; color: #475569; }
.bg-orange { background: #ffedd5; color: #9a3412; }

/* empty */
.ap-empty { padding: 3rem 1rem; text-align: center; color: var(--a-faint); }
.ap-empty-title { font-size: .95rem; font-weight: 700; color: var(--a-muted); margin: 0 0 .3rem; }
.ap-empty-sub   { font-size: .82rem; margin: 0; }

/* pager */
.ap-pager { padding: .7rem 1rem; border-top: 1px solid var(--a-border); background: #f8fafc; }
</style>

<div class="ap">
<div class="ap-wrap">

  {{-- ── Cabeçalho ───────────────────────────────────────── --}}
  <div class="ap-topbar">
    <div>
      <h1>C&oacute;digos WiFi</h1>
      <p class="ap-sub">Admin &rsaquo; Stock de Vouchers por Plano</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="ap-back">&larr; Dashboard</a>
  </div>

  {{-- ── Alertas ──────────────────────────────────────────── --}}
  @if(session('success'))
    <div class="ap-ok">&#10003; {{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="ap-err">
      @foreach($errors->all() as $e)<div>&#10007; {{ $e }}</div>@endforeach
    </div>
  @endif

  {{-- ── Contadores globais ───────────────────────────────── --}}
  @php
    $available = $statusCounts['available'] ?? 0;
    $used      = $statusCounts['used']      ?? 0;
    $reserved  = $statusCounts['reserved']  ?? 0;
    $planLabels = ['diario' => 'Di&aacute;rio', 'semanal' => 'Semanal', 'mensal' => 'Mensal'];
    $planText   = ['diario' => 'Diário',        'semanal' => 'Semanal', 'mensal' => 'Mensal'];
  @endphp
  <div class="ap-stats">
    <div class="ap-stat">
      <p class="ap-stat-val" style="color:{{ $available > 0 ? 'var(--a-green)' : 'var(--a-red)' }}">{{ $available }}</p>
      <p class="ap-stat-lbl">Dispon&iacute;veis</p>
    </div>
    <div class="ap-stat">
      <p class="ap-stat-val c-dim">{{ $used }}</p>
      <p class="ap-stat-lbl">Utilizados</p>
    </div>
    <div class="ap-stat">
      <p class="ap-stat-val" style="color:var(--a-amber)">{{ $reserved }}</p>
      <p class="ap-stat-lbl">Reservados</p>
    </div>
  </div>

  {{-- ── Stock por plano ──────────────────────────────────── --}}
  <div class="ap-plans">
    @foreach(['diario','semanal','mensal'] as $pid)
      @php
        $n = $planCounts[$pid] ?? 0;
        $cls = $n === 0 ? 'c-out' : ($n < 5 ? 'c-low' : 'c-ok');
      @endphp
      <div class="ap-plan ap-plan-{{ $pid }}">
        <p class="ap-plan-name">{!! $planLabels[$pid] !!}</p>
        <p class="ap-plan-val {{ $cls }}">{{ $n }}</p>
        <p class="ap-plan-note {{ $cls }}">
          @if($n === 0) &otimes; Stock esgotado
          @elseif($n < 5) &#9651; Stock baixo
          @else &#10003; Dispon&iacute;vel
          @endif
        </p>
      </div>
    @endforeach
  </div>

  {{-- ── Importar codes ───────────────────────────────────── --}}
  <p class="ap-sec">Importar novos c&oacute;digos</p>
  <div class="ap-import-grid">

    {{-- Colar lista --}}
    <div class="ap-card">
      <p class="ap-card-title">Colar lista de c&oacute;digos</p>
      <p class="ap-card-sub">Um por linha, ou separados por v&iacute;rgula &mdash; duplicados ignorados</p>
      <form method="POST" action="{{ route('admin.wifi_codes.import_paste') }}">
        @csrf
        <div class="ap-card-body">
          <div class="ap-field">
            <label class="ap-label" for="paste_plan_id">Plano <sup>*</sup></label>
            <select id="paste_plan_id" name="plan_id" required class="ap-ctrl">
              <option value="">&mdash; Escolha o plano &mdash;</option>
              @foreach(['diario','semanal','mensal'] as $pid)
                <option value="{{ $pid }}" @selected(old('plan_id') === $pid)>{{ $planText[$pid] }}</option>
              @endforeach
            </select>
          </div>
          <div class="ap-field">
            <label class="ap-label">C&oacute;digos <sup>*</sup></label>
            <textarea name="codes_text" rows="7" required
              placeholder="ABC123DEF4&#10;XYZ789GHJ1&#10;QWE456RTY7&#10;..."
              class="ap-ctrl ap-ctrl-mono" style="resize:vertical;"></textarea>
          </div>
        </div>
        <div class="ap-foot">
          <button type="submit" class="ap-btn ap-btn-primary">Importar C&oacute;digos</button>
        </div>
      </form>
    </div>

    {{-- Ficheiro CSV --}}
    <div class="ap-card">
      <p class="ap-card-title">Carregar ficheiro CSV / TXT</p>
      <p class="ap-card-sub">Um c&oacute;digo por linha &middot; m&aacute;x. 500&thinsp;MB</p>
      <form method="POST" action="{{ route('admin.wifi_codes.import_csv') }}" enctype="multipart/form-data">
        @csrf
        <div class="ap-card-body">
          <div class="ap-field">
            <label class="ap-label" for="csv_plan_id">Plano <sup>*</sup></label>
            <select id="csv_plan_id" name="plan_id" required class="ap-ctrl">
              <option value="">&mdash; Escolha o plano &mdash;</option>
              @foreach(['diario','semanal','mensal'] as $pid)
                <option value="{{ $pid }}" @selected(old('plan_id') === $pid)>{{ $planText[$pid] }}</option>
              @endforeach
            </select>
          </div>
          <div class="ap-field">
            <label class="ap-label">Ficheiro <sup>*</sup></label>
            <label class="ap-file-zone" id="dropZone" for="csvFileInput">
              <p class="ap-file-label">Clique para seleccionar ou arraste o ficheiro</p>
              <p class="ap-file-hint">.csv ou .txt &mdash; m&aacute;ximo 500 MB</p>
              <p class="ap-file-chosen" id="chosenName"></p>
              <input type="file" id="csvFileInput" name="csv_file" accept=".csv,.txt,text/plain" required
                     onchange="
                       var n = this.files[0] ? this.files[0].name : '';
                       var cn = document.getElementById('chosenName');
                       cn.textContent = n ? '&#10003; ' + n : '';
                       cn.style.display = n ? 'block' : 'none';
                       document.getElementById('dropZone').classList.toggle('ready', !!n);">
            </label>
          </div>
        </div>
        <div class="ap-foot">
          <button type="submit" class="ap-btn ap-btn-primary">Carregar Ficheiro</button>
        </div>
      </form>
    </div>
  </div>

  {{-- ── Filtrar ────────────────────────────────────────────── --}}
  <p class="ap-sec">Pesquisar e filtrar stock</p>
  <form method="get" class="ap-filters">
    <div class="ap-fg" style="min-width:140px;">
      <label class="ap-label" for="fPlan">Plano</label>
      <select id="fPlan" name="plan_id" class="ap-ctrl">
        <option value="">Todos os planos</option>
        @foreach(['diario','semanal','mensal'] as $pid)
          <option value="{{ $pid }}" @selected(request('plan_id') === $pid)>{{ $planText[$pid] }}</option>
        @endforeach
      </select>
    </div>
    <div class="ap-fg" style="min-width:130px;">
      <label class="ap-label" for="fStatus">Estado</label>
      <select id="fStatus" name="status" class="ap-ctrl">
        <option value="">Todos</option>
        <option value="available" @selected(request('status') === 'available')>Dispon&iacute;vel</option>
        <option value="used"      @selected(request('status') === 'used')>Utilizado</option>
        <option value="reserved"  @selected(request('status') === 'reserved')>Reservado</option>
      </select>
    </div>
    <div class="ap-fg grow">
      <label class="ap-label" for="fQ">Pesquisar c&oacute;digo</label>
      <input id="fQ" name="q" value="{{ request('q') }}" class="ap-ctrl" placeholder="Ex: ABC123DEF4&hellip;">
    </div>
    <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
      <button type="submit" class="ap-btn ap-btn-primary ap-btn-sm" style="width:auto;">Filtrar</button>
      @if(request()->hasAny(['plan_id','status','q']))
        <a href="{{ route('admin.wifi_codes.index') }}" class="ap-btn ap-btn-outline ap-btn-sm">Limpar</a>
      @endif
    </div>
  </form>

  {{-- ── Tabela ─────────────────────────────────────────────── --}}
  <div class="ap-tcard">
    <div style="overflow-x:auto;">
      <table class="ap-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Plano</th>
            <th>C&oacute;digo</th>
            <th>Estado</th>
            <th>Ordem</th>
            <th>Utilizado em</th>
            <th>Importado em</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($codes as $code)
            <tr>
              <td class="dim">#{{ $code->id }}</td>
              <td>
                @if($code->plan_id === 'diario')
                  <span class="badge bg-amber">Di&aacute;rio</span>
                @elseif($code->plan_id === 'semanal')
                  <span class="badge bg-amber">Semanal</span>
                @elseif($code->plan_id === 'mensal')
                  <span class="badge bg-amber">Mensal</span>
                @else
                  <span class="badge bg-orange">sem plano</span>
                @endif
              </td>
              <td class="mono">{{ $code->code }}</td>
              <td>
                @if($code->status === 'available')
                  <span class="badge bg-green">Dispon&iacute;vel</span>
                @elseif($code->status === 'used')
                  <span class="badge bg-gray">Utilizado</span>
                @else
                  <span class="badge bg-orange">Reservado</span>
                @endif
              </td>
              <td>
                @if($code->autovenda_order_id)
                  <a href="{{ route('admin.autovenda.index', ['q' => $code->autovenda_order_id]) }}"
                     style="color:var(--a-indigo);font-weight:600;text-decoration:none;">#{{ $code->autovenda_order_id }}</a>
                @else <span class="dim">&mdash;</span>
                @endif
              </td>
              <td class="dim">{{ optional($code->used_at)->format('d/m/Y H:i') ?: '&mdash;' }}</td>
              <td class="dim">{{ optional($code->created_at)->format('d/m/Y H:i') }}</td>
              <td>
                @if($code->status === 'available')
                  <form method="POST" action="{{ route('admin.wifi_codes.destroy', $code) }}"
                        onsubmit="return confirm('Eliminar o c\u00f3digo {{ $code->code }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="ap-btn-del" title="Eliminar">&#128465;</button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8">
                <div class="ap-empty">
                  <p class="ap-empty-title">Nenhum c&oacute;digo encontrado</p>
                  <p class="ap-empty-sub">Importe c&oacute;digos acima para come&ccedil;ar</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($codes->hasPages())
      <div class="ap-pager">{{ $codes->links() }}</div>
    @endif
  </div>

</div>{{-- /ap-wrap --}}
</div>{{-- /ap --}}
@endsection