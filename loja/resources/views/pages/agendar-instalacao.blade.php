@extends('layouts.app')

@section('seo_title', 'Agendar Instalação de Internet | AngolaWiFi')
@section('seo_description', 'Agende a instalação do seu plano de internet AngolaWiFi. Técnicos especializados, equipamentos incluídos e activação em até 48h. Disponível para planos familiares, empresariais e institucionais.')

@section('content')
<style>
.ai-page{background:#f4f6f9;min-height:70vh;padding:3rem 0 5rem;font-family:Inter,system-ui,sans-serif;}
.ai-wrap{max-width:580px;margin:0 auto;padding:0 1.25rem;}
.ai-back{display:inline-block;margin-bottom:1.25rem;font-size:.83rem;font-weight:600;color:#64748b;text-decoration:none;}
.ai-back:hover{color:#1a202c;}

/* Section headers */
.ai-section{background:#fff;border:1px solid #dde2ea;border-radius:14px;padding:1.6rem 2rem 2rem;box-shadow:0 2px 10px rgba(0,0,0,.06);margin-bottom:1rem;}
.ai-section-header{display:flex;align-items:center;gap:.75rem;margin-bottom:1.4rem;}
.ai-section-badge{width:2rem;height:2rem;background:#f7b500;color:#1a202c;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:900;flex-shrink:0;}
.ai-section-title{font-size:1.1rem;font-weight:800;color:#1a202c;margin:0;letter-spacing:-.01em;}
.ai-section-sub{font-size:.78rem;color:#64748b;margin:.1rem 0 0;}

.ai-ok{background:#f0fdf4;border:1px solid #86efac;border-left:4px solid #16a34a;color:#166534;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1.25rem;}
.ai-err{background:#fef2f2;border:1px solid #fecaca;border-left:4px solid #dc2626;color:#7f1d1d;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ai-err ul{margin:.4rem 0 0 1.1rem;padding:0;}
.ai-err li{margin:.15rem 0;}

.ai-row{margin-bottom:1.05rem;}
.ai-row label{display:block;font-size:.82rem;font-weight:700;color:#374151;margin-bottom:.35rem;}
.ai-row input,.ai-row textarea,.ai-row select{width:100%;box-sizing:border-box;padding:.65rem .85rem;border:1px solid #d1d5db;border-radius:8px;font-size:.9rem;font-family:inherit;color:#1a202c;background:#fff;transition:border-color .15s;outline:none;}
.ai-row input:focus,.ai-row textarea:focus,.ai-row select:focus{border-color:#f7b500;box-shadow:0 0 0 3px rgba(247,181,0,.15);}
.ai-row textarea{resize:vertical;min-height:90px;}
.ai-opt{font-size:.75rem;color:#94a3b8;font-weight:400;margin-left:.3rem;}
.ai-row-2{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}
@media(max-width:460px){.ai-row-2{grid-template-columns:1fr;}}

.ai-types{display:flex;gap:.6rem;flex-wrap:wrap;}
.ai-type{flex:1;min-width:110px;}
.ai-type input[type=radio]{display:none;}
.ai-type label{display:flex;flex-direction:column;align-items:center;gap:.35rem;padding:.75rem .5rem;border:2px solid #dde2ea;border-radius:10px;cursor:pointer;font-size:.82rem;font-weight:600;color:#374151;transition:border-color .15s,background .15s;text-align:center;}
.ai-type label .ai-type-icon{font-size:1.5rem;line-height:1;}
.ai-type input[type=radio]:checked + label{border-color:#f7b500;background:#fffdf0;color:#1a202c;}
.ai-type label:hover{border-color:#f7b500;}

.ai-btn{width:100%;padding:.75rem 1rem;background:#f7b500;color:#1a202c;border:none;border-radius:9px;font-size:1rem;font-weight:800;cursor:pointer;font-family:inherit;transition:filter .15s;margin-top:.25rem;}
.ai-btn:hover{filter:brightness(.95);}

.ai-info{background:rgba(247,181,0,.08);border:1px solid rgba(247,181,0,.3);border-radius:8px;padding:.65rem .85rem;font-size:.79rem;color:#6b5500;line-height:1.5;margin-bottom:1.1rem;}
</style>

<div class="ai-page">
  <div class="ai-wrap">

    <a href="/" class="ai-back">&larr; Voltar à loja</a>

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

      {{-- SECÇÃO 1: Pré-Cadastro --}}
      <div class="ai-section">
        <div class="ai-section-header">
          <div class="ai-section-badge">1</div>
          <div>
            <p class="ai-section-title">Pré-Cadastro</p>
            <p class="ai-section-sub">Os seus dados de identificação para o registo</p>
          </div>
        </div>

        <div class="ai-row">
          <label for="appt_name">Nome completo *</label>
          <input type="text" id="appt_name" name="name"
            value="{{ old('name') }}" required
            placeholder="Ex: João Manuel Silva" autocomplete="name">
        </div>

        <div class="ai-row-2">
          <div class="ai-row" style="margin-bottom:0">
            <label for="appt_phone">Telefone / WhatsApp *</label>
            <input type="tel" id="appt_phone" name="phone"
              value="{{ old('phone') }}" required
              placeholder="9XX XXX XXX" autocomplete="tel">
          </div>
          <div class="ai-row" style="margin-bottom:0">
            <label for="appt_email">E-mail *</label>
            <input type="email" id="appt_email" name="email"
              value="{{ old('email') }}" required
              placeholder="exemplo@gmail.com" autocomplete="email">
          </div>
        </div>

        <div class="ai-row" style="margin-top:1.05rem">
          <label for="appt_nif">NIF / BI <span class="ai-opt">(opcional)</span></label>
          <input type="text" id="appt_nif" name="nif"
            value="{{ old('nif') }}"
            placeholder="Ex: 004567890LA041">
        </div>
      </div>

      {{-- SECÇÃO 2: Detalhes da instalação --}}
      <div class="ai-section">
        <div class="ai-section-header">
          <div class="ai-section-badge">2</div>
          <div>
            <p class="ai-section-title">Detalhes da instalação</p>
            <p class="ai-section-sub">Onde e para que fim pretende instalar o serviço</p>
          </div>
        </div>

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

        <div class="ai-row">
          <label for="appt_morada">Morada / Localização <span class="ai-opt">(opcional)</span></label>
          <input type="text" id="appt_morada" name="morada"
            value="{{ old('morada') }}"
            placeholder="Ex: Luanda, Talatona, Rua das Acácias nº 12" autocomplete="street-address">
        </div>

        <div class="ai-row">
          <label for="appt_message">Mensagem / Observações <span class="ai-opt">(opcional)</span></label>
          <textarea id="appt_message" name="message"
            placeholder="Ex: Prédio de 3 andares, preciso de cobertura em todos os pisos…">{{ old('message') }}</textarea>
        </div>

        <div class="ai-info">
          Após submeter o pedido a nossa equipa entrará em contacto em até <strong>48 horas</strong> para confirmar a data e hora da instalação.
        </div>

        <button type="submit" class="ai-btn">Enviar pedido de instalação</button>
      </div>

    </form>
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
  var isDefault = false;

  function setDefault(type) {
    var msg = defaultMessages[type] || '';
    var current = msgArea.value.trim();
    if (!current || isDefault) { msgArea.value = msg; isDefault = true; }
  }

  radios.forEach(function (r) {
    if (r.checked) setDefault(r.value);
    r.addEventListener('change', function () { setDefault(r.value); });
  });

  msgArea.addEventListener('input', function () { isDefault = false; });
})();
</script>
@endpush
@endsection
