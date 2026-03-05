@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Painel do Revendedor" style="padding-top:2rem;padding-bottom:2rem;">
  <div class="container">
    <h2>Painel do Revendedor</h2>
    <p class="lead">Área exclusiva para revendedores aprovados comprarem códigos em quantidade.</p>

    @if(session('status'))
      <p class="auth-footer-note" style="margin-bottom:1rem;color:#15803d;font-weight:600;">{{ session('status') }}</p>
    @endif

    <div class="auth-grid" style="margin-top:1rem;">
      <div class="auth-card">
        <h3>Identificação</h3>

        @if(!$application)
          <form action="{{ route('reseller.panel.login') }}" method="POST" novalidate>
            @csrf
            <div class="form-row">
              <label for="rev-email">Email de revendedor <span style="color:#d00">*</span></label>
              <input id="rev-email" name="email" type="email" placeholder="revendedor@exemplo.ao" required />
            </div>
            <p class="auth-footer-note">Utilize o mesmo email usado no formulário "Quero ser revendedor". Só revendedores aprovados podem aceder a esta área.</p>
            <div style="margin-top:1rem;">
              <button type="submit" class="btn-primary">Entrar</button>
            </div>
          </form>
        @else
          <p class="auth-footer-note">Revendedor: <strong>{{ $application->full_name }}</strong></p>
          <p class="auth-footer-note">Email: <strong>{{ $application->email }}</strong></p>

          <div class="info-grid" style="margin-top:1rem;">
            <div class="info-card">
              <h3>Saldo de créditos</h3>
              <p>Valor bruto adquirido: <strong>{{ number_format($totals['total_gross'], 0, ',', '.') }} AOA</strong></p>
              <p>Valor líquido após descontos: <strong>{{ number_format($totals['total_net'], 0, ',', '.') }} AOA</strong></p>
              <p>Número de códigos gerados: <strong>{{ $totals['codes_total'] }}</strong></p>
            </div>

            <div class="info-card">
              <h3>Comprar novos códigos</h3>
              <form action="{{ route('reseller.panel.purchase') }}" method="POST">
                @csrf
                <div class="form-row">
                  <label for="codes_count">Quantidade de códigos</label>
                  <input id="codes_count" name="codes_count" type="number" min="1" max="1000" value="10" class="newsletter-input" />
                </div>
                <p class="auth-footer-note">O valor em Kz será calculado com base na tabela de preços e descontos por escalão.</p>
                <div style="margin-top:1rem;">
                  <button type="submit" class="btn-primary">Gerar códigos</button>
                </div>
              </form>
            </div>
          </div>

          <form action="{{ route('reseller.panel.logout') }}" method="POST" style="margin-top:1rem;">
            @csrf
            <button type="submit" class="btn-ghost">Terminar sessão de revendedor</button>
          </form>
        @endif
      </div>

      <div class="auth-card">
        <h3>Histórico de compras (CSV)</h3>

        @if(!$application)
          <p class="auth-footer-note">Identifique-se ao lado para ver as compras associadas ao seu email.</p>
        @else
          @if($purchases instanceof \Illuminate\Pagination\LengthAwarePaginator && $purchases->count())
            <div class="plans-table" style="margin-top:.75rem;overflow-x:auto;">
              <table class="w-full text-sm" style="border-collapse:collapse;">
                <thead>
                  <tr>
                    <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">#</th>
                    <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Data</th>
                    <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Bruto</th>
                    <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Desconto</th>
                    <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Líquido</th>
                    <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Códigos</th>
                    <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">CSV</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($purchases as $purchase)
                    <tr>
                      <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">#{{ $purchase->id }}</td>
                      <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ optional($purchase->created_at)->format('d/m/Y H:i') }}</td>
                      <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ number_format($purchase->gross_amount_aoa, 0, ',', '.') }} AOA</td>
                      <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ $purchase->discount_percent }}%</td>
                      <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ number_format($purchase->net_amount_aoa, 0, ',', '.') }} AOA</td>
                      <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ $purchase->codes_count }}</td>
                      <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                        <a href="{{ route('reseller.panel.purchase.csv', ['purchase' => $purchase->id]) }}" class="btn-ghost">Download CSV</a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <div style="margin-top:.75rem;">
              {{ $purchases->links() }}
            </div>
          @else
            <p class="auth-footer-note">Ainda não existem compras registadas para este revendedor.</p>
          @endif
        @endif
      </div>
    </div>
  </div>
</section>
@endsection
