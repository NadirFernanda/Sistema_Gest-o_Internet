# EMIS GPO: Android, callbacks e operação segura

## Evidência e decisão de integração

O manual local `pagamentos/GPO Manual de Integração - v2.2.8 1.pdf` (EMIS,
versão 02.80, 2023-07-07) descreve o fluxo WebFrame nas páginas 13–18: o
servidor cria um token único por compra e o cliente abre a WebFrame, com iframe
como exemplo explícito. A página 18 também documenta `postMessage` e o callback
server-to-server. O manual não documenta um deep link Android nem exige que o
comerciante construa um URL alternativo de checkout. Por isso, a integração
continua a utilizar o URL de WebFrame devolvido pela EMIS; não se inventam URIs,
packages Android ou parâmetros.

Referências oficiais da plataforma para a semântica do atributo `sandbox`:
[WHATWG HTML — iframe sandbox](https://html.spec.whatwg.org/multipage/iframe-embed-object.html#attr-iframe-sandbox)
e
[MDN — iframe sandbox tokens](https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/iframe#allow-top-navigation-to-custom-protocols).

As quatro páginas de pagamento usavam `sandbox` sem
`allow-top-navigation-by-user-activation`. Nesse estado, o conteúdo EMIS podia
executar scripts e abrir pop-ups, mas não podia navegar a janela principal
como consequência de um clique explícito do cliente. A hipótese da EMIS é,
portanto, tecnicamente consistente com a marcação encontrada e especialmente
relevante para sair da WebFrame e abrir a aplicação no Android. O `sandbox`
continua activo; foi acrescentada apenas a permissão de navegação de topo
condicionada a uma activação explícita do utilizador. A política CSP `frame-src`
continua limitada ao host EMIS configurado.

### Navegação para protocolos da aplicação

Não acrescentar `allow-top-navigation-to-custom-protocols` à sandbox actual.
O iframe já declara `allow-popups`, que, de acordo com a documentação HTML da
WHATWG e MDN, também activa navegações para protocolos personalizados. A
permissão nova `allow-top-navigation-by-user-activation` é distinta: deixa
conteúdo do iframe navegar o contexto de topo apenas após gesto do utilizador,
sem abrir navegação de topo irrestrita. Não se adiciona `allow-top-navigation`
nem se remove o sandbox. Esta análise explica a política do browser, mas não
confirma que a EMIS invoque efectivamente um protocolo personalizado no botão,
nem que o handoff concreto funcione em cada browser/dispositivo.

### Frames, CSP e redireccionamentos

Nas páginas Blade da loja existe um único iframe EMIS; não há iframe local
aninhado nem `srcdoc`. O `frame-src` do CSP da loja permite somente
`https://pagamentonline.emis.co.ao`, que é o host configurado para a WebFrame.
`frame-ancestors 'none'` e `X-Frame-Options: DENY` impedem terceiros de
embutirem a página da loja; não impedem a loja de embutir EMIS. A CSP da loja
não controla os documentos que EMIS, por sua vez, possa carregar dentro da
WebFrame, nem os seus redireccionamentos. Esses recursos e cabeçalhos só podem
ser confirmados pela EMIS ou numa sessão de homologação no browser.

Em resposta ao relato da EMIS de que foi observado um componente Modal a chamar
a Frame, foi revalidado o fluxo actual no repositório: o POST do checkout
redirecciona para `/pagar/gpo/{order}` e o servidor entrega uma página de
pagamento dedicada, com o iframe como conteúdo de um cartão normal — não existe
modal que crie, abra ou contenha `#gpoFrame`. Um teste de integração verifica
esta relação estrutural no HTML renderizado. Existe, no layout partilhado, um
aviso de consentimento de cookies com `role="dialog"`; é independente da
WebFrame, não a contém nem a chama, mas pode aparecer como sobreposição no
fundo da página enquanto não for aceite. Se a EMIS estiver a observar esse
aviso ou uma página/modal diferente na produção, são necessários o URL exacto,
browser, momento e captura/DOM da observação para identificar qualquer
divergência entre produção e o código aqui analisado.

`allow-top-navigation-by-user-activation` não remove restrições CSP do
documento EMIS, política de navegação do browser, bloqueio de abertura da app
sem gesto explícito, nem requisitos de associação de links Android/iOS. A
WebFrame é de origem cruzada, pelo que a loja não pode inspeccionar o DOM,
frames internos ou redireccionamentos EMIS. O `postMessage` registado é apenas
um sinal para iniciar polling do estado; a origem é comparada com o host EMIS
conhecido e o evento nunca é usado por si só para declarar pagamento.

Isto identifica e corrige uma restrição real do iframe, mas não prova por si só
que todos os navegadores Android, versões da aplicação EMIS ou estados do
dispositivo irão abrir MULTICAIXA Express. Essa prova exige homologação com a
EMIS e ensaio num dispositivo real.

## Callback e entrega

O manual define o callback como o objecto da consulta de transacção. O código
agora reconhece `merchantReferenceNumber`, `status`, `transactionType`,
`amount` e `currency`, e só classifica `status=ACCEPTED` com
`transactionType=PAYMENT` como pagamento confirmado. Uma autorização (`AUTHORIZATION`),
um estado não documentado como sucesso ou montante/moeda em falta não emite
voucher. O montante/moeda são comparados com a encomenda ou compra antes da
entrega. Os registos de callback guardam apenas identificadores e estado, não o
corpo integral da notificação.

No fluxo de autovenda, actualizações da página reutilizam o token EMIS enquanto
o TTL devolvido pelo gateway estiver válido. Depois da expiração (ou de uma
falha/cancelamento), é criada uma nova referência única; as referências
anteriores ficam associadas à mesma encomenda para callbacks atrasados. A
entrega da autovenda bloqueia a encomenda durante a transacção, evitando que
callbacks simultâneos emitam dois vouchers.

O manual consultado não especifica assinatura criptográfica do callback nem
uma allowlist pública de IPs EMIS. A aplicação exige `GPO_CALLBACK_IPS` em
produção; sem allowlist, os callbacks são rejeitados com 503 e registados como
erro crítico. Antes do deploy, pedir à EMIS os IPs oficiais de origem e
configurá-los no ambiente, confirmando também a configuração de proxies
confiáveis para que o IP observado pelo Laravel não seja controlável pelo
cliente. Não preencher a variável com IPs presumidos. A integração actual não
faz uma consulta REST autenticada à EMIS para validar cada callback; para esse
nível adicional de confirmação, são necessários os dados oficiais do terminal
e credenciais/API concedidas pela EMIS.

## Validação antes da publicação

### Pedidos à EMIS antes da homologação

Solicitar formalmente:

1. Confirmação de que o botão “ABRIR MULTICAIXA EXPRESS” suporta WebFrame
   incorporada em iframe sandboxed e quais permissões de sandbox são exigidas.
2. Confirmação do mecanismo usado pelo botão (navegação da janela principal,
   popup, Android App Link, URI scheme ou Android Intent), sem pedir à loja que
   invente/derive o esquema. Solicitar os browsers/versões e restrições
   suportados, incluindo Samsung Internet, iOS e navegadores in-app.
3. URLs oficiais da WebFrame por ambiente e a lista de origens/domínios de
   frames e redireccionamentos que o comerciante deve permitir.
4. IPs/faixas de origem actuais dos callbacks por ambiente, processo de aviso
   de alterações e comportamento de retry/timeout.
5. Mecanismo de autenticação criptográfica de callbacks: algoritmo, localização
   e encoding da assinatura, headers/campos assinados, gestão/rotação de chaves,
   timestamps/nonces e protecção contra replay. Se não houver assinatura,
   confirmação escrita e alternativa recomendada para validar a transacção
   consultando a API EMIS autenticada.
6. Schema real do callback e da consulta de transacção: nomes, tipos e valores
   possíveis de `status`, `transactionType`, referência, montante, moeda,
   identificador; semântica dos estados finais, autorizações, cancelamentos e
   reversões.
7. Credenciais, terminal/posId, endpoint e escopo exactos de homologação, bem
   como números de teste e resultado esperado, sem transacções financeiras reais.
8. Procedimento conjunto de homologação e contacto de suporte para Android/iOS.

Uma allowlist IP é uma defesa de rede e não uma assinatura criptográfica nem
prova isolada de autenticidade. Não configurar IPs presumidos. A ausência de
`GPO_CALLBACK_IPS` bloqueia os endpoints GPO em produção com 503; não afecta
outros métodos de pagamento. A mesma configuração aplica-se aos callbacks GPO
de planos individuais, familiares, revendedores e manutenção.

### Roteiro executável por dispositivo

Executar apenas em ambiente de homologação EMIS com aparelho/cartão/conta de
teste fornecidos ou aprovados pela EMIS. Não usar cobranças reais.

Para cada caso, registar ID do caso, data/hora com fuso, marca/modelo, versão
Android/iOS, browser e versão, versão da app MULTICAIXA Express, estado da
app instalada, ambiente EMIS, referência comercial mascarada, resultado e
capturas de ecrã. Guardar também HAR/console do browser quando disponível,
redigindo tokens, dados de cartão, telefone, sessão e outros dados pessoais.
Nos logs backend, correlacionar referência, ID de transacção, estado EMIS,
resultado do callback e estado da encomenda; nunca guardar payload sensível.

1. Abrir a loja no browser indicado, escolher um plano de teste e iniciar EMIS.
   Confirmar que a página de pagamento carrega a WebFrame EMIS, que não há
   erro CSP no console e que a referência/valor exibidos correspondem ao pedido.
2. Com MULTICAIXA Express instalada e configurada para homologação, tocar uma
   única vez em “ABRIR MULTICAIXA EXPRESS”. Registar gravação de ecrã mostrando
   o toque e a abertura da app (não divulgar dados de conta). Completar a
   transacção de teste; guardar comprovativo EMIS, callback correlacionado e
   encomenda marcada paga com apenas um voucher.
3. Repetir com a aplicação ausente. Registar a mensagem/alternativa apresentada
   pelo browser/gateway e confirmar que a loja continua pendente ou mostra
   falha, sem voucher. Não inferir como deve funcionar: comparar com a UX
   homologada pela EMIS.
4. Repetir para cancelamento/recusa e estado pendente, verificar regresso ou
   reabertura da loja e confirmar que nenhum deles emite voucher.
5. Recarregar antes da expiração e confirmar reutilização do token/referência;
   após TTL, confirmar novo token e nova referência. Repetir o callback de teste
   e verificar entrega única. Interromper a rede, regressar e actualizar a loja:
   o estado deve vir do backend, nunca do parâmetro de retorno do browser.
6. Repetir os passos 1–5 em Android Chrome e Samsung Internet. Em iPhone,
   repetir no Safari (e Chrome iOS se estiver no escopo de homologação). Fazer
   um caso desktop para regressão da WebFrame.

Critério de aprovação do handoff: a gravação mostra o toque explícito e a
mudança para a aplicação MULTICAIXA Express no mesmo dispositivo/browser; o
comprovativo de homologação corresponde à referência e ao montante; os logs
demonstram callback validado e a encomenda fica paga; existe exactamente uma
alocação de voucher; em cancelado/pendente/rede interrompida, não existe
voucher. Uma captura isolada da página de pagamento ou retorno ao site não é
prova de abertura da app nem de pagamento.

O teste de página inicial utiliza `RefreshDatabase` para criar
`purchase_help_videos` apenas na base SQLite em memória definida por
`phpunit.xml`; não acede à base de produção. Antes do teste físico, executar os
testes automatizados localmente com `php artisan test`.

Na execução inicial, os 14 testes passaram (95 assertions), mas PHPUnit
assinalou 1 teste arriscado devido ao output buffer deixado aberto pelo GET da
homepage. Esse problema foi investigado e corrigido abaixo.

| Ambiente | Estado |
|---|---|
| Android Chrome | Pendente de dispositivo real e homologação EMIS |
| Android Samsung Internet | Pendente de dispositivo real e homologação EMIS |
| iPhone Safari e Chrome | Pendente de regressão em dispositivo real |
| Desktop | Pendente de regressão em homologação |

## Publicação e rollback

Publicar primeiro em homologação, instalar a nova versão das quatro views e
do serviço GPO e configurar apenas os IPs EMIS oficialmente confirmados.
Monitorizar rejeições 503 de callback, discrepâncias de montante/moeda e
estados de pagamento sem emitir vouchers. Em caso de regressão no handoff,
reverter a alteração de `sandbox` nas quatro views para o atributo anterior;
reverter separadamente as alterações do parser/controladores apenas com
reconciliação das transacções pendentes. Não apagar encomendas nem reutilizar
referências. Não foi feito deploy nem executada transacção real.

## Plano de homologação Android sem acesso ao ambiente

O candidato de homologação é o worktree local actual, que contém a permissão
`allow-top-navigation-by-user-activation` nas quatro views GPO. A branch `main`
e `origin/main` permanecem no commit `6671fca`; o worktree tem alterações não
commitadas. Não há acesso configurado nesta sessão a um ambiente EMIS de
homologação, credenciais aprovadas, servidor de staging ou dispositivos físicos.
Por isso, este documento prepara a validação, mas não declara uma versão
instalada nem uma WebFrame de homologação efectivamente observada.

Antes de disponibilizar o candidato, publicar apenas num host de staging
isolado, com base de dados e credenciais de homologação separadas das de
produção. Confirmar no HTML recebido que cada fluxo usa um iframe GPO com
`allow="payment"` e sandbox contendo
`allow-scripts allow-forms allow-same-origin allow-popups
allow-top-navigation-by-user-activation`. Confirmar também que não há atributo
`target`, mutação posterior de `sandbox`/`allow` ou wrapper modal no DOM
renderizado. Não importar encomendas ou dados pessoais reais.

### Depuração remota e recolha de evidências

**Android Chrome:** activar Opções de programador e depuração USB no Android,
ligar o aparelho por USB a uma estação de teste autorizada, aceitar a
autorização de depuração no aparelho e abrir `chrome://inspect/#devices` no
Chrome desktop. Seleccionar **Inspect** no separador da loja. Em DevTools,
activar Preserve log em Console e Network; limpar registos antes do caso; então
carregar o pagamento de homologação e tocar uma vez no botão EMIS. Registar a
mensagem completa de bloqueio/erro, hora, URL/origem e sequência de navegação.
Não exportar HAR bruto: pode conter token de Frame, cookies, referências ou
dados pessoais. Se HAR for indispensável, sanitizar localmente antes de o
partilhar e verificar que query strings, cabeçalhos de autorização, cookies,
corpos de pedido e dados bancários foram removidos.

Na consola, guardar apenas erros relevantes de CSP, sandbox, política de
navegação, popup/protocolo externo e `postMessage`. Em Network, registar host,
status HTTP e cadeia de redireccionamento, removendo paths/query que contenham
tokens. Capturar a gravação de ecrã do toque e da mudança (ou ausência de
mudança) para a app. A página da loja não consegue observar DOM, console ou
redirects de documentos cross-origin dentro da EMIS; os logs do DevTools e a
observação da EMIS são necessários para identificar o elo exacto.

**Samsung Internet:** executar o mesmo caso no aparelho Samsung. Usar a opção
de Web Inspector/depuração remota disponibilizada pela versão instalada e
documentada pela Samsung; se não estiver disponível, gravar o ecrã e recolher
diagnóstico do browser/aparelho com suporte Samsung. Não assumir que a sessão
remota do Chrome inspecciona Samsung Internet. Registar versão exacta do
browser e sistema.

**iPhone Safari:** inspeccionar com Safari Web Inspector num Mac, com Web
Inspector activado no iPhone e o aparelho ligado ao Mac. O Chrome no iPhone
deve ser ensaiado separadamente como regressão; não inferir comportamento de
Chrome Android a partir dele. Sem Mac/iPhone, esses casos permanecem pendentes.

Para distinguir a causa, comparar no mesmo build, aparelho, rede e sessão:
(a) primeiro acesso com aviso de cookies visível; (b) aviso aceite/oculto; e
(c) repetição com clique numa área desobstruída do botão na Frame. O aviso é
`position: fixed`, tem `z-index: 9999` e só é escondido depois de consentimento
guardado em `localStorage`; portanto **pode cobrir e interceptar toques na
parte inferior do viewport**, mas não foi demonstrado que cubra o botão EMIS.
Registar posição do botão e overlay no instante do toque. Se o toque ocorrer
fora da área coberta e o clique for recebido pela Frame, o aviso não explica
esse caso.

### Segurança do callback durante homologação

Os callbacks GPO actuais não validam assinatura criptográfica EMIS. Em produção,
a ausência de `GPO_CALLBACK_IPS` falha com 503; uma allowlist limita origens,
mas não autentica criptograficamente o conteúdo. Em ambientes não-prod, a
implementação pode permitir callback sem allowlist, logo **não activar callbacks
financeiros de homologação nesse estado**. Antes de qualquer transacção, obter
da EMIS o esquema de assinatura/verificação ou a consulta autenticada de
transacção e validar a implementação; além disso, configurar somente IPs de
homologação oficialmente fornecidos e confirmar o IP observado após proxies.
Se a EMIS não oferecer autenticação criptográfica, exigir o procedimento
alternativo escrito e validação server-to-server da transacção antes de aceitar
pagamento ou emitir voucher. Até essa validação, limitar a sessão a ensaios de
UI sem transacções e callbacks sintéticos em testes automatizados.

## Estado desta validação

O teste de homepage foi corrigido: a view `store/index.blade.php` não fechava
`@section('content')`, deixando duas quebras de linha num output buffer aberto
no pedido de teste. Foi adicionado `@endsection` e uma asserção para garantir
que o nível de output buffer regressa ao nível inicial.

Nesta execução, a suite completa passou sem avisos arriscados: 15 testes e 97
assertions. Nenhum teste físico Android/iOS, handoff de app, callback EMIS de
homologação ou emissão de voucher foi executado.
