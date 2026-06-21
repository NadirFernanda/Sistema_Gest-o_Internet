@extends('layouts.app')

@section('content')
<style>
:root{--a-bg:#f4f6f9;--a-surf:#fff;--a-border:#dde2ea;--a-text:#1a202c;--a-muted:#64748b;--a-faint:#9aa5b4;--a-brand:#f7b500;--a-green:#16a34a;--a-amber:#d97706;--a-red:#dc2626;--a-orange:#ea580c;}
.ap{font-family:Inter,system-ui,sans-serif;background:var(--a-bg);min-height:60vh;padding:2rem 0 4rem;color:var(--a-text);}
.ap-wrap{max-width:1200px;margin:0 auto;padding:0 1.5rem;}
.ap-topbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1.75rem;}
.ap-topbar h1{font-size:1.35rem;font-weight:800;margin:0 0 .15rem;letter-spacing:-.02em;}
.ap-topbar .ap-sub{font-size:.78rem;color:var(--a-faint);}
.ap-back{font-size:.82rem;font-weight:600;color:var(--a-muted);text-decoration:none;padding:.4rem .85rem;border:1px solid var(--a-border);border-radius:7px;background:var(--a-surf);transition:background .15s;}
.ap-back:hover{background:var(--a-border);}
.ap-ok{background:#f0fdf4;border:1px solid #86efac;border-left:4px solid var(--a-green);color:#166534;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ap-err{background:#fef2f2;border:1px solid #fecaca;border-left:4px solid var(--a-red);color:#7f1d1d;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ap-totals{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:.75rem;margin-bottom:1.25rem;}
.ap-total{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;padding:1rem 1.1rem;}
.ap-total-val{font-size:1.6rem;font-weight:800;line-height:1;margin:0 0 .2rem;}
.ap-total-lbl{font-size:.73rem;color:var(--a-muted);font-weight:500;text-transform:uppercase;letter-spacing:.04em;}
.ap-total--green{border-top:3px solid var(--a-green);}
.ap-total--brand{border-top:3px solid var(--a-brand);}
.ap-label{display:block;font-size:.77rem;font-weight:600;color:#374151;margin-bottom:.3rem;}
.ap-ctrl{width:100%;box-sizing:border-box;padding:.55rem .75rem;border:1.5px solid var(--a-border);border-radius:8px;font-size:.875rem;color:var(--a-text);background:#f8fafc;font-family:inherit;outline:none;transition:border-color .15s;}
.ap-ctrl:focus{border-color:var(--a-brand);background:#fff;}
.ap-btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.55rem 1.2rem;border-radius:8px;font-size:.875rem;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:filter .15s;text-decoration:none;white-space:nowrap;}
.ap-btn-primary{background:#f7b500;color:#1a202c;}.ap-btn-primary:hover{filter:brightness(.95);}
.ap-btn-outline{background:var(--a-surf);color:var(--a-muted);border:1.5px solid var(--a-border);}.ap-btn-outline:hover{background:var(--a-border);}
.ap-btn-export{background:#1a202c;color:#fff;}.ap-btn-export:hover{filter:brightness(1.15);}
.ap-btn-sm{padding:.35rem .75rem;font-size:.8rem;}
.ap-filters{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;padding:.9rem 1.1rem;margin-bottom:.65rem;}
.ap-filter-row{display:flex;flex-wrap:wrap;gap:.65rem;align-items:flex-end;}
.ap-filter-divider{width:100%;height:0;border-bottom:1px dashed var(--a-border);margin:.35rem 0;}
.ap-period-label{font-size:.72rem;font-weight:700;color:var(--a-muted);text-transform:uppercase;letter-spacing:.06em;width:100%;margin-bottom:.1rem;}
.ap-fg{display:flex;flex-direction:column;gap:.25rem;}
.ap-fg.grow{flex:1;min-width:170px;}
.ap-fg.date{min-width:140px;}
.ap-tcard{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;overflow:hidden;}
.ap-table{width:100%;border-collapse:collapse;font-size:.835rem;}
.ap-table thead{background:#f8fafc;}
.ap-table th{text-align:left;padding:.65rem .9rem;font-size:.69rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--a-faint);border-bottom:1px solid var(--a-border);white-space:nowrap;}
.ap-table td{padding:.6rem .9rem;border-bottom:1px solid #f4f6f9;vertical-align:middle;color:#374151;}
.ap-table tbody tr:last-child td{border-bottom:none;}
.ap-table tbody tr:hover td{background:#fffdf5;}
.ap-table .dim{color:var(--a-faint);font-size:.8rem;}
.ap-ref{font-family:ui-monospace,monospace;font-size:.78rem;color:#374151;background:#f1f5f9;padding:.15rem .4rem;border-radius:4px;letter-spacing:.03em;}
.badge{display:inline-block;padding:.2rem .6rem;border-radius:999px;font-size:.73rem;font-weight:700;white-space:nowrap;}
.bg-amber{background:#fef3c7;color:#b45309;}.bg-orange{background:#ffedd5;color:#9a3412;}
.bg-green{background:#dcfce7;color:#15803d;}.bg-gray{background:#f1f5f9;color:#475569;}
.bg-red{background:#fee2e2;color:#b91c1c;}
.ap-pager{padding:.7rem 1rem;border-top:1px solid var(--a-border);background:#f8fafc;}
.ap-empty{padding:3rem 1rem;text-align:center;color:var(--a-faint);}
.ap-empty-t{font-size:.95rem;font-weight:700;color:var(--a-muted);margin:0 0 .3rem;}
.ap-empty-s{font-size:.82rem;margin:0;}
.ap-note{background:#fffbeb;border:1px solid #fde68a;border-left:4px solid var(--a-brand);color:#78350f;padding:.75rem 1rem;border-radius:8px;font-size:.85rem;margin-bottom:1.25rem;line-height:1.55;}
.ap-recon{background:#eff6ff;border:1px solid #bfdbfe;border-left:4px solid #3b82f6;color:#1e3a8a;padding:.75rem 1rem;border-radius:8px;font-size:.845rem;margin-bottom:1.25rem;line-height:1.55;}
.ap-actions{display:flex;gap:.5rem;align-items:center;justify-content:flex-end;margin-bottom:.75rem;}
</style>

<div class="ap"><div class="ap-wrap">

  <div class="ap-topbar">
    <div>
      <h1>Recargas WiFi</h1>
      <p class="ap-sub">Admin &rsaquo; Compras de planos individuais</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="ap-back">&larr; Dashboard</a>
  </div>

  @if(session('success'))
    <div class="ap-ok">{{ session('success') }}</div>
  @endif

  @if(($paidWithoutCode ?? 0) > 0)
    <div class="ap-err" style="margin-bottom:1rem;">
      <strong>⚠️ {{ $paidWithoutCode }} ordem(ns) PAGA(s) SEM CÓDIGO WiFi</strong> — stock estava esgotado no momento do pagamento. O cliente pagou mas não recebeu código.
      <br><small>Filtre por estado <strong>Pago</strong> e verifique as entradas com campo "Código WiFi" vazio. Entregue manualmente ou reembolse.</small>
    </div>
  @endif

  <div class="ap-recon">
    <strong>Reconcilia&ccedil;&atilde;o com o extracto GPO/EMIS:</strong> Use o filtro de per&iacute;odo abaixo para seleccionar o intervalo de datas do extracto. Depois exporte o CSV &mdash; a coluna <strong>Refer&ecirc;ncia GPO</strong> corresponde ao campo <em>merchantReference</em> no extracto da EMIS.<br>
    Apenas ordens com estado <strong>Pago</strong> geram cobran&ccedil;a no gateway.
  </div>

  {{-- Totais do período filtrado --}}
  <div class="ap-totals">
    <div class="ap-total ap-total--green">
      <div class="ap-total-val" style="color:var(--a-green);">{{ number_format($totalPaid) }}</div>
      <div class="ap-total-lbl">Pagamentos confirmados</div>
    </div>
    <div class="ap-total ap-total--brand">
      <div class="ap-total-val" style="color:var(--a-amber);">{{ number_format($totalAoa, 0, ',', '.') }}</div>
      <div class="ap-total-lbl">Total recebido (AOA)</div>
    </div>
  </div>

  <form method="get" class="ap-filters">
    <div class="ap-filter-row">
      <div class="ap-fg">
        <label class="ap-label">Tipo de plano</label>
        <select name="plan_id" class="ap-ctrl" style="min-width:160px;">
          <option value="">Todos os planos</option>
          @foreach($plans as $p)
            <option value="{{ $p->slug }}" @selected(request('plan_id') === $p->slug)>{{ $p->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="ap-fg">
        <label class="ap-label">Estado</label>
        <select name="status" class="ap-ctrl" style="min-width:160px;">
          <option value="">Todos os estados</option>
          @foreach([\App\Models\AutovendaOrder::STATUS_PENDING => 'Pendente', \App\Models\AutovendaOrder::STATUS_AWAITING_PAYMENT => 'A aguardar pagamento', \App\Models\AutovendaOrder::STATUS_PAID => 'Pago', \App\Models\AutovendaOrder::STATUS_CANCELLED => 'Cancelado', \App\Models\AutovendaOrder::STATUS_FAILED => 'Falhou', \App\Models\AutovendaOrder::STATUS_EXPIRED => 'Expirado'] as $value => $label)
            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="ap-fg grow">
        <label class="ap-label">Pesquisa</label>
        <input name="q" value="{{ request('q') }}" class="ap-ctrl" placeholder="ID, nome, e-mail, telefone, refer&ecirc;ncia GPO, c&oacute;digo WiFi&hellip;">
      </div>
    </div>

    <div class="ap-filter-divider"></div>

    <div class="ap-filter-row">
      <span class="ap-period-label">Filtro por per&iacute;odo (data de pagamento):</span>
      <div class="ap-fg date">
        <label class="ap-label">De</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="ap-ctrl">
      </div>
      <div class="ap-fg date">
        <label class="ap-label">At&eacute;</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="ap-ctrl">
      </div>
      <button type="submit" class="ap-btn ap-btn-primary">Filtrar</button>
      @if(request()->hasAny(['status','payment_method','q','date_from','date_to']))
        <a href="{{ route('admin.autovenda.index') }}" class="ap-btn ap-btn-outline ap-btn-sm">Limpar</a>
      @endif
    </div>
  </form>

  {{-- Botão exportar CSV (mantém os filtros activos) --}}
  <div class="ap-actions">
    <a href="{{ route('admin.autovenda.export', request()->only(['q','status','date_from','date_to'])) }}"
       class="ap-btn ap-btn-export ap-btn-sm">
      &#8595; Exportar CSV (Pago)
    </a>
  </div>

  <div class="ap-tcard">
    <table class="ap-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Plano</th>
          <th>Valor</th>
          <th>Estado</th>
          <th>Refer&ecirc;ncia GPO</th>
          <th>Data Pagamento</th>
          <th>Cliente</th>
          <th>C&oacute;digo WiFi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $order)
          <tr>
            <td style="font-weight:700;color:var(--a-amber);">#{{ $order->id }}</td>
            <td style="font-weight:600;">{{ $order->plan_name ?? $order->plan_id }}</td>
            <td style="font-weight:700;">{{ number_format($order->amount_aoa, 0, ',', '.') }} <span class="dim">AOA</span></td>
            <td>
              @if($order->status === 'paid')
                <span class="badge bg-green">Pago</span>
              @elseif($order->status === 'awaiting_payment')
                <span class="badge bg-orange">Aguarda</span>
              @elseif($order->status === 'pending')
                <span class="badge bg-gray">Pendente</span>
              @elseif($order->status === 'cancelled')
                <span class="badge bg-gray">Cancelado</span>
              @elseif($order->status === 'failed')
                <span class="badge bg-red">Falhou</span>
              @elseif($order->status === 'expired')
                <span class="badge bg-gray">Expirado</span>
              @else
                <span class="badge bg-gray">{{ $order->status }}</span>
              @endif
            </td>
            <td>
              @if($order->payment_reference)
                <span class="ap-ref">{{ $order->payment_reference }}</span>
              @else
                <span class="dim">&mdash;</span>
              @endif
            </td>
            <td class="dim">
              @if($order->paid_at)
                {{ $order->paid_at->format('d/m/Y') }}<br>
                <span style="font-size:.75rem;">{{ $order->paid_at->format('H:i') }}</span>
              @else
                &mdash;
              @endif
            </td>
            <td>
              @if($order->customer_name)
                <span style="font-weight:600;">{{ $order->customer_name }}</span><br>
              @endif
              @if($order->customer_email)
                <span class="dim">{{ $order->customer_email }}</span><br>
              @endif
              @if($order->customer_phone)
                <span class="dim">{{ $order->customer_phone }}</span>
              @endif
              @if(!$order->customer_name && !$order->customer_email && !$order->customer_phone)
                <span class="dim">Sem dados</span>
              @endif
            </td>
            <td>
              @if($order->wifi_code)
                <span class="ap-ref">{{ $order->wifi_code }}</span>
              @else
                <span class="dim">&mdash;</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8">
              <div class="ap-empty">
                <p class="ap-empty-t">Nenhuma recarga encontrada</p>
                <p class="ap-empty-s">Ajuste os filtros ou aguarde novas compras.</p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
    <div class="ap-pager">{{ $orders->links() }}</div>
  </div>

  <div class="ap-note" style="margin-top:1.25rem;">
    <strong>O que &eacute; esta p&aacute;gina?</strong> Registo de todas as compras de planos individuais de Wi-Fi feitas pelos clientes via autovenda.<br><br>
    <strong>Reconcilia&ccedil;&atilde;o:</strong> No extracto GPO/EMIS cada linha tem um <em>merchantReference</em> &mdash; esse valor corresponde exactamente &agrave; coluna <strong>Refer&ecirc;ncia GPO</strong> aqui. Filtre o per&iacute;odo, exporte o CSV e cruze pelo n&uacute;mero de refer&ecirc;ncia.<br><br>
    <strong>Processo autom&aacute;tico:</strong> O cliente escolhe o plano, paga e o sistema entrega o c&oacute;digo Wi-Fi sem interven&ccedil;&atilde;o do admin. Use esta lista apenas para auditoria ou resolu&ccedil;&atilde;o de problemas.
  </div>

</div></div>
@endsection
