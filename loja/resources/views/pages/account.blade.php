@extends('layouts.app')

@section('content')
  <div class="page-hero">
    <div class="container">
      <span class="page-hero__eyebrow">Área Pessoal</span>
      <h1 class="page-hero__title">A Minha Conta</h1>
      <p class="page-hero__desc">Consulte o histórico de compras feitas com o seu endereço de email e volte rapidamente ao catálogo para comprar novos códigos.</p>
    </div>
  </div>

  <div class="page-body">
    <div class="container">
      <div class="auth-grid">
        <div class="auth-card">
          <h3>Identifique-se pelo email</h3>
          <form action="{{ route('account.login') }}" method="POST" novalidate>
            @csrf
            <div class="field">
              <label for="account-email">Endereço de email <span style="color:#d00">*</span></label>
              <input id="account-email" name="email" type="email" value="{{ old('email', $currentEmail) }}" placeholder="seu@email.ao" required />
            </div>

            <p class="auth-footer-note">Usaremos este email apenas para localizar as suas compras realizadas na loja.</p>

            <div style="margin-top:1rem;">
              <button class="btn-primary" type="submit">Ver meu histórico</button>
            </div>
          </form>
        </div>

        <div class="auth-card">
          <h3>Histórico de compras</h3>

          @if(session('status'))
            <p class="auth-footer-note" style="margin-bottom:.75rem; color:#15803d; font-weight:600;">{{ session('status') }}</p>
          @endif

          @if(!$currentEmail)
            <p class="auth-footer-note">Introduza o seu email ao lado para ver as compras associadas a ele.</p>
          @else
            <p class="auth-footer-note">Mostrando compras associadas a: <strong>{{ $currentEmail }}</strong></p>

            @if($orders instanceof \Illuminate\Pagination\LengthAwarePaginator && $orders->count())
              <div class="card" style="margin-top:.75rem;">
                <div class="card-body" style="padding:0;overflow-x:auto;">
                  <table class="data-table">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Plano</th>
                        <th>Valor</th>
                        <th>Estado</th>
                        <th>Criada em</th>
                        <th>Ações</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($orders as $order)
                        <tr>
                          <td>#{{ $order->id }}</td>
                          <td>{{ $order->plan_name ?? $order->plan_id }}</td>
                          <td>{{ number_format($order->amount_aoa, 0, ',', '.') }} AOA</td>
                          <td>{{ $order->status }}</td>
                          <td>{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
                          <td>
                            <div style="display:flex;flex-wrap:wrap;gap:.35rem;">
                              @if($order->plan_id)
                                <a href="{{ route('store.checkout', ['plan' => $order->plan_id]) }}" class="btn-primary">Comprar novamente</a>
                              @endif

                              @if($order->isPaid() && $order->customer_email)
                                <form action="{{ route('account.orders.resend-email', ['order' => $order->id]) }}" method="POST">
                                  @csrf
                                  <button type="submit" class="btn-ghost">Reenviar por email</button>
                                </form>
                              @endif

                              @if($order->isPaid() && $order->wifi_code)
                                <a href="{{ route('account.orders.whatsapp', ['order' => $order->id]) }}" class="btn-ghost" target="_blank" rel="noopener">Abrir no WhatsApp</a>
                              @endif
                            </div>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>

              <div style="margin-top:.75rem;">
                {{ $orders->links() }}
              </div>
            @else
              <p class="auth-footer-note">Ainda não encontrámos compras associadas a este email.</p>
            @endif

            <form action="{{ route('account.logout') }}" method="POST" style="margin-top:1rem;">
              @csrf
              <button type="submit" class="btn-ghost">Trocar de email</button>
            </form>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection
