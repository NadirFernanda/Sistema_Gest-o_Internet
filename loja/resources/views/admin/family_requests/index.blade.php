@extends('layouts.app')

@section('title', 'Pedidos de Planos Familiares — Admin')

@section('content')
<section class="planos-section" aria-label="Pedidos de planos familiares e empresariais">
  <div class="container">

    <h2>Pedidos de Planos Familiares &amp; Empresariais</h2>
    <p class="lead">
      Quando confirmar um pedido, o Sistema de Gestão cria automaticamente o registo do cliente
      e adiciona a janela de acesso ao plano — não é necessária qualquer acção manual no SG.
    </p>

    {{-- Alertas --}}
    @if(session('success'))
      <div class="checkout-errors" style="background:#f0fdf4;border-color:#86efac;color:#166534;margin-bottom:1rem;">
        <p>{{ session('success') }}</p>
      </div>
    @endif
    @if(session('error'))
      <div class="checkout-errors" style="margin-bottom:1rem;">
        <p>{{ session('error') }}</p>
      </div>
    @endif

    {{-- Contadores por estado --}}
    <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;">
      @php
        $filterLabels = [
          'pending'   => ['label' => 'Pendentes',   'color' => '#f59e0b'],
          'confirmed' => ['label' => 'Confirmados', 'color' => '#3b82f6'],
          'activated' => ['label' => 'Activados',   'color' => '#22c55e'],
          'cancelled' => ['label' => 'Cancelados',  'color' => '#ef4444'],
        ];
      @endphp
      @foreach($filterLabels as $key => $meta)
        <a href="{{ route('admin.family_requests.index', ['status' => $key]) }}"
           class="checkout-summary-card"
           style="flex:1;min-width:110px;text-align:center;padding:0.9rem;text-decoration:none;
                  {{ $status === $key ? 'border-color:'.$meta['color'].';' : 'opacity:0.65;' }}">
          <p class="total" style="font-size:1.5rem;margin:0;color:{{ $meta['color'] }};">{{ $counts[$key] }}</p>
          <p style="font-size:0.78rem;color:#64748b;margin:0;">{{ $meta['label'] }}</p>
        </a>
      @endforeach
      <a href="{{ route('admin.family_requests.index') }}"
         class="checkout-summary-card"
         style="flex:1;min-width:110px;text-align:center;padding:0.9rem;text-decoration:none;
                {{ $status === 'all' ? '' : 'opacity:0.65;' }}">
        <p class="total" style="font-size:1.5rem;margin:0;">{{ array_sum($counts) }}</p>
        <p style="font-size:0.78rem;color:#64748b;margin:0;">Todos</p>
      </a>
    </div>

    {{-- Tabela de pedidos --}}
    @if($requests->count() === 0)
      <p style="color:#64748b;">Nenhum pedido encontrado para o filtro selecionado.</p>
    @else
    <div style="overflow-x:auto;">
      <table class="admin-table" style="width:100%;border-collapse:collapse;font-size:0.88rem;">
        <thead>
          <tr style="background:#f8fafc;text-align:left;">
            <th style="padding:0.6rem 0.8rem;">#</th>
            <th style="padding:0.6rem 0.8rem;">Plano</th>
            <th style="padding:0.6rem 0.8rem;">Cliente</th>
            <th style="padding:0.6rem 0.8rem;">Contato</th>
            <th style="padding:0.6rem 0.8rem;">Pagamento</th>
            <th style="padding:0.6rem 0.8rem;">Estado</th>
            <th style="padding:0.6rem 0.8rem;">Data</th>
            <th style="padding:0.6rem 0.8rem;">Acções</th>
          </tr>
        </thead>
        <tbody>
          @foreach($requests as $req)
          @php
            $statusColor = match($req->status) {
              'pending'   => '#f59e0b',
              'confirmed' => '#3b82f6',
              'activated' => '#22c55e',
              'cancelled' => '#ef4444',
              default     => '#64748b',
            };
            $statusLabel = match($req->status) {
              'pending'   => 'Pendente',
              'confirmed' => 'Confirmado',
              'activated' => 'Activado',
              'cancelled' => 'Cancelado',
              default     => $req->status,
            };
          @endphp
          <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:0.6rem 0.8rem;color:#94a3b8;">{{ $req->id }}</td>
            <td style="padding:0.6rem 0.8rem;font-weight:600;">{{ $req->plan_name }}</td>
            <td style="padding:0.6rem 0.8rem;">
              {{ $req->customer_name }}
              @if($req->customer_email)
                <br><span style="color:#64748b;font-size:0.8rem;">{{ $req->customer_email }}</span>
              @endif
            </td>
            <td style="padding:0.6rem 0.8rem;">{{ $req->customer_phone }}</td>
            <td style="padding:0.6rem 0.8rem;text-transform:capitalize;">
              {{ str_replace('_', ' ', $req->payment_method) }}
            </td>
            <td style="padding:0.6rem 0.8rem;">
              <span style="background:{{ $statusColor }}22;color:{{ $statusColor }};padding:0.2rem 0.6rem;border-radius:999px;font-size:0.78rem;font-weight:700;">
                {{ $statusLabel }}
              </span>
            </td>
            <td style="padding:0.6rem 0.8rem;white-space:nowrap;color:#64748b;">
              {{ $req->created_at->format('d/m/Y H:i') }}
            </td>
            <td style="padding:0.6rem 0.8rem;">
              @if(in_array($req->status, ['pending', 'confirmed']))
                {{-- Confirm = sync janela in SG --}}
                <form method="POST" action="{{ route('admin.family_requests.confirmar', $req) }}"
                      style="display:inline;"
                      onsubmit="return confirm('Confirmar pedido #{{ $req->id }} e adicionar janela no SG?');">
                  @csrf
                  <button type="submit" class="btn-primary"
                          style="padding:0.25rem 0.75rem;font-size:0.8rem;border:none;cursor:pointer;">
                    ✅ Confirmar
                  </button>
                </form>
                <form method="POST" action="{{ route('admin.family_requests.cancelar', $req) }}"
                      style="display:inline;margin-left:0.4rem;"
                      onsubmit="return confirm('Cancelar pedido #{{ $req->id }}?');">
                  @csrf
                  <button type="submit"
                          style="padding:0.25rem 0.75rem;font-size:0.8rem;background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;border-radius:6px;cursor:pointer;">
                    ✖ Cancelar
                  </button>
                </form>
              @elseif($req->status === 'activated')
                <span style="color:#22c55e;font-size:0.82rem;">✅ Janela adicionada no SG</span>
                @if($req->notes)
                  <br><small style="color:#94a3b8;font-size:0.75rem;">{{ $req->notes }}</small>
                @endif
              @else
                <span style="color:#94a3b8;font-size:0.82rem;">—</span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div style="margin-top:1rem;">
      {{ $requests->links() }}
    </div>
    @endif

  </div>
</section>
@endsection
