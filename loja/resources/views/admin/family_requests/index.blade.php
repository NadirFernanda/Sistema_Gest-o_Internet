@extends('layouts.app')

@section('title', 'Planos Familiares, Empresariais e Institucionais &mdash; Admin')

@section('content')
<style>
:root{--a-bg:#f4f6f9;--a-surf:#fff;--a-border:#dde2ea;--a-text:#1a202c;--a-muted:#64748b;--a-faint:#9aa5b4;--a-brand:#f7b500;--a-green:#16a34a;--a-amber:#d97706;--a-red:#dc2626;}
.ap-ref{font-family:'Courier New',monospace;font-size:.8rem;background:#f0f9ff;color:#0369a1;padding:.1rem .4rem;border-radius:4px;white-space:nowrap;}
.bg-sky{background:#e0f2fe;color:#0369a1;}.bg-orange{background:#ffedd5;color:#c2410c;}
.ap{font-family:Inter,system-ui,sans-serif;background:var(--a-bg);min-height:60vh;padding:2rem 0 4rem;color:var(--a-text);}
.ap-wrap{max-width:1200px;margin:0 auto;padding:0 1.5rem;}
.ap-topbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;}
.ap-topbar h1{font-size:1.35rem;font-weight:800;margin:0 0 .15rem;letter-spacing:-.02em;}
.ap-topbar .ap-sub{font-size:.78rem;color:var(--a-faint);}
.ap-back{font-size:.82rem;font-weight:600;color:var(--a-muted);text-decoration:none;padding:.4rem .85rem;border:1px solid var(--a-border);border-radius:7px;background:var(--a-surf);transition:background .15s;}
.ap-back:hover{background:var(--a-border);}
.ap-ok{background:#f0fdf4;border:1px solid #86efac;border-left:4px solid var(--a-green);color:#166534;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ap-err{background:#fef2f2;border:1px solid #fecaca;border-left:4px solid var(--a-red);color:#7f1d1d;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ap-note{background:#fffbeb;border:1px solid #fde68a;border-left:4px solid var(--a-brand);color:#78350f;padding:.75rem 1rem;border-radius:8px;font-size:.85rem;margin-bottom:1.25rem;line-height:1.55;}
.ap-stats{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:.65rem;margin-bottom:1.5rem;}
.ap-stat{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;padding:.85rem 1rem;text-decoration:none;transition:border-color .15s;}
.ap-stat:hover{border-color:var(--a-brand);}
.ap-stat-val{font-size:1.6rem;font-weight:800;line-height:1;margin:0 0 .2rem;}
.ap-stat-lbl{font-size:.75rem;color:var(--a-muted);font-weight:500;}
.ap-stat.active{border-color:var(--a-brand);}
.ap-btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.45rem 1rem;border-radius:8px;font-size:.82rem;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:filter .15s;text-decoration:none;white-space:nowrap;}
.ap-btn-primary{background:#f7b500;color:#1a202c;}.ap-btn-primary:hover{filter:brightness(.95);}
.ap-btn-danger{background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;}.ap-btn-danger:hover{background:#fecaca;}
.ap-btn-outline{background:var(--a-surf);color:var(--a-muted);border:1.5px solid var(--a-border);}.ap-btn-outline:hover{background:var(--a-border);}
.ap-btn-sm{padding:.32rem .75rem;font-size:.78rem;}
.ap-label{display:block;font-size:.77rem;font-weight:600;color:#374151;margin-bottom:.3rem;}
.ap-ctrl{width:100%;box-sizing:border-box;padding:.55rem .75rem;border:1.5px solid var(--a-border);border-radius:8px;font-size:.875rem;color:var(--a-text);background:#f8fafc;font-family:inherit;outline:none;transition:border-color .15s;}
.ap-ctrl:focus{border-color:var(--a-brand);background:#fff;}
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
      <h1>Planos Familiares, Empresariais e Institucionais</h1>
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
    <strong>O que é esta página?</strong> Lista dos pedidos de planos Familiares, Empresariais e Institucionais (planos mensais de 30 dias). São diferentes das recargas individuais (Diário/Semanal/Mensal) &mdash; estes são activados directamente no Sistema de Gestão (SG).<br><br>
    <strong>Fluxo completo de um pedido:</strong><br>
    1. O cliente escolhe o plano e preenche os dados (nome, telefone, NIF) &rarr; pedido criado com estado <strong>A aguardar pagamento</strong>.<br>
    2. O cliente paga (Multicaixa Express ou PayPal) &rarr; o gateway confirma automaticamente &rarr; o sistema activa o plano no SG &rarr; estado muda para <strong>Activado</strong>. <em>Nenhuma intervenção do admin é necessária neste caso.</em><br>
    3. Se o pagamento foi confirmado mas o SG devolveu erro (ex: SG em manutenção, timeout), o estado fica <strong>Pendente</strong>. Clique em <strong>"Activar no SG"</strong> para retentar a activação manualmente.<br><br>
    <strong>Estados e o que fazer:</strong><br>
    &bull; <strong>A aguardar pagamento</strong>: cliente ainda não pagou. Não precisa de fazer nada &mdash; aguarde. Se suspeitar de spam ou engano, use o botão <strong>Cancelar</strong>.<br>
    &bull; <strong>Pendente ⚠</strong>: pagamento confirmado mas activação no SG falhou. <em>Precisa de intervenção.</em> Clique em <strong>"Activar no SG"</strong> para resolver.<br>
    &bull; <strong>Confirmado</strong>: a ser processado (estado transitório breve).<br>
    &bull; <strong>Activado</strong>: tudo concluído, cliente com acesso activo no SG. Nenhuma acção necessária.<br>
    &bull; <strong>Cancelado</strong>: pedido cancelado. Se o cliente já tinha pago, o reembolso tem de ser feito manualmente fora do sistema.<br><br>
    <strong>Botão "Activar no SG"</strong>: aparece nos estados Pendente e Confirmado. Envia os dados do cliente para o SG e cria a janela de acesso.<br>
    <strong>Botão "Cancelar"</strong>: disponível em Aguarda pagamento, Pendente e Confirmado. Cancela o pedido no sistema mas <em>não</em> faz reembolso automaticamente.
  </div>

  {{-- Contadores por estado --}}
  @php
    $filterLabels = [
      'awaiting_payment' => ['label' => 'Aguarda pag.',  'val' => $counts['awaiting_payment'] ?? 0, 'color' => 'color:var(--a-amber)'],
      'pending'          => ['label' => 'Pendentes ⚠',   'val' => $counts['pending']          ?? 0, 'color' => 'color:var(--a-red)'],
      'confirmed'        => ['label' => 'Confirmados',   'val' => $counts['confirmed']        ?? 0, 'color' => 'color:#1d4ed8'],
      'activated'        => ['label' => 'Activados',     'val' => $counts['activated']        ?? 0, 'color' => 'color:var(--a-green)'],
      'cancelled'        => ['label' => 'Cancelados',    'val' => $counts['cancelled']        ?? 0, 'color' => ''],
    ];
    $total = array_sum(array_column($filterLabels, 'val'));
  @endphp
  <div class="ap-stats">
    @foreach($filterLabels as $key => $meta)
      <a href="{{ route('admin.family_requests.index', ['status' => $key]) }}"
         class="ap-stat {{ $status === $key ? 'active' : '' }}">
        <p class="ap-stat-val" style="{{ $meta['color'] }}">{{ $meta['val'] }}</p>
        <p class="ap-stat-lbl">{{ $meta['label'] }}</p>
      </a>
    @endforeach
    <a href="{{ route('admin.family_requests.index') }}"
       class="ap-stat {{ $status === 'all' ? 'active' : '' }}">
      <p class="ap-stat-val">{{ $total }}</p>
      <p class="ap-stat-lbl">Todos</p>
    </a>
  </div>

  {{-- Filtros --}}
  <form method="get" class="ap-filters">
    @if($status !== 'all')
      <input type="hidden" name="status" value="{{ $status }}">
    @endif
    <div class="ap-fg grow">
      <label class="ap-label">Pesquisa</label>
      <input name="q" value="{{ request('q') }}" class="ap-ctrl" placeholder="Nome, telefone, e-mail, plano, refer&ecirc;ncia...">
    </div>
    <div class="ap-fg">
      <label class="ap-label">M&eacute;todo</label>
      <select name="payment_method" class="ap-ctrl" style="min-width:175px;">
        <option value="">Todos os m&eacute;todos</option>
        <option value="multicaixa_express" @selected(request('payment_method') === 'multicaixa_express')>Multicaixa Express</option>
        <option value="paypal"             @selected(request('payment_method') === 'paypal')>PayPal</option>
      </select>
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
    @if(request()->hasAny(['q','payment_method','date_from','date_to']) || $status !== 'all')
      <a href="{{ route('admin.family_requests.index') }}" class="ap-btn ap-btn-outline ap-btn-sm">Limpar</a>
    @endif
  </form>

  <div class="ap-tcard">
    <table class="ap-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Plano</th>
          <th>Refer&ecirc;ncia</th>
          <th>Cliente</th>
          <th>Contacto</th>
          <th>M&eacute;todo</th>
          <th>Estado</th>
          <th>Data</th>
          <th>Ac&ccedil;&otilde;es</th>
        </tr>
      </thead>
      <tbody>
        @forelse($requests as $req)
          @php
            $planLower = mb_strtolower($req->plan_name);
            if (str_contains($planLower, 'institucional')) {
                $typeBadge = '<span class="badge bg-sky">Institucional</span>';
            } elseif (str_contains($planLower, 'empresarial')) {
                $typeBadge = '<span class="badge bg-orange">Empresarial</span>';
            } else {
                $typeBadge = '<span class="badge bg-blue">Familiar</span>';
            }
          @endphp
          <tr>
            <td class="dim">{{ $req->id }}</td>
            <td>
              {!! $typeBadge !!}
              <br><span style="font-weight:600;font-size:.85rem;">{{ $req->plan_name }}</span>
              @if($req->plan_preco)
                <br><span class="dim">{{ number_format($req->plan_preco, 0, ',', '.') }} AOA/m&ecirc;s</span>
              @endif
            </td>
            <td>
              @if($req->payment_reference)
                <span class="ap-ref">{{ $req->payment_reference }}</span>
              @else
                <span class="dim">&mdash;</span>
              @endif
            </td>
            <td>
              <span style="font-weight:600;">{{ $req->customer_name }}</span>
              @if($req->customer_email)
                <br><span class="dim">{{ $req->customer_email }}</span>
              @endif
              @if($req->customer_nif)
                <br><span class="dim">NIF: {{ $req->customer_nif }}</span>
              @endif
            </td>
            <td style="white-space:nowrap;">{{ $req->customer_phone }}</td>
            <td>
              @if($req->payment_method === 'multicaixa_express')
                <span class="badge bg-amber">Multicaixa</span>
              @elseif($req->payment_method === 'paypal')
                <span class="badge bg-blue">PayPal</span>
              @else
                <span class="dim">{{ $req->payment_method }}</span>
              @endif
            </td>
            <td>
              @if($req->status === 'activated')
                <span class="badge bg-green">Activado</span>
              @elseif($req->status === 'confirmed')
                <span class="badge bg-blue">Confirmado</span>
              @elseif($req->status === 'pending')
                <span class="badge" style="background:#fee2e2;color:#b91c1c;">Pendente ⚠</span>
              @elseif($req->status === 'awaiting_payment')
                <span class="badge bg-amber">Aguarda pag.</span>
              @elseif($req->status === 'cancelled')
                <span class="badge bg-gray">Cancelado</span>
              @else
                <span class="badge bg-gray">{{ $req->status }}</span>
              @endif
            </td>
            <td class="dim" style="white-space:nowrap;">{{ $req->created_at->format('d/m/Y H:i') }}</td>
            <td>
              @if(in_array($req->status, ['pending', 'confirmed']))
                <div style="display:flex;gap:.35rem;flex-wrap:wrap;">
                  <form method="POST" action="{{ route('admin.family_requests.confirmar', $req) }}"
                        style="display:inline;"
                        onsubmit="return confirm('Activar pedido #{{ $req->id }} no SG manualmente?');">
                    @csrf
                    <button type="submit" class="ap-btn ap-btn-primary ap-btn-sm">Activar no SG</button>
                  </form>
                  <form method="POST" action="{{ route('admin.family_requests.cancelar', $req) }}"
                        style="display:inline;"
                        onsubmit="return confirm('Cancelar pedido #{{ $req->id }}?');">
                    @csrf
                    <button type="submit" class="ap-btn ap-btn-danger ap-btn-sm">Cancelar</button>
                  </form>
                </div>
              @elseif($req->status === 'awaiting_payment')
                <form method="POST" action="{{ route('admin.family_requests.cancelar', $req) }}"
                      style="display:inline;"
                      onsubmit="return confirm('Cancelar pedido #{{ $req->id }}? O pagamento ainda não foi confirmado.');">
                  @csrf
                  <button type="submit" class="ap-btn ap-btn-danger ap-btn-sm">Cancelar</button>
                </form>
              @elseif($req->status === 'activated')
                <span class="badge bg-green" style="font-size:.75rem;">Janela adicionada</span>
                @if($req->notes)
                  <br><span class="dim" style="font-size:.72rem;">{{ $req->notes }}</span>
                @endif
              @else
                <span class="dim">&mdash;</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9">
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
