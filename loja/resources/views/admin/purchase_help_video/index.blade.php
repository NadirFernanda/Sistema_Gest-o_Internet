@extends('layouts.app')

@section('content')
<style>
.phv-page { min-height: 60vh; padding: 2rem 1rem 4rem; background: #f4f6f9; color: #1a202c; font-family: Inter, system-ui, sans-serif; }
.phv-wrap { max-width: 760px; margin: 0 auto; }
.phv-head { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
.phv-head h1 { margin: 0 0 .25rem; font-size: 1.35rem; font-weight: 800; }
.phv-head p { margin: 0; color: #64748b; font-size: .85rem; }
.phv-card { margin-bottom: 1.25rem; overflow: hidden; border: 1px solid #dde2ea; border-radius: 14px; background: #fff; }
.phv-card h2 { margin: 0; padding: 1rem 1.25rem; background: #1a202c; color: #fff; font-size: 1rem; }
.phv-body { padding: 1.25rem; }
.phv-notice { margin-bottom: 1rem; padding: .8rem 1rem; border: 1px solid #fde68a; border-radius: 8px; background: #fffbeb; color: #78350f; font-size: .84rem; line-height: 1.55; }
.phv-status, .phv-error { margin-bottom: 1rem; padding: .75rem 1rem; border-radius: 8px; font-size: .85rem; }
.phv-status { border: 1px solid #86efac; background: #f0fdf4; color: #166534; }
.phv-error { border: 1px solid #fecaca; background: #fef2f2; color: #991b1b; }
.phv-video { display: block; width: 100%; max-height: 420px; margin-bottom: 1rem; border-radius: 10px; background: #0f172a; }
.phv-label { display: block; margin-bottom: .4rem; font-size: .88rem; font-weight: 700; }
.phv-input { display: block; width: 100%; padding: .7rem; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; color: #1a202c; }
.phv-help { margin: .45rem 0 1rem; color: #64748b; font-size: .78rem; line-height: 1.5; }
.phv-actions { display: flex; flex-wrap: wrap; align-items: center; gap: .75rem; margin-top: 1rem; }
.phv-button { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: .65rem 1rem; border: 0; border-radius: 8px; background: #f7b500; color: #1a202c; font: inherit; font-size: .86rem; font-weight: 800; text-decoration: none; cursor: pointer; }
.phv-button--danger { background: #fee2e2; color: #991b1b; }
.phv-back { color: #475569; font-size: .84rem; text-decoration: none; }
@media (max-width: 520px) { .phv-page { padding: 1.25rem .75rem 3rem; } .phv-head { align-items: flex-start; flex-direction: column; } .phv-body { padding: 1rem; } }
</style>

<main class="phv-page">
  <div class="phv-wrap">
    <header class="phv-head">
      <div>
        <h1>Vídeo de ajuda para comprar</h1>
        <p>Carregue e faça a gestão do vídeo exibido junto aos planos e no checkout.</p>
      </div>
      <a class="phv-back" href="{{ route('admin.dashboard') }}">← Painel administrativo</a>
    </header>

    @if(session('status'))
      <div class="phv-status" role="status">{{ session('status') }}</div>
    @endif

    <section class="phv-card">
      <h2>Vídeo actual</h2>
      <div class="phv-body">
        @if($videoUrl)
          <video class="phv-video" controls preload="metadata" playsinline>
            <source src="{{ $videoUrl }}">
            O seu navegador não suporta a reprodução deste vídeo.
          </video>
          <form method="POST" action="{{ route('admin.purchase-help-video.destroy') }}">
            @csrf
            <button class="phv-button phv-button--danger" type="submit">Remover vídeo</button>
          </form>
        @else
          <p style="margin:0;color:#64748b;font-size:.88rem;">Ainda não foi carregado nenhum vídeo. A ajuda de compra continua a apresentar o passo a passo e o contacto de suporte.</p>
        @endif
      </div>
    </section>

    <section class="phv-card">
      <h2>{{ $videoUrl ? 'Substituir vídeo' : 'Carregar vídeo' }}</h2>
      <div class="phv-body">
        <div class="phv-notice">Formatos aceites: MP4 ou WebM. Tamanho máximo: 500 MB. O novo vídeo passa a ser apresentado aos clientes assim que for guardado.</div>
        @if($errors->has('video'))
          <div class="phv-error" role="alert">{{ $errors->first('video') }}</div>
        @endif
        <form method="POST" action="{{ route('admin.purchase-help-video.store') }}" enctype="multipart/form-data">
          @csrf
          <label class="phv-label" for="video">Ficheiro de vídeo</label>
          <input class="phv-input" id="video" name="video" type="file" accept="video/mp4,video/webm,.mp4,.webm" required>
          <p class="phv-help">Prefira um vídeo curto, com legendas ou instruções claras, e mantenha a ligação acessível para quem usa dados móveis.</p>
          <div class="phv-actions">
            <button class="phv-button" type="submit">Guardar vídeo</button>
          </div>
        </form>
      </div>
    </section>
  </div>
</main>
@endsection
