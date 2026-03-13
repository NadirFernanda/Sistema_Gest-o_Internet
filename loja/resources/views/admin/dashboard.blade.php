@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Painel administrativo da loja">
  <div class="container">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:.25rem;">
      <h2 style="margin:0;">Visão geral da loja</h2>
      <form method="POST" action="{{ route('admin.logout') }}" style="margin:0;">
        @csrf
        <button type="submit" class="btn-modern"
                style="font-size:.85rem;padding:.4rem .9rem;background:#f3f4f6;color:#374151;border:1px solid #d1d5db;">
          Sair (logout)
        </button>
      </form>
    </div>
    <p class="lead">Resumo rápido das vendas de autovenda e pedidos de revenda.</p>

    <div class="info-grid" style="margin-top:1.5rem;">
      <div class="info-card">
        <h3>Ordens de autovenda</h3>
        <p>Total de ordens: <strong>{{ $totalOrders }}</strong></p>
        <p>Pagas: <strong>{{ $paidOrders }}</strong></p>
        <p>A aguardar pagamento: <strong>{{ $awaitingPayment }}</strong></p>
        <p style="margin-top:.5rem;">Receita confirmada: <strong>{{ number_format($totalRevenueAoa, 0, ',', '.') }} AOA</strong></p>
      </div>

      <div class="info-card">
        <h3>Revenda</h3>
        <p>Pedidos pendentes: <strong>{{ $pendingResellers }}</strong></p>
        <p>Total de pedidos: <strong>{{ $totalResellers }}</strong></p>
        <div style="display:flex;gap:0.5rem;margin-top:0.75rem;flex-wrap:wrap;">
          <a href="{{ route('admin.resellers.index') }}" class="btn-modern" style="font-size:0.88rem;padding:0.4rem 0.9rem;">Candidaturas</a>
          <a href="{{ route('admin.resellers.purchases.index') }}" class="btn-modern" style="font-size:0.88rem;padding:0.4rem 0.9rem;">Compras em Bloco</a>
        </div>
      </div>

      <div class="info-card">
        <h3>Equipamentos &amp; Produtos</h3>
        <p>Produtos no catálogo: <strong>{{ $totalProducts }}</strong></p>
        <p>Encomendas totais: <strong>{{ $totalEquipOrders }}</strong></p>
        <p>Encomendas pendentes: <strong>{{ $newEquipOrders }}</strong></p>
        <p style="margin-top:.5rem;">Receita confirmada: <strong>{{ number_format($totalEquipRevenue, 0, ',', '.') }} AOA</strong></p>
        <div style="display:flex;gap:0.5rem;margin-top:0.75rem;flex-wrap:wrap;">
          <a href="{{ route('admin.equipment.products.index') }}" class="btn-modern" style="font-size:0.88rem;padding:0.4rem 0.9rem;">Produtos</a>
          <a href="{{ route('admin.equipment.orders.index') }}" class="btn-modern" style="font-size:0.88rem;padding:0.4rem 0.9rem;">Encomendas</a>
        </div>
      </div>

      <div class="info-card">
        <h3>📊 Estatísticas da Loja</h3>
        <p style="color:#666;font-size:.9rem;margin-bottom:1rem;">Os 4 números de destaque na página inicial (clientes, uptime, instalação, suporte).</p>
        <a href="{{ route('admin.site_stats.index') }}" class="btn-modern" style="font-size:0.88rem;padding:0.4rem 0.9rem;">Editar estatísticas</a>
      </div>

      <div class="info-card">
        <h3>🔑 Stock de Códigos WiFi</h3>
        <p>Disponíveis (total): <strong>{{ $availableWifiCodes }}</strong></p>
        @php
          $wifiPlanLabels = ['diario' => 'Diário', 'semanal' => 'Semanal', 'mensal' => 'Mensal'];
        @endphp
        <ul style="font-size:0.85rem;margin:.4rem 0 .6rem;padding-left:1.1rem;">
          @foreach($wifiPlanLabels as $pid => $plabel)
            @php $n = $wifiCodesByPlan[$pid] ?? 0; @endphp
            <li style="color:{{ $n === 0 ? '#dc2626' : ($n < 5 ? '#d97706' : '#166534') }}">
              {{ $plabel }}: <strong>{{ $n }}</strong>
              @if($n === 0) ⚠ esgotado @elseif($n < 5) ⚠ baixo @endif
            </li>
          @endforeach
        </ul>
        <p style="font-size:0.82rem;color:#64748b;">Utilizados: {{ $usedWifiCodes }}</p>
        @if($availableWifiCodes === 0)
          <p style="color:#dc2626;font-weight:700;margin-top:.5rem;">⚠️ Sem stock! Importe para poder vender.</p>
        @elseif($availableWifiCodes < 10)
          <p style="color:#d97706;font-weight:700;margin-top:.5rem;">⚠️ Stock baixo — importe mais códigos.</p>
        @endif
        <div style="margin-top:.75rem;">
          <a href="{{ route('admin.wifi_codes.index') }}" class="btn-modern" style="font-size:0.88rem;padding:0.4rem 0.9rem;">Gerir / Importar</a>
        </div>
      </div>

      <div class="info-card">
        <h3>🏠 Planos Familiares / Empresariais</h3>
        <p>Pendentes: <strong>{{ $pendingFamilyRequests }}</strong></p>
        @if($pendingFamilyRequests > 0)
          <p style="color:#d97706;font-weight:700;margin-top:.5rem;">⚠️ Há {{ $pendingFamilyRequests }} pedido(s) aguardando confirmação.</p>
        @else
          <p style="color:#22c55e;font-size:0.85rem;margin-top:.5rem;">Nenhum pedido pendente.</p>
        @endif
        <div style="margin-top:.75rem;">
          <a href="{{ route('admin.family_requests.index') }}" class="btn-modern" style="font-size:0.88rem;padding:0.4rem 0.9rem;">Ver Pedidos</a>
        </div>
      </div>
    </div>

    <div class="info-grid" style="margin-top:2rem;">
      <div class="info-card" style="grid-column:1 / -1;">
        <h3>Últimas ordens</h3>
        @if($recentOrders->isEmpty())
          <p>Nenhuma ordem registada ainda.</p>
        @else
          <div class="plans-table" style="overflow-x:auto;">
            <table class="w-full text-sm" style="border-collapse:collapse;">
              <thead>
                <tr>
                  <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">#</th>
                  <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Plano</th>
                  <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Valor</th>
                  <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Estado</th>
                  <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Método</th>
                  <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Criada em</th>
                </tr>
              </thead>
              <tbody>
                @foreach($recentOrders as $order)
                  <tr>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">#{{ $order->id }}</td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ $order->plan_name ?? $order->plan_id }}</td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ number_format($order->amount_aoa, 0, ',', '.') }} AOA</td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ $order->status }}</td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ $order->payment_method ?? '—' }}</td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>
</section>
@endsection
