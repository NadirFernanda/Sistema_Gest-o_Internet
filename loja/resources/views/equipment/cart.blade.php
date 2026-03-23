@extends('layouts.app')

@section('content')
<div class="page-hero">
  <div class="container">
    <span class="page-hero__eyebrow">Equipamentos</span>
    <h1 class="page-hero__title">Carrinho de Compras 🛒</h1>
  </div>
</div>

<div class="page-body">
  <div class="container container--880">

    @if (session('success'))
      <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    @if (empty($cart))
      <div class="card" style="text-align:center;padding:3rem 1.5rem;">
        <p style="color:var(--text-muted);font-size:1.1rem;">O seu carrinho está vazio.</p>
        <a href="{{ route('equipment.index') }}" class="btn-modern" style="display:inline-block;margin-top:1rem;">Ver Equipamentos</a>
      </div>
    @else
      <div class="card" style="margin-bottom:2rem;">
        <div class="card-body" style="padding:0;overflow-x:auto;">
          <table class="data-table">
            <thead>
              <tr>
                <th>Produto</th>
                <th style="text-align:right;">Preço unit.</th>
                <th style="text-align:center;">Qtd.</th>
                <th style="text-align:right;">Subtotal</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @php $total = 0; @endphp
              @foreach ($cart as $item)
                @php
                  $subtotal = $item['unit_price_aoa'] * $item['quantity'];
                  $total += $subtotal;
                @endphp
                <tr>
                  <td data-label="Produto">
                    @if (!empty($item['image_path']))
                      <img src="{{ asset($item['image_path']) }}" alt="{{ $item['product_name'] }}"
                           style="width:48px;height:48px;object-fit:cover;border-radius:0.5rem;vertical-align:middle;margin-right:0.5rem;">
                    @endif
                    <strong>{{ $item['product_name'] }}</strong>
                  </td>
                  <td data-label="Preço unit." style="text-align:right;">{{ number_format($item['unit_price_aoa'], 0, ',', '.') }} Kz</td>
                  <td data-label="Qtd." style="text-align:center;">{{ $item['quantity'] }}</td>
                  <td data-label="Subtotal" style="text-align:right;font-weight:700;">{{ number_format($subtotal, 0, ',', '.') }} Kz</td>
                  <td data-label="">
                    <form method="POST" action="{{ route('equipment.cart.remove') }}">
                      @csrf
                      <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                      <button type="submit" class="cart-remove-btn" aria-label="Remover {{ $item['product_name'] }}">✕</button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <td colspan="3" style="text-align:right;font-weight:700;">Total:</td>
                <td style="text-align:right;font-weight:800;font-size:1.15rem;color:var(--brand);">{{ number_format($total, 0, ',', '.') }} Kz</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div class="cart-actions">
        <div class="cart-actions__left">
          <a href="{{ route('equipment.index') }}" class="btn-ghost--light">← Continuar a comprar</a>
          <form method="POST" action="{{ route('equipment.cart.clear') }}">
            @csrf
            <button type="submit" class="btn-ghost--danger">🗑 Limpar carrinho</button>
          </form>
        </div>
        <a href="{{ route('equipment.checkout') }}" class="btn-primary">Finalizar Encomenda →</a>
      </div>
    @endif
  </div>
</div>
@endsection
