@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Compras de revendedores">
  <div class="container">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:.5rem;">
      <div>
        <h2 style="margin:0;">Compras de Revendedores</h2>
        <p class="lead" style="margin:.25rem 0 0;">Histórico de todas as compras em bloco effectuadas pelos revendedores aprovados.</p>
      </div>
      <a href="{{ route('admin.resellers.index') }}" class="btn-modern" style="font-size:.85rem;padding:.4rem .9rem;">← Candidaturas</a>
    </div>

    {{-- Resumo global --}}
    <div class="info-grid" style="margin:1.25rem 0;">
      <div class="info-card">
        <h3>Receita total (líquida)</h3>
        <p style="font-size:1.5rem;font-weight:700;">{{ number_format($totalRevenue, 0, ',', '.') }} AOA</p>
      </div>
      <div class="info-card">
        <h3>Códigos vendidos (total)</h3>
        <p style="font-size:1.5rem;font-weight:700;">{{ number_format($totalCodes, 0, ',', '.') }}</p>
      </div>
    </div>

    {{-- Filtro por revendedor --}}
    <form method="get" style="display:flex;gap:.75rem;align-items:flex-end;flex-wrap:wrap;margin-bottom:1rem;">
      <div class="form-row" style="flex:1;min-width:180px;">
        <label for="reseller_id">ID do Revendedor</label>
        <input id="reseller_id" name="reseller_id" value="{{ request('reseller_id') }}"
               class="newsletter-input" placeholder="ex: 3">
      </div>
      <div class="form-actions" style="margin-top:0;">
        <button type="submit" class="btn-primary">Filtrar</button>
        @if(request('reseller_id'))
          <a href="{{ route('admin.resellers.purchases.index') }}" class="btn-modern" style="font-size:.88rem;padding:.45rem .9rem;">Limpar</a>
        @endif
      </div>
    </form>

    <div class="plans-table" style="overflow-x:auto;">
      <table class="w-full text-sm" style="border-collapse:collapse;">
        <thead>
          <tr>
            <th style="text-align:left;padding:.4rem .5rem;border-bottom:1px solid #e5e7eb;">#</th>
            <th style="text-align:left;padding:.4rem .5rem;border-bottom:1px solid #e5e7eb;">Revendedor</th>
            <th style="text-align:left;padding:.4rem .5rem;border-bottom:1px solid #e5e7eb;">Cód. comprados</th>
            <th style="text-align:left;padding:.4rem .5rem;border-bottom:1px solid #e5e7eb;">Desconto</th>
            <th style="text-align:left;padding:.4rem .5rem;border-bottom:1px solid #e5e7eb;">Valor bruto</th>
            <th style="text-align:left;padding:.4rem .5rem;border-bottom:1px solid #e5e7eb;">Valor líquido</th>
            <th style="text-align:left;padding:.4rem .5rem;border-bottom:1px solid #e5e7eb;">CSV</th>
            <th style="text-align:left;padding:.4rem .5rem;border-bottom:1px solid #e5e7eb;">Data</th>
          </tr>
        </thead>
        <tbody>
          @forelse($purchases as $purchase)
            <tr>
              <td style="padding:.4rem .5rem;border-bottom:1px solid #f3f4f6;">#{{ $purchase->id }}</td>
              <td style="padding:.4rem .5rem;border-bottom:1px solid #f3f4f6;">
                @if($purchase->application)
                  <a href="{{ route('admin.resellers.index', ['q' => $purchase->application->id]) }}"
                     style="color:inherit;">
                    {{ $purchase->application->full_name }}
                  </a>
                  <span class="muted" style="font-size:.8rem;display:block;">ID {{ $purchase->reseller_application_id }}</span>
                @else
                  <span class="muted">ID {{ $purchase->reseller_application_id }}</span>
                @endif
              </td>
              <td style="padding:.4rem .5rem;border-bottom:1px solid #f3f4f6;">{{ $purchase->codes_count }}</td>
              <td style="padding:.4rem .5rem;border-bottom:1px solid #f3f4f6;">{{ $purchase->discount_percent }}%</td>
              <td style="padding:.4rem .5rem;border-bottom:1px solid #f3f4f6;">{{ number_format($purchase->gross_amount_aoa, 0, ',', '.') }} AOA</td>
              <td style="padding:.4rem .5rem;border-bottom:1px solid #f3f4f6;font-weight:600;">{{ number_format($purchase->net_amount_aoa, 0, ',', '.') }} AOA</td>
              <td style="padding:.4rem .5rem;border-bottom:1px solid #f3f4f6;">
                @if($purchase->csv_path)
                  <a href="{{ route('reseller.panel.purchase.csv', $purchase->id) }}"
                     class="btn-modern" style="font-size:.8rem;padding:.25rem .6rem;">
                    ⬇ CSV
                  </a>
                @else
                  <span class="muted">—</span>
                @endif
              </td>
              <td style="padding:.4rem .5rem;border-bottom:1px solid #f3f4f6;">{{ optional($purchase->created_at)->format('d/m/Y H:i') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="8" style="padding:.75rem;text-align:center;" class="muted">Nenhuma compra registada.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div style="margin-top:1rem;">
      {{ $purchases->links() }}
    </div>
  </div>
</section>
@endsection
