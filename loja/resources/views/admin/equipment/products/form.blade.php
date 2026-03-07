@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="{{ $product->exists ? 'Editar produto' : 'Criar produto' }}">
  <div class="container" style="max-width:680px;">
    <a href="{{ route('admin.equipment.products.index') }}" class="store-link" style="font-size:0.95rem;">&larr; Voltar aos produtos</a>

    <h2 style="margin-top:1rem;">{{ $product->exists ? 'Editar Produto' : 'Novo Produto' }}</h2>

    @if ($errors->any())
      <div class="alert alert-error" role="alert">
        <ul style="margin:0;padding-left:1.2rem;">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST"
          action="{{ $product->exists ? route('admin.equipment.products.update', $product->id) : route('admin.equipment.products.store') }}"
          class="plan-card-modern" style="max-width:100%;margin-top:1.5rem;">
      @csrf
      @if ($product->exists) @method('PUT') @endif

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div style="grid-column:1/-1;">
          <label for="name" style="display:block;font-weight:600;margin-bottom:0.3rem;">Nome do produto *</label>
          <input id="name" type="text" name="name" value="{{ old('name', $product->name) }}"
                 required style="width:100%;padding:0.6rem;border:1.5px solid #e2e8f0;border-radius:0.5rem;font-size:1rem;">
        </div>

        <div style="grid-column:1/-1;">
          <label for="slug" style="display:block;font-weight:600;margin-bottom:0.3rem;">Slug (URL) — gerado automaticamente se vazio</label>
          <input id="slug" type="text" name="slug" value="{{ old('slug', $product->slug) }}"
                 placeholder="ex: router-tplink-wr940n"
                 style="width:100%;padding:0.6rem;border:1.5px solid #e2e8f0;border-radius:0.5rem;font-size:1rem;">
        </div>

        <div>
          <label for="price_aoa" style="display:block;font-weight:600;margin-bottom:0.3rem;">Preço (Kz) *</label>
          <input id="price_aoa" type="number" name="price_aoa" min="1"
                 value="{{ old('price_aoa', $product->price_aoa) }}"
                 required style="width:100%;padding:0.6rem;border:1.5px solid #e2e8f0;border-radius:0.5rem;font-size:1rem;">
        </div>

        <div>
          <label for="stock" style="display:block;font-weight:600;margin-bottom:0.3rem;">Stock *</label>
          <input id="stock" type="number" name="stock" min="0"
                 value="{{ old('stock', $product->stock ?? 0) }}"
                 required style="width:100%;padding:0.6rem;border:1.5px solid #e2e8f0;border-radius:0.5rem;font-size:1rem;">
        </div>

        <div style="grid-column:1/-1;">
          <label for="category" style="display:block;font-weight:600;margin-bottom:0.3rem;">Categoria</label>
          <input id="category" type="text" name="category" value="{{ old('category', $product->category) }}"
                 placeholder="ex: Routers, Antenas, Repetidores…"
                 style="width:100%;padding:0.6rem;border:1.5px solid #e2e8f0;border-radius:0.5rem;font-size:1rem;">
        </div>

        <div style="grid-column:1/-1;">
          <label for="description" style="display:block;font-weight:600;margin-bottom:0.3rem;">Descrição</label>
          <textarea id="description" name="description" rows="4"
                    style="width:100%;padding:0.6rem;border:1.5px solid #e2e8f0;border-radius:0.5rem;font-size:1rem;resize:vertical;">{{ old('description', $product->description) }}</textarea>
        </div>

        <div style="grid-column:1/-1;">
          <label for="image_path" style="display:block;font-weight:600;margin-bottom:0.3rem;">Caminho da imagem (relativo a /public)</label>
          <input id="image_path" type="text" name="image_path" value="{{ old('image_path', $product->image_path) }}"
                 placeholder="ex: img/products/router-tplink.jpg"
                 style="width:100%;padding:0.6rem;border:1.5px solid #e2e8f0;border-radius:0.5rem;font-size:1rem;">
        </div>

        <div style="grid-column:1/-1;display:flex;align-items:center;gap:0.6rem;">
          <input id="active" type="checkbox" name="active" value="1"
                 {{ old('active', $product->active ?? true) ? 'checked' : '' }}
                 style="width:18px;height:18px;cursor:pointer;">
          <label for="active" style="font-weight:600;cursor:pointer;">Produto ativo (visível na loja)</label>
        </div>
      </div>

      <div style="display:flex;gap:0.75rem;justify-content:flex-end;margin-top:1.5rem;">
        <a href="{{ route('admin.equipment.products.index') }}" class="btn-modern"
           style="background:linear-gradient(90deg,#94a3b8,#64748b);">Cancelar</a>
        <button type="submit" class="btn-modern">
          {{ $product->exists ? 'Guardar alterações' : 'Criar produto' }}
        </button>
      </div>
    </form>
  </div>
</section>
@endsection
