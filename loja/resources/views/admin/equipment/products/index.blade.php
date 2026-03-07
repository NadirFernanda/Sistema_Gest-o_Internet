@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Gestão de produtos">
  <div class="container">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
      <div>
        <h2>Produtos / Equipamentos</h2>
        <p class="lead">Gerir o catálogo de produtos disponíveis na loja.</p>
      </div>
      <a href="{{ route('admin.equipment.products.create') }}" class="btn-modern">+ Novo Produto</a>
    </div>

    @if (session('success'))
      <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    @if ($products->isEmpty())
      <p style="color:#64748b;">Nenhum produto criado ainda.</p>
    @else
      <div class="plans-table" style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;background:#fff;border-radius:0.75rem;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.06);">
          <thead>
            <tr style="background:#f8fafc;">
              <th style="text-align:left;padding:0.75rem 1rem;border-bottom:1.5px solid #e2e8f0;">#</th>
              <th style="text-align:left;padding:0.75rem 1rem;border-bottom:1.5px solid #e2e8f0;">Nome</th>
              <th style="text-align:left;padding:0.75rem 1rem;border-bottom:1.5px solid #e2e8f0;">Categoria</th>
              <th style="text-align:right;padding:0.75rem 1rem;border-bottom:1.5px solid #e2e8f0;">Preço (Kz)</th>
              <th style="text-align:center;padding:0.75rem 1rem;border-bottom:1.5px solid #e2e8f0;">Stock</th>
              <th style="text-align:center;padding:0.75rem 1rem;border-bottom:1.5px solid #e2e8f0;">Ativo</th>
              <th style="padding:0.75rem 1rem;border-bottom:1.5px solid #e2e8f0;"></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($products as $product)
              <tr>
                <td style="padding:0.6rem 1rem;border-bottom:1px solid #f1f5f9;color:#94a3b8;">{{ $product->id }}</td>
                <td style="padding:0.6rem 1rem;border-bottom:1px solid #f1f5f9;font-weight:600;">{{ $product->name }}</td>
                <td style="padding:0.6rem 1rem;border-bottom:1px solid #f1f5f9;">{{ $product->category ?? '—' }}</td>
                <td style="padding:0.6rem 1rem;border-bottom:1px solid #f1f5f9;text-align:right;">{{ number_format($product->price_aoa, 0, ',', '.') }}</td>
                <td style="padding:0.6rem 1rem;border-bottom:1px solid #f1f5f9;text-align:center;">
                  <span style="color:{{ $product->stock > 0 ? '#16a34a' : '#ef4444' }};font-weight:700;">{{ $product->stock }}</span>
                </td>
                <td style="padding:0.6rem 1rem;border-bottom:1px solid #f1f5f9;text-align:center;">
                  {{ $product->active ? '✔' : '✗' }}
                </td>
                <td style="padding:0.6rem 1rem;border-bottom:1px solid #f1f5f9;">
                  <div style="display:flex;gap:0.5rem;">
                    <a href="{{ route('admin.equipment.products.edit', $product->id) }}" class="btn-modern" style="font-size:0.88rem;padding:0.4rem 0.9rem;">Editar</a>
                    <form method="POST" action="{{ route('admin.equipment.products.destroy', $product->id) }}"
                          onsubmit="return confirm('Eliminar o produto \'{{ addslashes($product->name) }}\'?');">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn-modern" style="font-size:0.88rem;padding:0.4rem 0.9rem;background:linear-gradient(90deg,#f87171,#ef4444);">Eliminar</button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</section>
@endsection
