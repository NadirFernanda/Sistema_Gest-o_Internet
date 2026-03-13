@extends('layouts.app')

@section('title', "{{ $product->exists ? 'Editar Produto' : 'Novo Produto' }} &mdash; Admin")

@section('content')
<style>
:root{--a-bg:#f4f6f9;--a-surf:#fff;--a-border:#dde2ea;--a-text:#1a202c;--a-muted:#64748b;--a-faint:#9aa5b4;--a-brand:#f7b500;--a-green:#16a34a;--a-red:#dc2626;}
.ap{font-family:Inter,system-ui,sans-serif;background:var(--a-bg);min-height:60vh;padding:2rem 0 4rem;color:var(--a-text);}
.ap-wrap{max-width:700px;margin:0 auto;padding:0 1.5rem;}
.ap-topbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;}
.ap-topbar h1{font-size:1.35rem;font-weight:800;margin:0 0 .15rem;letter-spacing:-.02em;}
.ap-topbar .ap-sub{font-size:.78rem;color:var(--a-faint);}
.ap-back{font-size:.82rem;font-weight:600;color:var(--a-muted);text-decoration:none;padding:.4rem .85rem;border:1px solid var(--a-border);border-radius:7px;background:var(--a-surf);transition:background .15s;}
.ap-back:hover{background:var(--a-border);}
.ap-errs{background:#fef2f2;border:1px solid #fecaca;border-left:4px solid var(--a-red);color:#7f1d1d;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ap-errs ul{margin:.25rem 0 0;padding-left:1.2rem;}
.ap-card{background:var(--a-surf);border:1px solid var(--a-border);border-radius:11px;padding:1.5rem;}
.ap-grid{display:grid;grid-template-columns:1fr 1fr;gap:.85rem;}
@media(max-width:560px){.ap-grid{grid-template-columns:1fr}}
.ap-full{grid-column:1/-1;}
.ap-label{display:block;font-size:.77rem;font-weight:600;color:#374151;margin-bottom:.3rem;}
.ap-ctrl{width:100%;box-sizing:border-box;padding:.55rem .75rem;border:1.5px solid var(--a-border);border-radius:8px;font-size:.875rem;color:var(--a-text);background:#f8fafc;font-family:inherit;outline:none;transition:border-color .15s;}
.ap-ctrl:focus{border-color:var(--a-brand);background:#fff;}
.ap-hint{font-size:.77rem;color:var(--a-faint);margin-top:.2rem;}
.ap-err-inline{font-size:.8rem;color:var(--a-red);margin-top:.2rem;}
.ap-check-row{display:flex;align-items:center;gap:.55rem;cursor:pointer;}
.ap-check-row input[type=checkbox]{width:17px;height:17px;cursor:pointer;accent-color:var(--a-brand);}
.ap-check-row span{font-size:.875rem;font-weight:600;}
.ap-actions{display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--a-border);}
.ap-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.2rem;border-radius:8px;font-size:.875rem;font-weight:700;border:none;cursor:pointer;font-family:inherit;text-decoration:none;white-space:nowrap;transition:filter .15s;}
.ap-btn-primary{background:#f7b500;color:#1a202c;}.ap-btn-primary:hover{filter:brightness(.95);}
.ap-btn-cancel{background:#f1f5f9;color:var(--a-muted);}.ap-btn-cancel:hover{background:var(--a-border);}
</style>

<div class="ap"><div class="ap-wrap">

  <div class="ap-topbar">
    <div>
      <h1>{{ $product->exists ? 'Editar Produto' : 'Novo Produto' }}</h1>
      <p class="ap-sub">Admin &rsaquo; Equipamentos</p>
    </div>
    <a href="{{ route('admin.equipment.products.index') }}" class="ap-back">&larr; Produtos</a>
  </div>

  @if($errors->any())
    <div class="ap-errs">
      <ul>
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form method="POST"
        action="{{ $product->exists ? route('admin.equipment.products.update', $product->id) : route('admin.equipment.products.store') }}">
    @csrf
    @if($product->exists) @method('PUT') @endif

    <div class="ap-card">
      <div class="ap-grid">

        <div class="ap-full">
          <label class="ap-label" for="name">Nome do produto *</label>
          <input id="name" type="text" name="name" value="{{ old('name', $product->name) }}"
                 required class="ap-ctrl">
          @error('name')<p class="ap-err-inline">{{ $message }}</p>@enderror
        </div>

        <div class="ap-full">
          <label class="ap-label" for="slug">Slug (URL) &mdash; gerado automaticamente se vazio</label>
          <input id="slug" type="text" name="slug" value="{{ old('slug', $product->slug) }}"
                 placeholder="ex: router-tplink-wr940n" class="ap-ctrl">
          @error('slug')<p class="ap-err-inline">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="ap-label" for="price_aoa">Pre&ccedil;o (Kz) *</label>
          <input id="price_aoa" type="number" name="price_aoa" min="1"
                 value="{{ old('price_aoa', $product->price_aoa) }}"
                 required class="ap-ctrl">
          @error('price_aoa')<p class="ap-err-inline">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="ap-label" for="stock">Stock *</label>
          <input id="stock" type="number" name="stock" min="0"
                 value="{{ old('stock', $product->stock ?? 0) }}"
                 required class="ap-ctrl">
          @error('stock')<p class="ap-err-inline">{{ $message }}</p>@enderror
        </div>

        <div class="ap-full">
          <label class="ap-label" for="category">Categoria</label>
          <input id="category" type="text" name="category" value="{{ old('category', $product->category) }}"
                 placeholder="ex: Routers, Antenas, Repetidores&hellip;" class="ap-ctrl">
          @error('category')<p class="ap-err-inline">{{ $message }}</p>@enderror
        </div>

        <div class="ap-full">
          <label class="ap-label" for="description">Descri&ccedil;&atilde;o</label>
          <textarea id="description" name="description" rows="4"
                    class="ap-ctrl" style="resize:vertical;">{{ old('description', $product->description) }}</textarea>
          @error('description')<p class="ap-err-inline">{{ $message }}</p>@enderror
        </div>

        <div class="ap-full">
          <label class="ap-label" for="image_path">Caminho da imagem (relativo a /public)</label>
          <input id="image_path" type="text" name="image_path" value="{{ old('image_path', $product->image_path) }}"
                 placeholder="ex: img/products/router-tplink.jpg" class="ap-ctrl">
          @error('image_path')<p class="ap-err-inline">{{ $message }}</p>@enderror
        </div>

        <div class="ap-full">
          <label class="ap-check-row">
            <input type="checkbox" name="active" value="1"
                   {{ old('active', $product->active ?? true) ? 'checked' : '' }}>
            <span>Produto activo (vis&iacute;vel na loja)</span>
          </label>
        </div>

      </div>

      <div class="ap-actions">
        <a href="{{ route('admin.equipment.products.index') }}" class="ap-btn ap-btn-cancel">Cancelar</a>
        <button type="submit" class="ap-btn ap-btn-primary">
          {{ $product->exists ? 'Guardar altera&ccedil;&otilde;es' : 'Criar produto' }}
        </button>
      </div>
    </div>

  </form>

</div></div>
@endsection
