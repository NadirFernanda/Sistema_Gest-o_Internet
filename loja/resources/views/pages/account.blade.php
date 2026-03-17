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

      @if(session('status'))
        <div style="background:#f0fdf4;border:1.5px solid #86efac;color:#15803d;border-radius:8px;padding:.85rem 1rem;margin-bottom:1rem;font-size:.92rem;">
          ✅ {{ session('status') }}
        </div>
      @endif
      @if(session('error'))
        <div style="background:#fef2f2;border:1.5px solid #fecaca;color:#991b1b;border-radius:8px;padding:.85rem 1rem;margin-bottom:1rem;font-size:.92rem;">
          ⚠️ {{ session('error') }}
        </div>
      @endif

      <div class="auth-grid">

        {{-- ── Coluna esquerda: formulário email OU formulário OTP ── --}}
        <div class="auth-card">

          @if($otpPending)
            {{-- PASSO 2: introduzir o código recebido por email --}}
            <h3>Verificação por email</h3>
            <p style="font-size:.9rem;color:#475569;margin-bottom:1rem;line-height:1.55;">
              Enviámos um código de <strong>6 dígitos</strong> para <strong>{{ $otpEmail }}</strong>.<br>
              Introduza-o abaixo para aceder ao seu histórico.<br>
              <span style="font-size:.82rem;color:#94a3b8;">O código expira em 10 minutos.</span>
            </p>
            <form action="{{ route('account.verify') }}" method="POST" novalidate autocomplete="off">
              @csrf
              <div class="field">
                <label for="otp-code">Código de verificação <span style="color:#d00">*</span></label>
                <input id="otp-code" name="otp" type="text" inputmode="numeric" pattern="[0-9]{6}"
                       maxlength="6" placeholder="_ _ _ _ _ _" required autofocus
                       style="font-size:1.6rem;letter-spacing:.35em;text-align:center;font-family:monospace;" />
              </div>
              <div style="margin-top:1rem;">
                <button class="btn-primary" type="submit">Confirmar código</button>
              </div>
            </form>
            <form action="{{ route('account.logout') }}" method="POST" style="margin-top:.75rem;">
              @csrf
              <button type="submit" class="btn-ghost" style="font-size:.82rem;">← Usar outro email</button>
            </form>

          @else
            {{-- PASSO 1: introduzir email --}}
            <h3>Identifique-se pelo email</h3>
            <form action="{{ route('account.login') }}" method="POST" novalidate>
              @csrf
              <div class="field">
                <label for="account-email">Endereço de email <span style="color:#d00">*</span></label>
                <input id="account-email" name="email" type="email" value="{{ old('email') }}" placeholder="seu@email.ao" required autocomplete="email" />
              </div>
              <p class="auth-footer-note">Receberá um código de verificação neste endereço para confirmar a sua identidade antes de aceder ao histórico.</p>
              <div style="margin-top:1rem;">
                <button class="btn-primary" type="submit">Enviar código de verificação</button>
              </div>
            </form>
          @endif

        </div>

        {{-- ── Coluna direita: histórico ── --}}
        <div class="auth-card">
          <h3>Histórico de compras</h3>

          @if(!$currentEmail && !$otpPending)
            <p class="auth-footer-note">Introduza o seu email ao lado e confirme o código que receberá para ver as compras associadas.</p>

          @elseif($otpPending)
            <p class="auth-footer-note" style="color:#92400e;">
              🔒 Acesso bloqueado até confirmar o código enviado para <strong>{{ $otpEmail }}</strong>.
            </p>

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
              <button type="submit" class="btn-ghost">Terminar sessão</button>
            </form>
          @endif
        </div>

      </div>
    </div>
  </div>
@endsection

