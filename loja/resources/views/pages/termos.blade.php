@extends('layouts.app')

@section('seo_title', 'Termos e Condições | AngolaWiFi')
@section('seo_description', 'Termos e Condições de Utilização da plataforma AngolaWiFi — venda de planos WiFi individuais, familiares e empresariais em Angola.')

@push('styles')
<style>
.tp-hero{background:var(--color-dark,#1a202c);color:#fff;padding:3.5rem 1.5rem 2.5rem;text-align:center;}
.tp-hero-eyebrow{display:inline-block;font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#f7b500;margin-bottom:.75rem;}
.tp-hero-title{font-size:2rem;font-weight:900;margin:0 0 .6rem;line-height:1.15;}
.tp-hero-sub{font-size:.95rem;color:#9ca3af;margin:0;}
.tp-body{max-width:780px;margin:0 auto;padding:3rem 1.5rem 5rem;}
.tp-toc{background:#fffbeb;border:1px solid #fde68a;border-left:4px solid #f7b500;border-radius:10px;padding:1.25rem 1.5rem;margin-bottom:2.5rem;}
.tp-toc-title{font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#78350f;margin:0 0 .75rem;}
.tp-toc ol{margin:0;padding-left:1.25rem;}
.tp-toc li{font-size:.875rem;line-height:1.8;color:#92400e;}
.tp-toc a{color:#92400e;text-decoration:none;font-weight:600;}
.tp-toc a:hover{text-decoration:underline;}
.tp-section{margin-bottom:2.5rem;}
.tp-section h2{font-size:1.15rem;font-weight:800;color:#1a202c;margin:0 0 .9rem;padding-bottom:.5rem;border-bottom:2px solid #f7b500;display:flex;align-items:center;gap:.5rem;}
.tp-section h2 .tp-num{display:inline-flex;align-items:center;justify-content:center;width:1.6rem;height:1.6rem;border-radius:50%;background:#f7b500;color:#1a202c;font-size:.75rem;font-weight:900;flex-shrink:0;}
.tp-section p{font-size:.92rem;line-height:1.75;color:#374151;margin:0 0 .85rem;}
.tp-section p:last-child{margin-bottom:0;}
.tp-section ul,
.tp-section ol{font-size:.92rem;line-height:1.75;color:#374151;margin:.5rem 0 .85rem;padding-left:1.4rem;}
.tp-section li{margin-bottom:.3rem;}
.tp-section strong{color:#1a202c;}
.tp-section a{color:#d97706;text-decoration:none;font-weight:600;}
.tp-section a:hover{text-decoration:underline;}
.tp-alert{background:#fef2f2;border:1px solid #fecaca;border-left:4px solid #dc2626;border-radius:8px;padding:.9rem 1.1rem;font-size:.875rem;color:#7f1d1d;margin:.75rem 0;}
.tp-card-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:.75rem;margin:.75rem 0;}
.tp-card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:1rem 1.1rem;}
.tp-card-title{font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin:0 0 .3rem;}
.tp-card-val{font-size:.9rem;color:#1a202c;font-weight:600;}
.tp-divider{border:none;border-top:1px solid #e2e8f0;margin:2.5rem 0;}
.tp-footer-note{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:1.25rem 1.5rem;font-size:.85rem;color:#64748b;line-height:1.65;}
.tp-footer-note strong{color:#1a202c;}
</style>
@endpush

@section('content')

<div class="tp-hero">
  <span class="tp-hero-eyebrow">Documento Legal</span>
  <h1 class="tp-hero-title">Termos e Condições de Utilização</h1>
  <p class="tp-hero-sub">Última actualização: Junho de 2026 &nbsp;·&nbsp; Versão 1.0</p>
</div>

<div class="tp-body">

  <div class="tp-toc">
    <p class="tp-toc-title">Índice</p>
    <ol>
      <li><a href="#aceitacao">Aceitação dos Termos</a></li>
      <li><a href="#servicos">Descrição dos Serviços</a></li>
      <li><a href="#conta">Conta de Cliente</a></li>
      <li><a href="#pagamento">Condições de Pagamento</a></li>
      <li><a href="#reembolso">Política de Reembolso</a></li>
      <li><a href="#uso-aceitavel">Uso Aceitável</a></li>
      <li><a href="#revendedores">Programa de Revendedores</a></li>
      <li><a href="#propriedade">Propriedade Intelectual</a></li>
      <li><a href="#responsabilidade">Limitação de Responsabilidade</a></li>
      <li><a href="#suspensao">Suspensão e Cancelamento</a></li>
      <li><a href="#alteracoes">Alterações aos Termos</a></li>
      <li><a href="#lei">Lei Aplicável e Foro</a></li>
    </ol>
  </div>

  {{-- 1 --}}
  <div class="tp-section" id="aceitacao">
    <h2><span class="tp-num">1</span> Aceitação dos Termos</h2>
    <p>Ao aceder ao site <strong>angolawifi.ao</strong>, adquirir qualquer plano ou serviço, ou criar uma conta de cliente, declara ter lido, compreendido e aceite integralmente os presentes Termos e Condições de Utilização, bem como a nossa <a href="{{ url('/privacy') }}">Política de Privacidade</a>.</p>
    <p>Se não concordar com estes Termos, deverá abster-se de utilizar os nossos serviços.</p>
  </div>

  {{-- 2 --}}
  <div class="tp-section" id="servicos">
    <h2><span class="tp-num">2</span> Descrição dos Serviços</h2>
    <p>A <strong>AngolaWiFi</strong> disponibiliza os seguintes serviços através da sua plataforma digital:</p>
    <ul>
      <li><strong>Planos Individuais (Autovenda):</strong> vouchers de acesso WiFi com validade Diária, Semanal ou Mensal, adquiridos e entregues automaticamente após confirmação de pagamento.</li>
      <li><strong>Planos Familiares e Empresariais:</strong> planos mensais de acesso à internet, activados manualmente pela nossa equipa após confirmação de pagamento e verificação dos dados do cliente.</li>
      <li><strong>Programa de Revendedores:</strong> possibilidade de aquisição de códigos WiFi em bloco, mediante aprovação de candidatura e pagamento de taxa de manutenção mensal.</li>
      <li><strong>Equipamentos:</strong> venda de equipamentos de rede compatíveis com os serviços AngolaWiFi, sujeita a disponibilidade de stock.</li>
    </ul>
    <p>A AngolaWiFi reserva-se o direito de alterar, suspender ou descontinuar qualquer serviço ou plano, mediante aviso prévio adequado aos clientes activos.</p>
  </div>

  {{-- 3 --}}
  <div class="tp-section" id="conta">
    <h2><span class="tp-num">3</span> Conta de Cliente</h2>
    <p>A criação de conta é opcional para a compra de planos individuais, mas obrigatória para aceder ao painel de revendedor. O cliente é responsável por:</p>
    <ul>
      <li>Fornecer informações verídicas, actuais e completas no momento do registo.</li>
      <li>Manter as suas credenciais de acesso confidenciais e não as partilhar com terceiros.</li>
      <li>Notificar imediatamente a AngolaWiFi em caso de acesso não autorizado à sua conta.</li>
    </ul>
    <p>A AngolaWiFi reserva-se o direito de suspender ou cancelar contas que violem estes Termos ou que apresentem actividade fraudulenta.</p>
  </div>

  {{-- 4 --}}
  <div class="tp-section" id="pagamento">
    <h2><span class="tp-num">4</span> Condições de Pagamento</h2>
    <p>Todos os pagamentos são processados em <strong>Kwanzas (AOA)</strong> através da <strong>gateway segura EMIS GPO</strong>, que suporta Multicaixa Express e cartão bancário. A EMIS opera sob supervisão do <strong>Banco Nacional de Angola (BNA)</strong>.</p>
    <ul>
      <li>Os preços indicados na plataforma incluem todos os impostos aplicáveis.</li>
      <li>O pagamento é confirmado automaticamente pelo sistema EMIS após aprovação da transacção bancária.</li>
      <li>A entrega do serviço (código WiFi ou activação do plano) ocorre imediatamente após confirmação de pagamento.</li>
      <li>A AngolaWiFi não armazena dados de cartão bancário. Toda a informação de pagamento é tratada exclusivamente pela EMIS.</li>
    </ul>
  </div>

  {{-- 5 --}}
  <div class="tp-section" id="reembolso">
    <h2><span class="tp-num">5</span> Política de Reembolso</h2>
    <div class="tp-alert">
      <strong>Sem direito a reembolso após entrega do código.</strong> Pelo carácter digital e imediato do serviço, os planos individuais não são reembolsáveis após a entrega do código de acesso WiFi.
    </div>
    <p>Exceptuam-se os casos em que:</p>
    <ul>
      <li>O pagamento foi debitado mas o código não foi entregue por falha técnica do nosso sistema.</li>
      <li>O serviço não estava disponível na área indicada e o cliente não foi previamente informado.</li>
      <li>Ocorreu um erro de cobrança dupla comprovado.</li>
    </ul>
    <p>Para reclamações de reembolso, contacte-nos em <a href="mailto:suporte@angolawifi.ao">suporte@angolawifi.ao</a> no prazo de <strong>5 dias úteis</strong> após a transacção, com indicação da referência de pagamento. As reclamações serão analisadas caso a caso.</p>
  </div>

  {{-- 6 --}}
  <div class="tp-section" id="uso-aceitavel">
    <h2><span class="tp-num">6</span> Uso Aceitável</h2>
    <p>O acesso aos serviços AngolaWiFi destina-se exclusivamente a fins lícitos. É expressamente proibido:</p>
    <ul>
      <li>Partilhar ou revender o código WiFi adquirido para uso individual a terceiros sem autorização.</li>
      <li>Utilizar os serviços para actividades ilegais, incluindo as previstas na <strong>Lei n.º 5/2017 de 27 de Janeiro</strong> (Lei dos Crimes Informáticos de Angola).</li>
      <li>Tentar contornar os sistemas de autenticação ou de controlo de acesso.</li>
      <li>Utilizar os serviços para enviar comunicações não solicitadas (spam) ou conteúdo malicioso.</li>
      <li>Interferir com a infra-estrutura de rede da AngolaWiFi ou dos seus parceiros.</li>
    </ul>
    <p>A violação destas regras implica a suspensão imediata do acesso e pode resultar em acções legais nos termos da legislação angolana.</p>
  </div>

  {{-- 7 --}}
  <div class="tp-section" id="revendedores">
    <h2><span class="tp-num">7</span> Programa de Revendedores</h2>
    <p>A participação no Programa de Revendedores está sujeita a:</p>
    <ul>
      <li>Aprovação prévia da candidatura pela equipa AngolaWiFi.</li>
      <li>Pagamento único da taxa de instalação.</li>
      <li>Pagamento de taxa de manutenção mensal, conforme tabela em vigor.</li>
      <li>Cumprimento das políticas de revenda, incluindo respeito pelo preço de tabela ao público.</li>
      <li>Não partilha das credenciais do painel de revendedor com terceiros.</li>
    </ul>
    <p>A AngolaWiFi pode revogar o estatuto de revendedor em caso de incumprimento das presentes condições, fraude comprovada ou cessação voluntária.</p>
  </div>

  {{-- 8 --}}
  <div class="tp-section" id="propriedade">
    <h2><span class="tp-num">8</span> Propriedade Intelectual</h2>
    <p>Todo o conteúdo da plataforma — incluindo logotipo, design, textos, imagens e software — é propriedade da AngolaWiFi ou dos seus licenciantes e está protegido pela <strong>Lei n.º 15/14 de 31 de Julho</strong> (Lei dos Direitos de Autor e Direitos Conexos de Angola).</p>
    <p>É proibida a reprodução, distribuição ou utilização comercial de qualquer conteúdo sem autorização prévia e escrita da AngolaWiFi.</p>
  </div>

  {{-- 9 --}}
  <div class="tp-section" id="responsabilidade">
    <h2><span class="tp-num">9</span> Limitação de Responsabilidade</h2>
    <p>Na máxima medida permitida pela lei angolana, a AngolaWiFi não é responsável por:</p>
    <ul>
      <li>Interrupções de serviço resultantes de factores externos (cortes de energia, falhas de rede de terceiros, casos de força maior).</li>
      <li>Perda de dados ou danos causados pelo uso inadequado dos códigos WiFi.</li>
      <li>Danos indirectos, consequenciais ou lucros cessantes resultantes da utilização ou impossibilidade de utilização dos serviços.</li>
    </ul>
    <p>A responsabilidade máxima da AngolaWiFi fica limitada ao valor pago pelo cliente na transacção em causa.</p>
  </div>

  {{-- 10 --}}
  <div class="tp-section" id="suspensao">
    <h2><span class="tp-num">10</span> Suspensão e Cancelamento</h2>
    <p>O cliente pode cancelar a sua conta a qualquer momento contactando o suporte. A AngolaWiFi pode suspender ou cancelar o acesso, com ou sem aviso prévio, nos casos de:</p>
    <ul>
      <li>Violação destes Termos e Condições.</li>
      <li>Actividade fraudulenta ou suspeita de fraude.</li>
      <li>Não pagamento de valores em dívida (para revendedores).</li>
      <li>Decisão de descontinuação do serviço, com aviso prévio de 30 dias.</li>
    </ul>
  </div>

  {{-- 11 --}}
  <div class="tp-section" id="alteracoes">
    <h2><span class="tp-num">11</span> Alterações aos Termos</h2>
    <p>A AngolaWiFi reserva-se o direito de actualizar estes Termos a qualquer momento. Alterações substanciais serão comunicadas por e-mail aos clientes registados com antecedência mínima de <strong>15 dias</strong>. A continuação da utilização dos serviços após essa data implica a aceitação dos novos Termos.</p>
  </div>

  {{-- 12 --}}
  <div class="tp-section" id="lei">
    <h2><span class="tp-num">12</span> Lei Aplicável e Foro</h2>
    <p>Os presentes Termos são regidos pela lei angolana, nomeadamente:</p>
    <ul>
      <li><strong>Lei n.º 22/11 de 17 de Junho</strong> — Lei da Protecção de Dados Pessoais.</li>
      <li><strong>Lei n.º 5/2017 de 27 de Janeiro</strong> — Lei dos Crimes Informáticos.</li>
      <li><strong>Lei n.º 7/17 de 16 de Fevereiro</strong> — Lei das Comunicações Electrónicas e dos Serviços da Sociedade de Informação.</li>
      <li><strong>Lei n.º 15/14 de 31 de Julho</strong> — Lei dos Direitos de Autor.</li>
      <li>Código Civil Angolano e legislação comercial aplicável.</li>
    </ul>
    <p>Para resolução de litígios emergentes destes Termos, as partes elegem os <strong>Tribunais da Comarca de Luanda</strong> como foro competente, com expressa renúncia a qualquer outro.</p>
  </div>

  <hr class="tp-divider">

  <div class="tp-footer-note">
    <strong>Dúvidas?</strong> Contacte-nos em <a href="mailto:suporte@angolawifi.ao" style="color:#d97706;font-weight:600;">suporte@angolawifi.ao</a> ou pelo +244 949 364 505.<br><br>
    <a href="{{ url('/privacy') }}" style="color:#d97706;font-weight:600;">Ver Política de Privacidade &rarr;</a>
  </div>

</div>
@endsection
