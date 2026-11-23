@extends('layouts.app')

@push('styles')
<style>
.hs-form-page{background:#f8fafc;min-height:75vh;padding:2rem 1rem 5rem}.hs-form-wrap{max-width:780px;margin:auto}.hs-form-wrap h1{font-size:1.8rem;color:#0f172a}.hs-form{background:white;border:1px solid #e2e8f0;border-radius:.8rem;padding:1.5rem;display:grid;gap:1.1rem}.hs-form label{display:block;font-weight:700;color:#334155;margin-bottom:.3rem}.hs-form input:not([type=checkbox]),.hs-form textarea{width:100%;box-sizing:border-box;padding:.7rem;border:1px solid #cbd5e1;border-radius:.5rem;font:inherit}.hs-form textarea{min-height:100px}.hs-form small{display:block;color:#64748b;margin-top:.3rem}.hs-form .hs-error{color:#b91c1c}.hs-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}.hs-form img{max-width:360px;width:100%;border-radius:.5rem}.hs-form button{background:#f7b500;color:#1a202c;padding:.7rem 1.2rem;border:0;border-radius:.6rem;font-weight:700;cursor:pointer}.hs-check{display:flex;gap:.5rem;align-items:center}.hs-check label{margin:0}@media(max-width:620px){.hs-form-grid{grid-template-columns:1fr}.hs-form{padding:1rem}}
</style>
@endpush

@section('content')
@php($editing = $slide->exists)
<section class="hs-form-page"><div class="hs-form-wrap">
  <a href="{{ route('admin.hero-slides.index') }}">← Voltar ao carrossel</a>
  <h1>{{ $editing ? 'Editar slide' : 'Adicionar slide' }}</h1>
  <form class="hs-form" method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.hero-slides.update', $slide) : route('admin.hero-slides.store') }}">
    @csrf @if($editing) @method('PUT') @endif
    <div><label for="image">Imagem {{ $editing ? '(deixe vazio para manter a atual)' : '*' }}</label>@if($editing)<img src="{{ $slide->image_url }}" alt="Imagem atual do slide">@endif<input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" {{ $editing ? '' : 'required' }}><small>JPG, PNG ou WebP, até 5 MB. Recomenda-se uma imagem horizontal com pelo menos 1600 × 900 px.</small>@error('image')<small class="hs-error">{{ $message }}</small>@enderror</div>
    <div><label for="eyebrow">Etiqueta acima do título</label><input id="eyebrow" name="eyebrow" maxlength="100" value="{{ old('eyebrow', $slide->eyebrow) }}">@error('eyebrow')<small class="hs-error">{{ $message }}</small>@enderror</div>
    <div class="hs-form-grid"><div><label for="title">Título *</label><input id="title" name="title" required maxlength="150" value="{{ old('title', $slide->title) }}">@error('title')<small class="hs-error">{{ $message }}</small>@enderror</div><div><label for="highlighted_title">Segunda linha em destaque</label><input id="highlighted_title" name="highlighted_title" maxlength="150" value="{{ old('highlighted_title', $slide->highlighted_title) }}">@error('highlighted_title')<small class="hs-error">{{ $message }}</small>@enderror</div></div>
    <div><label for="description">Descrição</label><textarea id="description" name="description" maxlength="1000">{{ old('description', $slide->description) }}</textarea>@error('description')<small class="hs-error">{{ $message }}</small>@enderror</div>
    <div class="hs-form-grid"><div><label for="primary_label">Texto do botão principal</label><input id="primary_label" name="primary_label" maxlength="80" value="{{ old('primary_label', $slide->primary_label) }}">@error('primary_label')<small class="hs-error">{{ $message }}</small>@enderror</div><div><label for="primary_url">Ligação do botão principal</label><input id="primary_url" name="primary_url" maxlength="2048" value="{{ old('primary_url', $slide->primary_url) }}" placeholder="#planos ou /como-comprar">@error('primary_url')<small class="hs-error">{{ $message }}</small>@enderror</div></div>
    <div class="hs-form-grid"><div><label for="secondary_label">Texto do botão secundário</label><input id="secondary_label" name="secondary_label" maxlength="80" value="{{ old('secondary_label', $slide->secondary_label) }}">@error('secondary_label')<small class="hs-error">{{ $message }}</small>@enderror</div><div><label for="secondary_url">Ligação do botão secundário</label><input id="secondary_url" name="secondary_url" maxlength="2048" value="{{ old('secondary_url', $slide->secondary_url) }}" placeholder="/como-comprar">@error('secondary_url')<small class="hs-error">{{ $message }}</small>@enderror</div></div>
    <div class="hs-form-grid"><div><label for="sort_order">Ordem de apresentação *</label><input id="sort_order" type="number" name="sort_order" min="0" max="99999" required value="{{ old('sort_order', $slide->sort_order) }}"><small>Números menores aparecem primeiro.</small>@error('sort_order')<small class="hs-error">{{ $message }}</small>@enderror</div><div class="hs-check"><input type="hidden" name="active" value="0"><input id="active" type="checkbox" name="active" value="1" {{ old('active', $slide->active) ? 'checked' : '' }}><label for="active">Publicado na página inicial</label></div></div>
    <div><button type="submit">Guardar slide</button></div>
  </form>
</div></section>
@endsection
