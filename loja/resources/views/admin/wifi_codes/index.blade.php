@extends('layouts.app')

@section('content')
<style>
/* â”€â”€ Admin Design System â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.adm { --primary:#4f46e5; --primary-hover:#4338ca; --success:#16a34a; --warning:#d97706; --danger:#dc2626;
       --bg:#f1f5f9; --surface:#fff; --border:#e2e8f0; --text:#1e293b; --muted:#64748b; --faint:#94a3b8; }
.adm-page { background:var(--bg); padding:2rem 0 3rem; }
.adm-wrap  { max-width:1200px; margin:0 auto; padding:0 1.5rem; }

/* Header */
.adm-header { display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:.75rem; margin-bottom:2rem; }
.adm-header h1 { font-size:1.45rem; font-weight:800; color:var(--text); margin:0 0 .2rem; letter-spacing:-.02em; }
.adm-crumb { font-size:.78rem; color:var(--faint); }

/* Alerts */
.adm-alert { padding:.85rem 1.1rem; border-radius:10px; margin-bottom:1rem; font-size:.875rem; display:flex; align-items:flex-start; gap:.6rem; }
.adm-alert-ok  { background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; }
.adm-alert-err { background:#fef2f2; border:1px solid #fecaca; color:#991b1b; }

/* Stat cards */
.adm-stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(130px,1fr)); gap:.75rem; margin-bottom:1rem; }
.adm-stat  { background:var(--surface); border:1px solid var(--border); border-radius:10px; padding:1rem 1.1rem;
             box-shadow:0 1px 3px rgba(0,0,0,.05); }
.adm-stat-val  { font-size:1.85rem; font-weight:800; line-height:1; color:var(--text); margin:0 0 .25rem; }
.adm-stat-lbl  { font-size:.75rem; color:var(--muted); font-weight:500; }
.adm-stat-warn { color:var(--danger) !important; }
.adm-plan-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:.75rem; margin-bottom:2rem; }
@media(max-width:600px){ .adm-plan-stats { grid-template-columns:1fr; } }
.adm-plan-stat { background:var(--surface); border:1px solid var(--border); border-radius:10px; padding:1.1rem 1.25rem;
                box-shadow:0 1px 3px rgba(0,0,0,.05); border-top:3px solid; }
.adm-plan-stat.diario  { border-top-color:#3b82f6; }
.adm-plan-stat.semanal { border-top-color:#8b5cf6; }
.adm-plan-stat.mensal  { border-top-color:#f59e0b; }
.adm-plan-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1rem; margin-bottom:.6rem; }
.adm-plan-stat.diario  .adm-plan-icon { background:#dbeafe; }
.adm-plan-stat.semanal .adm-plan-icon { background:#ede9fe; }
.adm-plan-stat.mensal  .adm-plan-icon { background:#fef3c7; }
.adm-plan-stat-name { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--faint); margin:0 0 .3rem; }
.adm-plan-stat-val  { font-size:1.9rem; font-weight:800; color:var(--text); margin:0; line-height:1.1; }
.adm-plan-stat-sub  { font-size:.78rem; color:var(--muted); margin:.25rem 0 0; }
.stock-ok  { color:var(--success) !important; }
.stock-low { color:var(--warning) !important; }
.stock-out { color:var(--danger)  !important; }

/* Import section */
.adm-section-title { font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.1em; color:var(--faint);
                     margin:0 0 .9rem; display:flex; align-items:center; gap:.5rem; }
.adm-section-title::after { content:''; flex:1; height:1px; background:var(--border); }
.adm-import-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:1rem; margin-bottom:2rem; }
.adm-icard { background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:1.5rem; 
             box-shadow:0 1px 4px rgba(0,0,0,.06); display:flex; flex-direction:column; }
.adm-icard-head { display:flex; align-items:center; gap:.75rem; margin-bottom:.6rem; }
.adm-icard-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.15rem; flex-shrink:0; }
.adm-icard-paste .adm-icard-icon { background:#eef2ff; }
.adm-icard-csv   .adm-icard-icon { background:#fef3c7; }
.adm-icard-head-text h3 { font-size:.97rem; font-weight:700; color:var(--text); margin:0; }
.adm-icard-head-text p  { font-size:.78rem; color:var(--muted); margin:0; margin-top:.1rem; }
.adm-icard form { flex:1; display:flex; flex-direction:column; }
.adm-form-body { flex:1; }

/* Fields */
.adm-field  { margin-bottom:.85rem; }
.adm-label  { display:block; font-size:.78rem; font-weight:600; color:#374151; margin-bottom:.3rem; }
.adm-label sup { color:#ef4444; font-size:.85em; }
.adm-ctrl   { width:100%; box-sizing:border-box; padding:.55rem .8rem; border:1.5px solid var(--border);
              border-radius:8px; font-size:.875rem; color:var(--text); background:#f8fafc; font-family:inherit;
              transition:border-color .15s,box-shadow .15s; outline:none; }
.adm-ctrl:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.12); background:#fff; }
.adm-ctrl-mono { font-family:'Courier New',monospace; }

/* File drop zone */
.adm-dropzone { border:2px dashed var(--border); border-radius:10px; padding:1.75rem 1rem; text-align:center;
                background:#f8fafc; cursor:pointer; transition:border-color .2s,background .2s;
                display:flex; flex-direction:column; align-items:center; gap:.4rem; flex:1; }
.adm-dropzone:hover,
.adm-dropzone.has-file { border-color:#6366f1; background:#eef2ff; }
.adm-dropzone input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
.adm-dropzone-wrap { position:relative; flex:1; display:flex; }
.adm-dropzone .dz-icon { font-size:1.75rem; }
.adm-dropzone .dz-text { font-size:.8rem; color:var(--muted); }
.adm-dropzone .dz-hint { font-size:.72rem; color:var(--faint); }
.adm-dropzone .dz-chosen { font-size:.82rem; color:#4f46e5; font-weight:600; display:none; }

/* Buttons */
.adm-btn { display:inline-flex; align-items:center; justify-content:center; gap:.4rem; padding:.6rem 1.2rem;
           border-radius:8px; font-size:.875rem; font-weight:600; border:none; cursor:pointer;
           transition:all .15s; text-decoration:none; white-space:nowrap; font-family:inherit; }
.adm-btn-primary { background:#4f46e5; color:#fff; }
.adm-btn-primary:hover { background:#4338ca; transform:translateY(-1px); box-shadow:0 4px 12px rgba(79,70,229,.3); }
.adm-btn-ghost { background:transparent; color:var(--muted); border:1.5px solid var(--border); }
.adm-btn-ghost:hover { background:var(--border); color:var(--text); }
.adm-btn-danger-ghost { background:transparent; color:var(--danger); border:none; padding:.3rem .5rem; font-size:.8rem; }
.adm-btn-danger-ghost:hover { background:#fee2e2; border-radius:6px; }
.adm-btn-full { width:100%; }
.adm-btn-sm { padding:.38rem .8rem; font-size:.8rem; }
.adm-form-foot { margin-top:auto; padding-top:1rem; }

/* Filter bar */
.adm-filterbar { background:var(--surface); border:1px solid var(--border); border-radius:12px;
                 padding:1rem 1.25rem; display:flex; flex-wrap:wrap; gap:.75rem; align-items:flex-end;
                 margin-bottom:1.25rem; box-shadow:0 1px 3px rgba(0,0,0,.04); }
.adm-fg { display:flex; flex-direction:column; gap:.25rem; }
.adm-fg.grow { flex:1; min-width:180px; }

/* Table */
.adm-tcard { background:var(--surface); border:1px solid var(--border); border-radius:12px; overflow:hidden;
             box-shadow:0 1px 4px rgba(0,0,0,.06); }
.adm-table { width:100%; border-collapse:collapse; font-size:.845rem; }
.adm-table thead tr { background:#f8fafc; }
.adm-table th { text-align:left; padding:.75rem 1rem; font-size:.7rem; font-weight:700; text-transform:uppercase;
                letter-spacing:.06em; color:var(--faint); border-bottom:1px solid var(--border); white-space:nowrap; }
.adm-table td { padding:.65rem 1rem; border-bottom:1px solid #f8fafc; color:#374151; vertical-align:middle; }
.adm-table tbody tr:last-child td { border-bottom:none; }
.adm-table tbody tr:hover td { background:#fafbff; }
.adm-table .col-mono { font-family:'Courier New',monospace; font-weight:700; color:var(--text); font-size:.82rem; letter-spacing:.03em; }
.adm-table .col-dim  { color:var(--faint); }
.adm-table .col-link a { color:#4f46e5; text-decoration:none; font-weight:600; }
.adm-table .col-link a:hover { text-decoration:underline; }

/* Badges */
.adm-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.22rem .65rem; border-radius:999px; font-size:.75rem; font-weight:600; white-space:nowrap; }
.badge-green  { background:#dcfce7; color:#15803d; }
.badge-gray   { background:#f1f5f9; color:#475569; }
.badge-amber  { background:#fef3c7; color:#b45309; }
.badge-blue   { background:#dbeafe; color:#1d4ed8; }
.badge-purple { background:#ede9fe; color:#6d28d9; }
.badge-gold   { background:#fef9c3; color:#a16207; }
.badge-warn   { background:#fff7ed; color:#c2410c; }

/* Empty state */
.adm-empty { text-align:center; padding:3rem 1rem; color:var(--faint); }
.adm-empty .adm-empty-icon { font-size:2.5rem; margin-bottom:.5rem; }
.adm-empty p { margin:.25rem 0; font-size:.875rem; }

/* Pagination */
.adm-pager { padding: .75rem 1.25rem; border-top:1px solid var(--border); background:#fafafa; }
</style>

<div class="adm">
<div class="adm-page">
<div class="adm-wrap">

  {{-- Page header --}}
  <div class="adm-header">
    <div>
      <h1>ðŸ”‘ CÃ³digos WiFi</h1>
      <p class="adm-crumb">Admin â€º Stock de Vouchers por Plano</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="adm-btn adm-btn-ghost adm-btn-sm">â† Dashboard</a>
  </div>

  {{-- Alerts --}}
  @if(session('success'))
    <div class="adm-alert adm-alert-ok">
      <span>âœ“</span> <span>{{ session('success') }}</span>
    </div>
  @endif
  @if($errors->any())
    <div class="adm-alert adm-alert-err">
      <span>âœ•</span>
      <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    </div>
  @endif

  {{-- Global stats --}}
  @php
    $available = $statusCounts['available'] ?? 0;
    $used      = $statusCounts['used']      ?? 0;
    $reserved  = $statusCounts['reserved']  ?? 0;
    $planLabels = ['diario' => 'DiÃ¡rio', 'semanal' => 'Semanal', 'mensal' => 'Mensal'];
    $planIcons  = ['diario' => 'â˜€ï¸', 'semanal' => 'ðŸ“…', 'mensal' => 'ðŸ—“ï¸'];
  @endphp
  <div class="adm-stats" style="margin-bottom:.75rem;">
    <div class="adm-stat">
      <p class="adm-stat-val" style="color:{{ $available > 0 ? 'var(--success)' : 'var(--danger)' }}">{{ $available }}</p>
      <p class="adm-stat-lbl">DisponÃ­veis</p>
    </div>
    <div class="adm-stat">
      <p class="adm-stat-val" style="color:var(--muted)">{{ $used }}</p>
      <p class="adm-stat-lbl">Utilizados</p>
    </div>
    <div class="adm-stat">
      <p class="adm-stat-val" style="color:var(--warning)">{{ $reserved }}</p>
      <p class="adm-stat-lbl">Reservados</p>
    </div>
  </div>

  {{-- Per-plan stats --}}
  <div class="adm-plan-stats">
    @foreach($planLabels as $pid => $plabel)
      @php
        $n = $planCounts[$pid] ?? 0;
        $cls = $n === 0 ? 'stock-out' : ($n < 5 ? 'stock-low' : 'stock-ok');
      @endphp
      <div class="adm-plan-stat {{ $pid }}">
        <div class="adm-plan-icon">{{ $planIcons[$pid] }}</div>
        <p class="adm-plan-stat-name">{{ $plabel }}</p>
        <p class="adm-plan-stat-val {{ $cls }}">{{ $n }}</p>
        <p class="adm-plan-stat-sub">
          @if($n === 0) <span class="stock-out">âš  Stock esgotado</span>
          @elseif($n < 5) <span class="stock-low">âš  Stock baixo</span>
          @else <span class="stock-ok">âœ“ DisponÃ­vel</span>
          @endif
        </p>
      </div>
    @endforeach
  </div>

  {{-- â”€â”€ IMPORT â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
  <p class="adm-section-title">Importar novos cÃ³digos</p>
  <div class="adm-import-grid">

    {{-- Paste --}}
    <div class="adm-icard adm-icard-paste">
      <div class="adm-icard-head">
        <div class="adm-icard-icon">âœï¸</div>
        <div class="adm-icard-head-text">
          <h3>Colar lista de cÃ³digos</h3>
          <p>Um por linha, ou separados por vÃ­rgula</p>
        </div>
      </div>
      <form method="POST" action="{{ route('admin.wifi_codes.import_paste') }}">
        @csrf
        <div class="adm-form-body">
          <div class="adm-field">
            <label class="adm-label" for="paste_plan_id">Plano <sup>*</sup></label>
            <select id="paste_plan_id" name="plan_id" required class="adm-ctrl">
              <option value="">â€” Escolha o plano â€”</option>
              @foreach($planLabels as $pid => $plabel)
                <option value="{{ $pid }}" @selected(old('plan_id') === $pid)>{{ $planIcons[$pid] }} {{ $plabel }}</option>
              @endforeach
            </select>
          </div>
          <div class="adm-field" style="flex:1;display:flex;flex-direction:column;">
            <label class="adm-label">CÃ³digos <sup>*</sup></label>
            <textarea name="codes_text" rows="6" required
              placeholder="ABC123DEF4&#10;XYZ789GHJ1&#10;QWE456RTY7&#10;..."
              class="adm-ctrl adm-ctrl-mono" style="flex:1;min-height:130px;"></textarea>
          </div>
        </div>
        <div class="adm-form-foot">
          <button type="submit" class="adm-btn adm-btn-primary adm-btn-full">
            â†‘ Importar CÃ³digos
          </button>
        </div>
      </form>
    </div>

    {{-- CSV --}}
    <div class="adm-icard adm-icard-csv">
      <div class="adm-icard-head">
        <div class="adm-icard-icon">ðŸ“„</div>
        <div class="adm-icard-head-text">
          <h3>Carregar ficheiro CSV / TXT</h3>
          <p>Um cÃ³digo por linha Â· mÃ¡x. 500 MB</p>
        </div>
      </div>
      <form method="POST" action="{{ route('admin.wifi_codes.import_csv') }}" enctype="multipart/form-data" id="csvForm">
        @csrf
        <div class="adm-form-body" style="display:flex;flex-direction:column;gap:.85rem;">
          <div class="adm-field" style="margin-bottom:0;">
            <label class="adm-label" for="csv_plan_id">Plano <sup>*</sup></label>
            <select id="csv_plan_id" name="plan_id" required class="adm-ctrl">
              <option value="">â€” Escolha o plano â€”</option>
              @foreach($planLabels as $pid => $plabel)
                <option value="{{ $pid }}" @selected(old('plan_id') === $pid)>{{ $planIcons[$pid] }} {{ $plabel }}</option>
              @endforeach
            </select>
          </div>
          <div class="adm-dropzone-wrap" style="flex:1;min-height:130px;">
            <label class="adm-dropzone" id="dropLabel" for="csv_file_input">
              <span class="dz-icon">ðŸ“‚</span>
              <span class="dz-text">Clique para seleccionar ou arraste o ficheiro</span>
              <span class="dz-hint">.csv ou .txt â€” mÃ¡ximo 500 MB</span>
              <span class="dz-chosen" id="chosenFile"></span>
              <input type="file" id="csv_file_input" name="csv_file" accept=".csv,.txt,text/plain" required
                     onchange="
                       var n=this.files[0]?.name;
                       document.getElementById('chosenFile').style.display=n?'block':'none';
                       document.getElementById('chosenFile').textContent='âœ“ '+n;
                       document.getElementById('dropLabel').classList.toggle('has-file',!!n);">
            </label>
          </div>
        </div>
        <div class="adm-form-foot">
          <button type="submit" class="adm-btn adm-btn-primary adm-btn-full">
            â†‘ Carregar Ficheiro
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- â”€â”€ FILTER BAR â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
  <p class="adm-section-title">Pesquisar e filtrar stock</p>
  <form method="get" class="adm-filterbar">
    <div class="adm-fg" style="min-width:150px;">
      <label class="adm-label" for="filter_plan">Plano</label>
      <select id="filter_plan" name="plan_id" class="adm-ctrl" style="min-width:140px;">
        <option value="">Todos os planos</option>
        @foreach($planLabels as $pid => $plabel)
          <option value="{{ $pid }}" @selected(request('plan_id') === $pid)>{{ $plabel }}</option>
        @endforeach
      </select>
    </div>
    <div class="adm-fg" style="min-width:140px;">
      <label class="adm-label" for="f_status">Estado</label>
      <select id="f_status" name="status" class="adm-ctrl" style="min-width:130px;">
        <option value="">Todos</option>
        <option value="available" @selected(request('status') === 'available')>âœ“ DisponÃ­vel</option>
        <option value="used"      @selected(request('status') === 'used')>â— Utilizado</option>
        <option value="reserved"  @selected(request('status') === 'reserved')>â—‡ Reservado</option>
      </select>
    </div>
    <div class="adm-fg grow">
      <label class="adm-label" for="f_q">Pesquisar cÃ³digo</label>
      <input id="f_q" name="q" value="{{ request('q') }}" class="adm-ctrl" placeholder="Ex: ABC123DEF4â€¦">
    </div>
    <div style="display:flex;gap:.5rem;align-items:center;">
      <button type="submit" class="adm-btn adm-btn-primary">Filtrar</button>
      @if(request()->hasAny(['plan_id','status','q']))
        <a href="{{ route('admin.wifi_codes.index') }}" class="adm-btn adm-btn-ghost">âœ• Limpar</a>
      @endif
    </div>
  </form>

  {{-- â”€â”€ TABLE â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
  <div class="adm-tcard">
    <div style="overflow-x:auto;">
      <table class="adm-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Plano</th>
            <th>CÃ³digo</th>
            <th>Estado</th>
            <th>Ordem</th>
            <th>Utilizado em</th>
            <th>Importado em</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($codes as $code)
            @php
              $planBadge = ['diario' => 'badge-blue', 'semanal' => 'badge-purple', 'mensal' => 'badge-gold'];
              $planLabel = ['diario' => 'â˜€ï¸ DiÃ¡rio', 'semanal' => 'ðŸ“… Semanal', 'mensal' => 'ðŸ—“ï¸ Mensal'];
            @endphp
            <tr>
              <td class="col-dim">#{{ $code->id }}</td>
              <td>
                @if($code->plan_id && isset($planBadge[$code->plan_id]))
                  <span class="adm-badge {{ $planBadge[$code->plan_id] }}">{{ $planLabel[$code->plan_id] }}</span>
                @else
                  <span class="adm-badge badge-warn">âš  sem plano</span>
                @endif
              </td>
              <td class="col-mono">{{ $code->code }}</td>
              <td>
                @if($code->status === 'available')
                  <span class="adm-badge badge-green">â— DisponÃ­vel</span>
                @elseif($code->status === 'used')
                  <span class="adm-badge badge-gray">â— Utilizado</span>
                @else
                  <span class="adm-badge badge-amber">â—‡ Reservado</span>
                @endif
              </td>
              <td class="col-link">
                @if($code->autovenda_order_id)
                  <a href="{{ route('admin.autovenda.index', ['q' => $code->autovenda_order_id]) }}">#{{ $code->autovenda_order_id }}</a>
                @else <span class="col-dim">â€”</span>
                @endif
              </td>
              <td class="col-dim">{{ optional($code->used_at)->format('d/m/Y H:i') ?: 'â€”' }}</td>
              <td class="col-dim">{{ optional($code->created_at)->format('d/m/Y H:i') }}</td>
              <td>
                @if($code->status === 'available')
                  <form method="POST" action="{{ route('admin.wifi_codes.destroy', $code) }}"
                        onsubmit="return confirm('Eliminar o cÃ³digo {{ $code->code }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="adm-btn adm-btn-danger-ghost" title="Eliminar">ðŸ—‘</button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8">
                <div class="adm-empty">
                  <div class="adm-empty-icon">ðŸ“­</div>
                  <p><strong>Nenhum cÃ³digo encontrado</strong></p>
                  <p>Importe cÃ³digos acima para comeÃ§ar</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($codes->hasPages())
      <div class="adm-pager">{{ $codes->links() }}</div>
    @endif
  </div>

</div>{{-- /wrap --}}
</div>{{-- /page --}}
</div>{{-- /adm --}}
@endsection
