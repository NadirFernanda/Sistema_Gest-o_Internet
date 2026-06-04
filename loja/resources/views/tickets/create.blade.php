@extends('layouts.app')
@section('title', 'Abrir Ticket de Suporte — AngolaWiFi')

@push('styles')
<style>
.tk{font-family:Inter,system-ui,sans-serif;background:#f4f6f9;min-height:70vh;padding:3rem 0 5rem;}
.tk-wrap{max-width:660px;margin:0 auto;padding:0 1.25rem;}
.tk-head{text-align:center;margin-bottom:2.5rem;}
.tk-head h1{font-size:1.75rem;font-weight:800;color:#1a202c;margin:0 0 .5rem;letter-spacing:-.03em;}
.tk-head p{color:#64748b;font-size:.95rem;margin:0;}
.tk-card{background:#fff;border:1px solid #dde2ea;border-radius:14px;padding:2rem;}
.tk-label{display:block;font-size:.8rem;font-weight:700;color:#374151;margin-bottom:.35rem;}
.tk-input{width:100%;box-sizing:border-box;padding:.6rem .85rem;border:1.5px solid #dde2ea;border-radius:9px;font-size:.9rem;font-family:inherit;color:#1a202c;background:#f8fafc;outline:none;transition:border-color .15s;}
.tk-input:focus{border-color:#f7b500;background:#fff;}
.tk-err{font-size:.78rem;color:#dc2626;margin-top:.25rem;}
.tk-grid{display:grid;grid-template-columns:1fr 1fr;gap:.9rem;}
@media(max-width:540px){.tk-grid{grid-template-columns:1fr;}}
.tk-fg{display:flex;flex-direction:column;margin-bottom:.9rem;}
.tk-btn{display:inline-flex;align-items:center;justify-content:center;width:100%;padding:.75rem;background:#f7b500;color:#1a202c;font-weight:800;font-size:1rem;border:none;border-radius:10px;cursor:pointer;font-family:inherit;margin-top:.5rem;transition:filter .15s;}
.tk-btn:hover{filter:brightness(.95);}
.tk-info{background:#fffbeb;border:1px solid #fde68a;border-left:4px solid #f7b500;border-radius:0 8px 8px 0;padding:.75rem 1rem;font-size:.85rem;color:#78350f;margin-bottom:1.5rem;line-height:1.6;}
</style>
@endpush

@section('content')
<div class="tk"><div class="tk-wrap">

  <div class="tk-head">
    <h1>Suporte ao Cliente</h1>
    <p>Descreva o seu problema e a nossa equipa responderá em breve.</p>
  </div>

  @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #86efac;border-left:4px solid #16a34a;color:#166534;padding:.85rem 1rem;border-radius:10px;margin-bottom:1.5rem;font-size:.9rem;">
      {{ session('success') }}
    </div>
  @endif

  <div class="tk-info">
    Pode acompanhar o estado do seu ticket a qualquer momento através do <strong>link único</strong> que lhe será mostrado após a submissão. Não é necessário criar conta.
  </div>

  <div class="tk-card">
    <form method="POST" action="{{ route('tickets.store') }}">
      @csrf

      <div class="tk-grid">
        <div class="tk-fg">
          <label class="tk-label" for="name">Nome completo *</label>
          <input id="name" name="name" class="tk-input @error('name') is-invalid @enderror"
                 value="{{ old('name') }}" placeholder="O seu nome" required>
          @error('name')<p class="tk-err">{{ $message }}</p>@enderror
        </div>
        <div class="tk-fg">
          <label class="tk-label" for="phone">Telefone</label>
          <input id="phone" name="phone" class="tk-input" value="{{ old('phone') }}" placeholder="9XXXXXXXX">
        </div>
      </div>

      <div class="tk-fg">
        <label class="tk-label" for="email">E-mail (opcional, para receber resposta)</label>
        <input id="email" name="email" type="email" class="tk-input @error('email') is-invalid @enderror"
               value="{{ old('email') }}" placeholder="email@exemplo.com">
        @error('email')<p class="tk-err">{{ $message }}</p>@enderror
      </div>

      <div class="tk-fg">
        <label class="tk-label" for="category">Categoria *</label>
        <select id="category" name="category" class="tk-input" required>
          <option value="">Seleccione a categoria</option>
          @foreach(\App\Models\Ticket::CATEGORIES as $val => $lbl)
            <option value="{{ $val }}" @selected(old('category') === $val)>{{ $lbl }}</option>
          @endforeach
        </select>
        @error('category')<p class="tk-err">{{ $message }}</p>@enderror
      </div>

      <div class="tk-fg">
        <label class="tk-label" for="subject">Assunto *</label>
        <input id="subject" name="subject" class="tk-input @error('subject') is-invalid @enderror"
               value="{{ old('subject') }}" placeholder="Resumo em poucas palavras" required>
        @error('subject')<p class="tk-err">{{ $message }}</p>@enderror
      </div>

      <div class="tk-fg">
        <label class="tk-label" for="message">Mensagem *</label>
        <textarea id="message" name="message" rows="6"
                  class="tk-input @error('message') is-invalid @enderror"
                  placeholder="Descreva o seu problema com o máximo de detalhe possível..." required>{{ old('message') }}</textarea>
        @error('message')<p class="tk-err">{{ $message }}</p>@enderror
      </div>

      <button type="submit" class="tk-btn">Enviar ticket</button>
    </form>
  </div>

</div></div>
@endsection
