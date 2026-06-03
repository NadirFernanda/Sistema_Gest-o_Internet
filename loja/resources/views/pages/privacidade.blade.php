@extends('layouts.app')

@section('seo_title', 'Política de Privacidade | AngolaWiFi')
@section('seo_description', 'Saiba como a AngolaWiFi recolhe, trata e protege os seus dados pessoais, em conformidade com a Lei n.º 22/11 de 17 de Junho — Lei da Protecção de Dados Pessoais da República de Angola.')

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
.tp-alert{background:#f0fdf4;border:1px solid #86efac;border-left:4px solid #16a34a;border-radius:8px;padding:.9rem 1.1rem;font-size:.875rem;color:#166534;margin:.75rem 0;}
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
  <h1 class="tp-hero-title">Política de Privacidade</h1>
  <p class="tp-hero-sub">Última actualização: Junho de 2026 &nbsp;·&nbsp; Versão 1.0</p>
</div>

<div class="tp-body">

  <div class="tp-toc">
    <p class="tp-toc-title">Índice</p>
    <ol>
      <li><a href="#responsavel">Responsável pelo Tratamento</a></li>
      <li><a href="#dados">Dados Pessoais Recolhidos</a></li>
      <li><a href="#finalidade">Finalidade e Base Legal</a></li>
      <li><a href="#direitos">Direitos do Titular dos Dados</a></li>
      <li><a href="#partilha">Partilha com Terceiros</a></li>
      <li><a href="#conservacao">Conservação dos Dados</a></li>
      <li><a href="#seguranca">Segurança</a></li>
      <li><a href="#cookies">Cookies</a></li>
      <li><a href="#menores">Menores de Idade</a></li>
      <li><a href="#alteracoes">Alterações a esta Política</a></li>
      <li><a href="#contacto">Contacto</a></li>
    </ol>
  </div>

  {{-- 1 --}}
  <div class="tp-section" id="responsavel">
    <h2><span class="tp-num">1</span> Responsável pelo Tratamento</h2>
    <p>A <strong>AngolaWiFi</strong> é a entidade responsável pelo tratamento dos seus dados pessoais, nos termos da <strong>Lei n.º 22/11 de 17 de Junho</strong> — Lei da Protecção de Dados Pessoais da República de Angola.</p>
    <div class="tp-card-grid">
      <div class="tp-card"><p class="tp-card-title">Entidade</p><p class="tp-card-val">AngolaWiFi</p></div>
      <div class="tp-card"><p class="tp-card-title">E-mail</p><p class="tp-card-val">suporte@angolawifi.ao</p></div>
      <div class="tp-card"><p class="tp-card-title">Telefone</p><p class="tp-card-val">+244 949 364 505</p></div>
      <div class="tp-card"><p class="tp-card-title">Localização</p><p class="tp-card-val">Luanda, Angola</p></div>
    </div>
  </div>

  {{-- 2 --}}
  <div class="tp-section" id="dados">
    <h2><span class="tp-num">2</span> Dados Pessoais Recolhidos</h2>
    <p>Consoante o serviço utilizado, recolhemos as seguintes categorias de dados:</p>
    <ul>
      <li><strong>Identificação:</strong> nome completo, NIF (quando exigido por lei para planos empresariais/familiares).</li>
      <li><strong>Contacto:</strong> endereço de e-mail, número de telefone angolano (prefixo 244).</li>
      <li><strong>Transacção:</strong> plano adquirido, valor pago, método de pagamento, referência da gateway EMIS GPO, data e hora da transacção.</li>
      <li><strong>Técnicos:</strong> endereço IP, tipo de dispositivo e navegador (recolhidos automaticamente para segurança e prevenção de fraude).</li>
      <li><strong>Candidatura a Revendedor:</strong> dados profissionais fornecidos no formulário de candidatura (tipo de internet, morada comercial, observações).</li>
    </ul>
    <div class="tp-alert">Não recolhemos dados de cartão bancário. O pagamento é processado directamente pela <strong>EMIS (GPO)</strong>, que opera sob a supervisão do Banco Nacional de Angola (BNA).</div>
  </div>

  {{-- 3 --}}
  <div class="tp-section" id="finalidade">
    <h2><span class="tp-num">3</span> Finalidade e Base Legal</h2>
    <p>Os seus dados são tratados para as seguintes finalidades, com as respectivas bases legais ao abrigo da Lei n.º 22/11:</p>
    <ul>
      <li><strong>Execução do contrato</strong> — processar a compra de planos WiFi, entregar o código de acesso, activar planos familiares/empresariais no sistema de gestão (arts. 7.º e 8.º da Lei n.º 22/11).</li>
      <li><strong>Cumprimento de obrigação legal</strong> — emissão de recibos, conservação de registos contabilísticos e fiscais nos termos da Lei n.º 19/14 (Lei Geral Tributária) e do Regime Geral das Contribuições e Impostos.</li>
      <li><strong>Interesse legítimo</strong> — prevenção de fraude, segurança do sistema e protecção dos nossos serviços contra acessos não autorizados.</li>
      <li><strong>Consentimento</strong> — envio de comunicações de marketing por e-mail ou WhatsApp, quando expressamente autorizado.</li>
    </ul>
  </div>

  {{-- 4 --}}
  <div class="tp-section" id="direitos">
    <h2><span class="tp-num">4</span> Direitos do Titular dos Dados</h2>
    <p>Nos termos dos <strong>arts. 12.º a 17.º da Lei n.º 22/11</strong>, tem os seguintes direitos:</p>
    <ul>
      <li><strong>Acesso</strong> — obter confirmação de quais os seus dados que tratamos e receber uma cópia.</li>
      <li><strong>Rectificação</strong> — corrigir dados inexactos ou incompletos.</li>
      <li><strong>Apagamento</strong> — solicitar a eliminação dos seus dados, salvo obrigação legal de conservação.</li>
      <li><strong>Oposição</strong> — opor-se ao tratamento para fins de marketing.</li>
      <li><strong>Portabilidade</strong> — receber os seus dados num formato estruturado e legível por máquina.</li>
      <li><strong>Limitação</strong> — solicitar a suspensão temporária do tratamento enquanto analisa uma reclamação.</li>
    </ul>
    <p>Para exercer os seus direitos, envie um pedido escrito para <a href="mailto:suporte@angolawifi.ao">suporte@angolawifi.ao</a>. Responderemos no prazo máximo de <strong>15 dias úteis</strong>.</p>
    <p>Se considerar que os seus direitos foram violados, pode apresentar queixa junto da <strong>Agência Angolana de Regulação e Supervisão de Seguros (ARSEG)</strong> ou das autoridades de protecção de dados competentes em Angola.</p>
  </div>

  {{-- 5 --}}
  <div class="tp-section" id="partilha">
    <h2><span class="tp-num">5</span> Partilha com Terceiros</h2>
    <p>Os seus dados pessoais não são vendidos nem cedidos a terceiros para fins comerciais. Apenas partilhamos dados nos seguintes casos:</p>
    <ul>
      <li><strong>EMIS (Empresa Interbancária de Serviços)</strong> — para processar o pagamento via gateway GPO. A EMIS opera sob supervisão do BNA.</li>
      <li><strong>Prestadores de serviços de e-mail</strong> — para envio de confirmações de compra e notificações de serviço.</li>
      <li><strong>Autoridades competentes</strong> — quando exigido por lei, ordem judicial ou para cumprimento de obrigação legal angolana.</li>
    </ul>
    <p>Todos os subcontratantes estão vinculados a obrigações de confidencialidade e só tratam os dados para as finalidades específicas indicadas.</p>
  </div>

  {{-- 6 --}}
  <div class="tp-section" id="conservacao">
    <h2><span class="tp-num">6</span> Conservação dos Dados</h2>
    <p>Os dados são conservados pelo período mínimo necessário à finalidade para que foram recolhidos:</p>
    <ul>
      <li><strong>Dados de transacção e faturação:</strong> 10 anos, nos termos da legislação fiscal angolana (art. 53.º do Código Geral Tributário).</li>
      <li><strong>Dados de contacto (marketing):</strong> até revogação do consentimento ou, no máximo, 2 anos após o último contacto.</li>
      <li><strong>Logs de acesso e segurança:</strong> 12 meses.</li>
      <li><strong>Candidaturas a revendedor rejeitadas:</strong> 6 meses após decisão final.</li>
    </ul>
    <p>Após o prazo aplicável, os dados são eliminados de forma segura e irreversível.</p>
  </div>

  {{-- 7 --}}
  <div class="tp-section" id="seguranca">
    <h2><span class="tp-num">7</span> Segurança</h2>
    <p>Implementamos medidas técnicas e organizacionais adequadas para proteger os seus dados pessoais contra acesso não autorizado, perda acidental ou destruição, designadamente:</p>
    <ul>
      <li>Transmissão encriptada via <strong>HTTPS/TLS</strong> em todas as páginas do site.</li>
      <li>Armazenamento em servidores localizados em Angola, com acesso restrito por autenticação forte.</li>
      <li>Separação de ambientes: os dados de pagamento são tratados exclusivamente pela EMIS e nunca armazenados nos nossos servidores.</li>
      <li>URLs de confirmação de compra assinadas digitalmente para prevenir enumeração de pedidos (IDOR).</li>
      <li>Revisão periódica de acessos e permissões.</li>
    </ul>
    <p>Em caso de violação de segurança que afecte os seus dados, notificaremos as autoridades competentes e os titulares afectados no prazo legalmente estabelecido.</p>
  </div>

  {{-- 8 --}}
  <div class="tp-section" id="cookies">
    <h2><span class="tp-num">8</span> Cookies</h2>
    <p>O nosso site utiliza cookies estritamente necessários para o funcionamento do serviço:</p>
    <ul>
      <li><strong>Sessão de autenticação</strong> — para manter a sessão de administração segura.</li>
      <li><strong>Token CSRF</strong> — para proteger os formulários contra ataques de falsificação de pedidos entre sítios.</li>
      <li><strong>Preferências do carrinho</strong> — para manter os items seleccionados durante a navegação.</li>
    </ul>
    <p>Não utilizamos cookies de rastreamento, publicidade ou análise de terceiros (ex: Google Analytics, Facebook Pixel). Pode configurar o seu navegador para recusar cookies, mas algumas funcionalidades do site poderão não funcionar correctamente.</p>
  </div>

  {{-- 9 --}}
  <div class="tp-section" id="menores">
    <h2><span class="tp-num">9</span> Menores de Idade</h2>
    <p>Os nossos serviços não se destinam a menores de <strong>18 anos</strong>. Não recolhemos conscientemente dados pessoais de menores. Se tiver conhecimento de que um menor nos forneceu dados pessoais, contacte-nos imediatamente para <a href="mailto:suporte@angolawifi.ao">suporte@angolawifi.ao</a> para procedermos à sua eliminação.</p>
  </div>

  {{-- 10 --}}
  <div class="tp-section" id="alteracoes">
    <h2><span class="tp-num">10</span> Alterações a esta Política</h2>
    <p>Podemos actualizar esta Política de Privacidade periodicamente para reflectir alterações nos nossos serviços ou na legislação aplicável. Quando o fizermos, actualizamos a data de "Última actualização" no topo desta página. Alterações materiais serão comunicadas por e-mail aos titulares de conta activos com antecedência mínima de 15 dias.</p>
  </div>

  {{-- 11 --}}
  <div class="tp-section" id="contacto">
    <h2><span class="tp-num">11</span> Contacto</h2>
    <p>Para qualquer questão relacionada com a protecção dos seus dados pessoais ou para exercer os seus direitos, contacte-nos:</p>
    <div class="tp-card-grid">
      <div class="tp-card"><p class="tp-card-title">E-mail</p><p class="tp-card-val"><a href="mailto:suporte@angolawifi.ao">suporte@angolawifi.ao</a></p></div>
      <div class="tp-card"><p class="tp-card-title">Telefone</p><p class="tp-card-val">+244 949 364 505</p></div>
      <div class="tp-card"><p class="tp-card-title">Horário</p><p class="tp-card-val">Seg – Sex, 08h–18h</p></div>
    </div>
  </div>

  <hr class="tp-divider">

  <div class="tp-footer-note">
    <strong>Legislação aplicável:</strong> Esta Política é regida pela <strong>Lei n.º 22/11 de 17 de Junho</strong> (Lei da Protecção de Dados Pessoais da República de Angola) e demais legislação angolana aplicável. Para efeitos de resolução de litígios, as partes elegem os tribunais da comarca de Luanda como foro competente.
    <br><br>
    <a href="{{ url('/terms') }}" style="color:#d97706;font-weight:600;">Ver Termos e Condições de Utilização &rarr;</a>
  </div>

</div>
@endsection
