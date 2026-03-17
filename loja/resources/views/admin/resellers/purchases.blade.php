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
.ap-stats{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.75rem;margin-bottom:1.5rem;}
.ap-stat{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;padding:1rem 1.25rem;}
.ap-stat-val{font-size:1.6rem;font-weight:800;line-height:1;margin:0 0 .2rem;color:var(--a-brand);}
.ap-stat-lbl{font-size:.75rem;color:var(--a-muted);font-weight:500;}
.ap-label{display:block;font-size:.77rem;font-weight:600;color:#374151;margin-bottom:.3rem;}
.ap-ctrl{width:100%;box-sizing:border-box;padding:.55rem .75rem;border:1.5px solid var(--a-border);border-radius:8px;font-size:.875rem;color:var(--a-text);background:#f8fafc;font-family:inherit;outline:none;transition:border-color .15s;}
.ap-ctrl:focus{border-color:var(--a-brand);background:#fff;}
.ap-btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.55rem 1.2rem;border-radius:8px;font-size:.875rem;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:filter .15s;text-decoration:none;white-space:nowrap;}
.ap-btn-primary{background:#f7b500;color:#1a202c;}.ap-btn-primary:hover{filter:brightness(.95);}
.ap-btn-outline{background:var(--a-surf);color:var(--a-muted);border:1.5px solid var(--a-border);}.ap-btn-outline:hover{background:var(--a-border);}
.ap-btn-sm{padding:.35rem .75rem;font-size:.8rem;}
.ap-filters{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;padding:.9rem 1.1rem;display:flex;flex-wrap:wrap;gap:.65rem;align-items:flex-end;margin-bottom:1.25rem;}
.ap-fg{display:flex;flex-direction:column;gap:.25rem;}
.ap-fg.grow{flex:1;min-width:170px;}
.ap-tcard{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;overflow:hidden;}
.ap-table{width:100%;border-collapse:collapse;font-size:.845rem;}
.ap-table thead{background:#f8fafc;}
.ap-table th{text-align:left;padding:.65rem 1rem;font-size:.69rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--a-faint);border-bottom:1px solid var(--a-border);white-space:nowrap;}
.ap-table td{padding:.6rem 1rem;border-bottom:1px solid #f4f6f9;vertical-align:middle;color:#374151;}
.ap-table tbody tr:last-child td{border-bottom:none;}
.ap-table tbody tr:hover td{background:#fafbff;}
.ap-table .dim{color:var(--a-faint);font-size:.82rem;}
.ap-pager{padding:.7rem 1rem;border-top:1px solid var(--a-border);background:#f8fafc;}
.ap-empty{padding:3rem 1rem;text-align:center;color:var(--a-faint);}
.ap-empty-t{font-size:.95rem;font-weight:700;color:var(--a-muted);margin:0 0 .3rem;}
.ap-empty-s{font-size:.82rem;margin:0;}
.ap-rank-bar{background:#e5e7eb;border-radius:9999px;height:7px;overflow:hidden;margin-top:.3rem;min-width:80px;}
.ap-rank-fill{height:7px;border-radius:9999px;background:var(--a-brand);}
</style>

<div class="ap"><div class="ap-wrap">

  <div class="ap-topbar">
    <div>
      <h1>Compras de Revendedores</h1>
      <p class="ap-sub">Admin &rsaquo; Hist&oacute;rico de compras em bloco</p>
    </div>
    <a href="{{ route('admin.resellers.index') }}" class="ap-back">&larr; Candidaturas</a>
  </div>

  <div class="ap-stats">
    <div class="ap-stat">
      <p class="ap-stat-val">{{ number_format($totalRevenue, 0, ',', '.') }} AOA</p>
      <p class="ap-stat-lbl">Receita total (l&iacute;quida)</p>
    </div>
    <div class="ap-stat">
      <p class="ap-stat-val">{{ number_format($totalCodes, 0, ',', '.') }}</p>
      <p class="ap-stat-lbl">C&oacute;digos vendidos</p>
    </div>
    <div class="ap-stat">
      <p class="ap-stat-val">{{ $totalResellers }}</p>
      <p class="ap-stat-lbl">Revendedores activos</p>
    </div>
  </div>

  {{-- ── Ranking por volume de vendas ──────────────────── --}}
  @if($ranking->isNotEmpty())
  <div class="ap-tcard" style="margin-bottom:1.25rem;">
    <div style="padding:.85rem 1rem .6rem;border-bottom:1px solid var(--a-border);display:flex;justify-content:space-between;align-items:center;">
      <span style="font-size:.9rem;font-weight:700;">Ranking por Volume de Vendas</span>
      <span class="dim" style="font-size:.78rem;">ordenado por receita l&iacute;quida total</span>
    </div>
    <table class="ap-table">
      <thead>
        <tr>
          <th style="width:40px;">#</th>
          <th>Revendedor</th>
          <th>Compras</th>
          <th>C&oacute;digos</th>
          <th>Receita l&iacute;quida</th>
          <th style="min-width:120px;">Quota</th>
        </tr>
      </thead>
      <tbody>
        @foreach($ranking as $i => $row)
          @php
            $medals  = ['🥇','🥈','🥉'];
            $medal   = $medals[$i] ?? '';
            $pct     = $totalRevenue > 0 ? round(($row->total_net / $totalRevenue) * 100, 1) : 0;
          @endphp
          <tr>
            <td style="font-weight:700;font-size:.95rem;text-align:center;">{{ $medal ?: ($i + 1) }}</td>
            <td>
              @if($row->application)
                <a href="{{ route('admin.resellers.show', $row->application) }}" style="color:var(--a-text);font-weight:600;text-decoration:none;">
                  {{ $row->application->full_name }}
                </a>
                <br><span class="dim">{{ $row->application->phone }}</span>
              @else
                <span class="dim">ID {{ $row->reseller_application_id }}</span>
              @endif
            </td>
            <td style="font-weight:600;">{{ $row->purchases_count }}</td>
            <td style="font-weight:600;">{{ number_format($row->total_codes, 0, ',', '.') }}</td>
            <td style="font-weight:700;">{{ number_format($row->total_net, 0, ',', '.') }} AOA</td>
            <td>
              <span style="font-size:.8rem;font-weight:600;">{{ $pct }}%</span>
              <div class="ap-rank-bar"><div class="ap-rank-fill" style="width:{{ $pct }}%;"></div></div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif

  <div class="ap-note">
    <strong>O que é esta página?</strong> Registo consolidado de todas as compras em bloco feitas por todos os revendedores aprovados.<br><br>
    <strong>O que é uma compra em bloco?</strong> Um revendedor aprovado compra um lote de códigos Wi-Fi a preço de revendedor (com desconto sobre o preço de tabela). Os códigos são atribuídos e entregues automaticamente após confirmação do pagamento.<br><br>
    <strong>Colunas importantes:</strong><br>
    &bull; <strong>Valor bruto</strong>: total antes do desconto &nbsp;&bull;&nbsp; <strong>Desconto</strong>: redução aplicada ao revendedor &nbsp;&bull;&nbsp; <strong>Valor líquido</strong>: receita real da AngolaWiFi &nbsp;&bull;&nbsp; <strong>Códigos</strong>: número de vouchers entregues nesta compra.<br><br>
    <strong>Para ver o detalhe de um revendedor específico</strong> (historial individual, dados da candidatura, estado), clique no nome dele para ir à sua ficha.
  </div>

  <form method="get" class="ap-filters">
    <div class="ap-fg grow">
      <label class="ap-label">Pesquisa</label>
      <input name="q" value="{{ request('q') }}" class="ap-ctrl" placeholder="Nome, telefone ou e-mail do revendedor">
    </div>
    <div class="ap-fg">
      <label class="ap-label">De</label>
      <input type="date" name="date_from" value="{{ request('date_from') }}" class="ap-ctrl" style="min-width:140px;">
    </div>
    <div class="ap-fg">
      <label class="ap-label">At&eacute;</label>
      <input type="date" name="date_to" value="{{ request('date_to') }}" class="ap-ctrl" style="min-width:140px;">
    </div>
    <button type="submit" class="ap-btn ap-btn-primary">Filtrar</button>
    @if(request()->hasAny(['q','date_from','date_to','reseller_id']))
      <a href="{{ route('admin.resellers.purchases.index') }}" class="ap-btn ap-btn-outline ap-btn-sm">Limpar</a>
    @endif
  </form>

  <div class="ap-tcard">
    <table class="ap-table">
      <thead>
        <tr>
          <th>Nome do Revendedor</th>
          <th>N.&ordm; Telem&oacute;vel</th>
          <th>Valor bruto</th>
          <th>Desconto</th>
          <th>Valor l&iacute;quido</th>
          <th>Data da &uacute;ltima compra</th>
        </tr>
      </thead>
      <tbody>
        @forelse($purchases as $purchase)
          <tr>
            <td>
              @if($purchase->application)
                <a href="{{ route('admin.resellers.show', $purchase->application) }}" style="color:var(--a-text);font-weight:600;text-decoration:none;">
                  {{ $purchase->application->full_name }}
                </a>
              @else
                <span class="dim">ID {{ $purchase->reseller_application_id }}</span>
              @endif
            </td>
            <td>{{ $purchase->application->phone ?? '—' }}</td>
            <td class="dim">{{ number_format($purchase->gross_amount_aoa, 0, ',', '.') }} AOA</td>
            <td style="color:var(--a-green);font-weight:600;">{{ $purchase->discount_percent }}%</td>
            <td style="font-weight:700;">{{ number_format($purchase->net_amount_aoa, 0, ',', '.') }} AOA</td>
            <td class="dim">{{ optional($purchase->created_at)->format('d/m/Y H:i') }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="6">
              <div class="ap-empty">
                <p class="ap-empty-t">Nenhuma compra registada</p>
                <p class="ap-empty-s">Ainda n&atilde;o existem compras em bloco de revendedores.</p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
    <div class="ap-pager">{{ $purchases->links() }}</div>
  </div>

</div></div>
@endsection
