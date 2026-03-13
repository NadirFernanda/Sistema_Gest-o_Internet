@extends('layouts.app')

@section('title', 'Agendar Instalação — AngolaWiFi')

@section('content')
<style>
.ai-page{background:#f4f6f9;min-height:70vh;padding:3rem 0 5rem;font-family:Inter,system-ui,sans-serif;}
.ai-wrap{max-width:560px;margin:0 auto;padding:0 1.25rem;}
.ai-card{background:#fff;border:1px solid #dde2ea;border-radius:14px;padding:2rem 2rem 2.5rem;box-shadow:0 2px 10px rgba(0,0,0,.06);}
.ai-card h1{font-size:1.45rem;font-weight:800;color:#1a202c;margin:0 0 .3rem;letter-spacing:-.02em;}
.ai-card .ai-sub{font-size:.875rem;color:#64748b;margin:0 0 1.75rem;}
.ai-ok{background:#f0fdf4;border:1px solid #86efac;border-left:4px solid #16a34a;color:#166534;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1.25rem;}
.ai-err{background:#fef2f2;border:1px solid #fecaca;border-left:4px solid #dc2626;color:#7f1d1d;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1.25rem;}
.ai-err ul{margin:.4rem 0 0 1.1rem;padding:0;}
.ai-err li{margin:.15rem 0;}
.ai-row{margin-bottom:1.1rem;}
.ai-row label{display:block;font-size:.82rem;font-weight:700;color:#374151;margin-bottom:.35rem;}
.ai-row input,.ai-row textarea,.ai-row select{width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:8px;font-size:.9rem;font-family:inherit;color:#1a202c;background:#fff;transition:border-color .15s;outline:none;}
.ai-row input:focus,.ai-row textarea:focus,.ai-row select:focus{border-color:#f7b500;box-shadow:0 0 0 3px rgba(247,181,0,.15);}
.ai-row textarea{resize:vertical;min-height:100px;}
.ai-types{display:flex;gap:.6rem;flex-wrap:wrap;}
.ai-type{flex:1;min-width:110px;}
.ai-type input[type=radio]{display:none;}
.ai-type label{display:flex;flex-direction:column;align-items:center;gap:.35rem;padding:.75rem .5rem;border:2px solid #dde2ea;border-radius:10px;cursor:pointer;font-size:.82rem;font-weight:600;color:#374151;transition:border-color .15s,background .15s;text-align:center;}
.ai-type label .ai-type-icon{font-size:1.5rem;line-height:1;}
.ai-type input[type=radio]:checked + label{border-color:#f7b500;background:#fffdf0;color:#1a202c;}
.ai-type label:hover{border-color:#f7b500;}
.ai-btn{width:100%;padding:.7rem 1rem;background:#f7b500;color:#1a202c;border:none;border-radius:9px;font-size:1rem;font-weight:800;cursor:pointer;font-family:inherit;transition:filter .15s;margin-top:.5rem;}
.ai-btn:hover{filter:brightness(.95);}
.ai-back{display:inline-block;margin-bottom:1.25rem;font-size:.83rem;font-weight:600;color:#64748b;text-decoration:none;}
.ai-back:hover{color:#1a202c;}
</style>

<div class="ai-page">
  <div class="ai-wrap">

    <a href="/" class="ai-back">&larr; Voltar à loja</a>

    <div class="ai-card">
      <h1>Agendar Instalação</h1>
      <p class="ai-sub">Deixe os seus dados e a nossa equipa entrará em contacto para agendar a instalação.</p>

      @if(session('success'))
        <div class="ai-ok">{{ session('success') }}</div>
      @endif

      @if($errors->any())
        <div class="ai-err">
          <strong>Por favor corrija os erros abaixo:</strong>
          <ul>
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('appointment.store') }}" id="appt-form">
        @csrf

        {{-- Tipo de instalação --}}
        <div class="ai-row">
          <label>Tipo de instalação *</label>
          <div class="ai-types">
            <div class="ai-type">
              <input type="radio" id="type_familia" name="type" value="familia"
                {{ old('type', 'familia') === 'familia' ? 'checked' : '' }}>
              <label for="type_familia">
                <span class="ai-type-icon">🏠</span>
                Família
              </label>
            </div>
            <div class="ai-type">
              <input type="radio" id="type_empresa" name="type" value="empresa"
                {{ old('type') === 'empresa' ? 'checked' : '' }}>
              <label for="type_empresa">
                <span class="ai-type-icon">🏢</span>
                Empresa
              </label>
            </div>
            <div class="ai-type">
              <input type="radio" id="type_instituicao" name="type" value="instituicao"
                {{ old('type') === 'instituicao' ? 'checked' : '' }}>
              <label for="type_instituicao">
                <span class="ai-type-icon">🏛️</span>
                Instituição
              </label>
            </div>
          </div>
        </div>

        {{-- Nome --}}
        <div class="ai-row">
          <label for="appt_name">Nome completo *</label>
          <input type="text" id="appt_name" name="name"
            value="{{ old('name') }}" required
            placeholder="Ex: João Silva" autocomplete="name">
        </div>

        {{-- Telefone --}}
        <div class="ai-row">
          <label for="appt_phone">Telefone / WhatsApp *</label>
          <input type="tel" id="appt_phone" name="phone"
            value="{{ old('phone') }}" required
            placeholder="9XX XXX XXX" autocomplete="tel">
        </div>

        {{-- Mensagem --}}
        <div class="ai-row">
          <label for="appt_message">Mensagem</label>
          <textarea id="appt_message" name="message"
            placeholder="Ex: Quero instalar WiFi em casa, moro em Luanda…">{{ old('message') }}</textarea>
        </div>

        <button type="submit" class="ai-btn">Enviar pedido</button>
      </form>
    </div>

  </div>
</div>

@push('scripts')
<script>
(function () {
  var defaultMessages = {
    familia:    'Olá, gostaria de agendar a instalação do WiFi para uso residencial (família).',
    empresa:    'Olá, gostaria de agendar a instalação do WiFi para a minha empresa.',
    instituicao:'Olá, gostaria de agendar a instalação do WiFi para a nossa instituição.',
  };

  var radios  = document.querySelectorAll('input[name="type"]');
  var msgArea = document.getElementById('appt_message');

  // Only auto-fill if the textarea is empty or still has a default message
  var isDefault = false;

  function setDefault(type) {
    var msg = defaultMessages[type] || '';
    var current = msgArea.value.trim();
    if (!current || isDefault) {
      msgArea.value = msg;
      isDefault = true;
    }
  }

  // Set initial default based on currently checked radio
  radios.forEach(function (r) {
    if (r.checked) setDefault(r.value);
    r.addEventListener('change', function () {
      setDefault(r.value);
    });
  });

  // If user edits the textarea manually, stop overwriting
  msgArea.addEventListener('input', function () {
    isDefault = false;
  });
})();
</script>
@endpush
@endsection
