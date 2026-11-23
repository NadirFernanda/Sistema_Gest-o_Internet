@extends('layouts.app')

@push('styles')
<style>
.hs-page{background:#f8fafc;min-height:75vh;padding:2rem 1rem 5rem}.hs-wrap{max-width:1050px;margin:auto}.hs-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem}.hs-head h1{font-size:1.8rem;color:#0f172a;margin:0}.hs-btn{display:inline-block;background:#f7b500;color:#1a202c;padding:.65rem 1rem;border-radius:.6rem;font-weight:700;text-decoration:none;border:0;cursor:pointer}.hs-btn:hover{background:#e0a800}.hs-list{display:grid;gap:1rem}.hs-card{display:grid;grid-template-columns:220px 1fr;gap:1rem;background:white;border:1px solid #e2e8f0;border-radius:.8rem;padding:1rem;box-shadow:0 2px 8px #0000000a}.hs-card img{width:100%;height:145px;object-fit:cover;border-radius:.5rem}.hs-card h2{font-size:1.2rem;margin:0 0 .4rem;color:#0f172a}.hs-card p{color:#475569;margin:.35rem 0}.hs-actions{display:flex;align-items:center;gap:.8rem;margin-top:.8rem;flex-wrap:wrap}.hs-actions button{background:transparent;border:0;color:#b91c1c;text-decoration:underline;cursor:pointer;font:inherit}.hs-muted{color:#64748b}.hs-success{background:#dcfce7;color:#166534;padding:.8rem 1rem;border-radius:.5rem;margin-bottom:1rem}@media(max-width:620px){.hs-card{grid-template-columns:1fr}.hs-card img{height:190px}}
</style>
@endpush

@section('content')
<section class="hs-page"><div class="hs-wrap">
  <div class="hs-head"><div><a href="{{ route('admin.dashboard') }}">← Painel administrativo</a><h1>Carrossel da página inicial</h1><p class="hs-muted">Edite imagens, textos, botões, ordem e visibilidade sem alterar o código.</p></div><a class="hs-btn" href="{{ route('admin.hero-slides.create') }}">+ Adicionar slide</a></div>
  @if(session('success'))<div class="hs-success" role="status">{{ session('success') }}</div>@endif
  @if($slides->isEmpty())<p class="hs-muted">Ainda não há slides. Adicione o primeiro para mostrar o carrossel na página inicial.</p>@endif
  <div class="hs-list">
  @foreach($slides as $slide)
    <article class="hs-card">
      <img src="{{ $slide->image_url }}" alt="Pré-visualização de {{ $slide->title }}">
      <div><h2>{{ $slide->title }} {{ $slide->highlighted_title }}</h2><p>{{ $slide->eyebrow }}</p><p>{{ \Illuminate\Support\Str::limit($slide->description, 140) }}</p><p class="hs-muted">Ordem: {{ $slide->sort_order }} · {{ $slide->active ? 'Publicado' : 'Oculto' }}</p><div class="hs-actions"><a href="{{ route('admin.hero-slides.edit', $slide) }}">Editar</a><form method="POST" action="{{ route('admin.hero-slides.destroy', $slide) }}" onsubmit="return confirm('Remover este slide?')">@csrf @method('DELETE')<button type="submit">Remover</button></form></div></div>
    </article>
  @endforeach
  </div>
</div></section>
@endsection
