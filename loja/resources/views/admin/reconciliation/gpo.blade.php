@extends('layouts.app')

@section('content')
<style>
:root{--a-bg:#f4f6f9;--a-surf:#fff;--a-border:#dde2ea;--a-text:#1a202c;--a-muted:#64748b;--a-faint:#9aa5b4;--a-brand:#f7b500;--a-green:#16a34a;--a-amber:#d97706;--a-red:#dc2626;--a-blue:#3b82f6;}
.rg{font-family:Inter,system-ui,sans-serif;background:var(--a-bg);min-height:60vh;padding:2rem 0 4rem;color:var(--a-text);}
.rg-wrap{max-width:1200px;margin:0 auto;padding:0 1.5rem;}
.rg-topbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;}
.rg-topbar h1{font-size:1.35rem;font-weight:800;margin:0 0 .15rem;letter-spacing:-.02em;}
.rg-sub{font-size:.78rem;color:var(--a-faint);}
.rg-back{font-size:.82rem;font-weight:600;color:var(--a-muted);text-decoration:none;padding:.4rem .85rem;border:1px solid var(--a-border);border-radius:7px;background:var(--a-surf);}
.rg-back:hover{background:var(--a-border);}
.rg-info{background:#eff6ff;border:1px solid #bfdbfe;border-left:4px solid var(--a-blue);color:#1e3a8a;padding:.8rem 1rem;border-radius:8px;font-size:.845rem;margin-bottom:1.25rem;line-height:1.6;}
.rg-totals{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:.75rem;margin-bottom:1.25rem;}
.rg-total{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;padding:1rem 1.1rem;}
.rg-total-val{font-size:1.6rem;font-weight:800;line-height:1;margin:0 0 .25rem;}
.rg-total-lbl{font-size:.72rem;color:var(--a-muted);font-weight:500;text-transform:uppercase;letter-spacing:.04em;}
.rg-total--green{border-top:3px solid var(--a-green);}
.rg-total--brand{border-top:3px solid var(--a-brand);}
.rg-label{display:block;font-size:.77rem;font-weight:600;color:#374151;margin-bottom:.3rem;}
.rg-ctrl{width:100%;box-sizing:border-box;padding:.55rem .75rem;border:1.5px solid var(--a-border);border-radius:8px;font-size:.875rem;color:var(--a-text);background:#f8fafc;font-family:inherit;outline:none;transition:border-color .15s;}
.rg-ctrl:focus{border-color:var(--a-brand);background:#fff;}
.rg-btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.55rem 1.2rem;border-radius:8px;font-size:.875rem;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:filter .15s;text-decoration:none;white-space:nowrap;}
.rg-btn-primary{background:#f7b500;color:#1a202c;}.rg-btn-primary:hover{filter:brightness(.95);}
.rg-btn-outline{background:var(--a-surf);color:var(--a-muted);border:1.5px solid var(--a-border);}.rg-btn-outline:hover{background:var(--a-border);}
.rg-btn-export{background:#1a202c;color:#fff;}.rg-btn-export:hover{filter:brightness(1.2);}
.rg-btn-sm{padding:.35rem .75rem;font-size:.8rem;}
.rg-filters{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;padding:.9rem 1.1rem;margin-bottom:.65rem;}
.rg-filter-row{display:flex;flex-wrap:wrap;gap:.65rem;align-items:flex-end;}
.rg-filter-div{width:100%;height:0;border-bottom:1px dashed var(--a-border);margin:.35rem 0;}
.rg-period-lbl{font-size:.72rem;font-weight:700;color:var(--a-muted);text-transform:uppercase;letter-spacing:.06em;width:100%;margin-bottom:.1rem;}
.rg-fg{display:flex;flex-direction:column;gap:.25rem;}
.rg-fg.grow{flex:1;min-width:170px;}
.rg-fg.date{min-width:140px;}
.rg-actions{display:flex;gap:.5rem;align-items:center;justify-content:flex-end;margin-bottom:.75rem;}
.rg-card{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;overflow:hidden;}
.rg-table{width:100%;border-collapse:collapse;font-size:.83rem;}
.rg-table thead{background:#f8fafc;}
.rg-table th{text-align:left;padding:.65rem .9rem;font-size:.69rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--a-faint);border-bottom:1px solid var(--a-border);white-space:nowrap;}
.rg-table td{padding:.6rem .9rem;border-bottom:1px solid #f4f6f9;vertical-align:middle;color:#374151;}
.rg-table tbody tr:last-child td{border-bottom:none;}
.rg-table tbody tr:hover td{background:#fffdf5;}
.dim{color:var(--a-faint);font-size:.8rem;}
.rg-ref{font-family:ui-monospace,monospace;font-size:.78rem;color:#374151;background:#f1f5f9;padding:.15rem .4rem;border-radius:4px;letter-spacing:.03em;}
.badge{display:inline-block;padding:.2rem .6rem;border-radius:999px;font-size:.73rem;font-weight:700;white-space:nowrap;}
.bg-individual{background:#fef3c7;color:#92400e;}
.bg-family{background:#dbeafe;color:#1e40af;}
.bg-reseller{background:#d1fae5;color:#065f46;}
.bg-paid{background:#dcfce7;color:#15803d;}
.bg-pending{background:#fef3c7;color:#b45309;}
.bg-gray{background:#f1f5f9;color:#475569;}
.rg-pager{padding:.7rem 1rem;border-top:1px solid var(--a-border);background:#f8fafc;}
.rg-empty{padding:3rem 1rem;text-align:center;color:var(--a-faint);}
.rg-empty-t{font-size:.95rem;font-weight:700;color:var(--a-muted);margin:0 0 .3rem;}
.rg-empty-s{font-size:.82rem;margin:0;}
</style>

<div class="rg"><div class="rg-wrap">

  <div class="rg-topbar">
    <div>
      <h1>Reconcilia&ccedil;&atilde;o GPO / EMIS</h1>
      <p class="rg-sub">Admin &rsaquo; Todos os pagamentos via gateway EMIS GPO</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="rg-back">&larr; Dashboard</a>
  </div>

  <div class="rg-info">
    <strong>Como reconciliar com o extracto EMIS:</strong>
    Esta p&aacute;gina agrega <em>todos</em> os pagamentos processados pelo gateway GPO da EMIS &mdash; planos individuais, planos familiares/empresariais e compras de revendedores.<br>
    A coluna <strong>Refer&ecirc;ncia GPO</strong> corresponde ao campo <em>merchantReference</em> no extracto da EMIS.
    Filtre o per&iacute;odo, exporte o CSV e cruze linha a linha pelo n&uacute;mero de refer&ecirc;ncia.
    <br><strong>Aten&ccedil;&atilde;o:</strong> Para planos familiares/empresariais a data mostrada &eacute; a data de confirma&ccedil;&atilde;o do pedido (n&atilde;o existe campo <em>paid_at</em> nessa tabela).
  </div>

  {{-- Totais --}}
  <div class="rg-totals">
    <div class="rg-total rg-total--green">
      <div class="rg-total-val" style="color:var(--a-green);">{{ number_format($total) }}</div>
      <div class="rg-total-lbl">Pagamentos</div>
    </div>
    <div class="rg-total rg-total--brand">
      <div class="rg-total-val" style="color:var(--a-amber);">{{ number_format($totalAoa, 0, ',', '.') }}</div>
      <div class="rg-total-lbl">Total recebido (AOA)</div>
    </div>
  </div>

  {{-- Filtros --}}
  <form method="get" class="rg-filters">
    <div class="rg-filter-row">
      <div class="rg-fg">
        <label class="rg-label">Tipo</label>
        <select name="type" class="rg-ctrl" style="min-width:210px;">
          <option value="">Todos os tipos</option>
          <option value="individual" @selected(request('type')==='individual')>Planos Individuais</option>
          <option value="family"     @selected(request('type')==='family')>Planos Familiares/Empresariais</option>
          <option value="reseller"   @selected(request('type')==='reseller')>Compras de Revendedores</option>
        </select>
      </div>
      <div class="rg-fg grow">
        <label class="rg-label">Pesquisa</label>
        <input name="q" value="{{ request('q') }}" class="rg-ctrl" placeholder="Refer&ecirc;ncia GPO, nome, e-mail, telefone&hellip;">
      </div>
    </div>
    <div class="rg-filter-div"></div>
    <div class="rg-filter-row">
      <span class="rg-period-lbl">Per&iacute;odo (data de pagamento):</span>
      <div class="rg-fg date">
        <label class="rg-label">De</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="rg-ctrl">
      </div>
      <div class="rg-fg date">
        <label class="rg-label">At&eacute;</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="rg-ctrl">
      </div>
      <button type="submit" class="rg-btn rg-btn-primary">Filtrar</button>
      @if(request()->hasAny(['type','q','date_from','date_to']))
        <a href="{{ route('admin.reconciliation.gpo') }}" class="rg-btn rg-btn-outline rg-btn-sm">Limpar</a>
      @endif
    </div>
  </form>

  <div class="rg-actions">
    <a href="{{ route('admin.reconciliation.gpo.export', request()->only(['type','q','date_from','date_to'])) }}"
       class="rg-btn rg-btn-export rg-btn-sm">
      &#8595; Exportar CSV
    </a>
  </div>

  <div class="rg-card">
    <table class="rg-table">
      <thead>
        <tr>
          <th>Refer&ecirc;ncia GPO</th>
          <th>Tipo</th>
          <th>Descri&ccedil;&atilde;o</th>
          <th>Valor (AOA)</th>
          <th>Cliente</th>
          <th>Data</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $row)
          <tr>
            <td>
              @if($row['ref'] !== '—')
                <span class="rg-ref">{{ $row['ref'] }}</span>
              @else
                <span class="dim">&mdash;</span>
              @endif
            </td>
            <td>
              @if($row['type'] === 'individual')
                <span class="badge bg-individual">Individual</span>
              @elseif($row['type'] === 'family')
                <span class="badge bg-family">Familiar/Empresarial</span>
              @elseif($row['type'] === 'reseller')
                <span class="badge bg-reseller">Revendedor</span>
              @endif
            </td>
            <td style="max-width:220px;">{{ $row['description'] }}</td>
            <td style="font-weight:700;">{{ number_format($row['amount'], 0, ',', '.') }} <span class="dim">AOA</span></td>
            <td class="dim" style="max-width:160px;">{{ $row['customer'] ?: '—' }}</td>
            <td class="dim">
              @if($row['date'])
                {{ $row['date']->format('d/m/Y') }}<br>
                <span style="font-size:.74rem;">{{ $row['date']->format('H:i') }}</span>
              @else
                &mdash;
              @endif
            </td>
            <td>
              @if(in_array($row['status'], ['Pago', 'Activado']))
                <span class="badge bg-paid">{{ $row['status'] }}</span>
              @elseif(str_contains($row['status'], 'Aguarda') || $row['status'] === 'Confirmado')
                <span class="badge bg-pending">{{ $row['status'] }}</span>
              @else
                <span class="badge bg-gray">{{ $row['status'] }}</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7">
              <div class="rg-empty">
                <p class="rg-empty-t">Nenhum pagamento encontrado</p>
                <p class="rg-empty-s">Ajuste os filtros de data ou tipo.</p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
    <div class="rg-pager">{{ $rows->links() }}</div>
  </div>

</div></div>
@endsection
