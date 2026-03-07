@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Carrinho de compras">
  <div class="container" style="max-width:820px;">
    <h2>Carrinho de Compras 🛒</h2>

    @if (session('success'))
      <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    @if (empty($cart))
      <div class="plan-card-modern" style="max-width:100%;text-align:center;">
        <p style="color:#64748b;font-size:1.1rem;">O seu carrinho está vazio.</p>
        <a href="{{ route('equipment.index') }}" class="btn-modern" style="display:inline-block;margin-top:1rem;">Ver Equipamentos</a>
      </div>
    @else
      <div class="plans-table" style="overflow-x:auto;margin-bottom:2rem;">
        <table style="width:100%;border-collapse:collapse;background:#fff;border-radius:0.75rem;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.06);">
          <thead>
            <tr style="background:#f8fafc;">
              <th style="text-align:left;padding:0.75rem 1rem;border-bottom:1.5px solid #e2e8f0;">Produto</th>
              <th style="text-align:right;padding:0.75rem 1rem;border-bottom:1.5px solid #e2e8f0;">Preço unit.</th>
              <th style="text-align:center;padding:0.75rem 1rem;border-bottom:1.5px solid #e2e8f0;">Qtd.</th>
              <th style="text-align:right;padding:0.75rem 1rem;border-bottom:1.5px solid #e2e8f0;">Subtotal</th>
              <th style="padding:0.75rem 1rem;border-bottom:1.5px solid #e2e8f0;"></th>
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
                <td style="padding:0.75rem 1rem;border-bottom:1px solid #f1f5f9;">
                  @if (!empty($item['image_path']))
                    <img src="{{ asset($item['image_path']) }}" alt="{{ $item['product_name'] }}"
                         style="width:48px;height:48px;object-fit:cover;border-radius:0.5rem;vertical-align:middle;margin-right:0.5rem;">
                  @endif
                  <strong>{{ $item['product_name'] }}</strong>
                </td>
                <td style="padding:0.75rem 1rem;border-bottom:1px solid #f1f5f9;text-align:right;">{{ number_format($item['unit_price_aoa'], 0, ',', '.') }} Kz</td>
                <td style="padding:0.75rem 1rem;border-bottom:1px solid #f1f5f9;text-align:center;">{{ $item['quantity'] }}</td>
                <td style="padding:0.75rem 1rem;border-bottom:1px solid #f1f5f9;text-align:right;font-weight:700;">{{ number_format($subtotal, 0, ',', '.') }} Kz</td>
                <td style="padding:0.75rem 1rem;border-bottom:1px solid #f1f5f9;">
                  <form method="POST" action="{{ route('equipment.cart.remove') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                    <button type="submit" style="background:none;border:none;cursor:pointer;color:#ef4444;font-size:1.1rem;" title="Remover">✕</button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" style="padding:1rem;text-align:right;font-weight:700;font-size:1.05rem;">Total:</td>
              <td style="padding:1rem;text-align:right;font-weight:800;font-size:1.2rem;color:#2563eb;">{{ number_format($total, 0, ',', '.') }} Kz</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div style="display:flex;gap:1rem;flex-wrap:wrap;justify-content:space-between;align-items:center;">
        <div style="display:flex;gap:0.75rem;">
          <a href="{{ route('equipment.index') }}" class="btn-modern" style="background:linear-gradient(90deg,#94a3b8,#64748b);">← Continuar a comprar</a>
          <form method="POST" action="{{ route('equipment.cart.clear') }}">
            @csrf
            <button type="submit" class="btn-modern" style="background:linear-gradient(90deg,#f87171,#ef4444);">🗑 Limpar carrinho</button>
          </form>
        </div>
        <a href="{{ route('equipment.checkout') }}" class="btn-modern" style="font-size:1.12rem;padding:0.85rem 2rem;">Finalizar Encomenda →</a>
      </div>
    @endif
  </div>
</section>
@endsection
