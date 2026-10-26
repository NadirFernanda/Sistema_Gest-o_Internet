# Diagnóstico Android: WebFrame EMIS cinzenta após abrir MULTICAIXA Express

## Estado do diagnóstico

A captura recebida mantém visível o cabeçalho e o cartão AngolaWiFi «Pagamento
seguro», mas mostra cinzenta a área do conteúdo EMIS depois do toque em
«ABRIR MULTICAIXA EXPRESS». Isto é compatível com um documento interno da
WebFrame que deixou de apresentar conteúdo após o handoff para a aplicação;
por si só, a captura não identifica a causa nem prova que o clique chegou à
aplicação.

Na view individual actual, `#gpoFrame` recebe inicialmente o URL criado por
`GpoService`: a origem/configuração WebFrame seguida de `/frame?token=...`.
O URL contém um token de compra e não deve ser registado ou partilhado. O HTML
tem um único iframe e não há rotina que altere o seu `src` após o clique.
`postMessage` de origem EMIS apenas inicia o polling de estado; se o servidor
confirmar um estado terminal, a loja troca o painel pelo painel de sucesso ou
recusa, em vez de esvaziar silenciosamente o conteúdo interno. A página
principal permite `frame-src https://pagamentonline.emis.co.ao`; uma eventual
navegação interna para outra origem pode ser bloqueada pela CSP da página
principal. Não foi possível observar cabeçalhos/redireccionamentos de produção
nem consola do dispositivo nesta análise.

O documento dentro do iframe é cross-origin. A loja não pode ler o seu DOM,
URL actual, conteúdo, redireccionamentos, erros internos ou estado visual.
O evento `load` só indica que o browser concluiu um carregamento do frame; não
prova sucesso nem permite distinguir uma página vazia. Eventos `blur` e
`visibilitychange` podem coincidir com a abertura de uma aplicação, mas não
provam que a aplicação foi chamada. O browser/DevTools Android e a EMIS têm de
fornecer a evidência decisiva.

## Instrumentação opt-in

Na rota individual, acrescentar `?gpo_diag=1` à página de pagamento para
activar a instrumentação. Sem esse parâmetro, a lista e os listeners de
diagnóstico não são criados. Os eventos ficam apenas numa lista limitada a 100
entradas na memória da página, sem saída automática para o console e sem
pedidos de rede. No Chrome Android, abrir `chrome://inspect/#devices`,
inspeccionar o separador e consultar `window.gpoFrameDiagnostics` na consola
após o teste. Guardar apenas os campos/eventos sanitizados necessários.
Os registos incluem apenas nomes de eventos, tempo decorrido, estado de
visibilidade, dimensões do iframe, contagem de loads, origem EMIS previamente
validada (representada apenas como mensagem EMIS), presença de dados
booleanizada e a directiva CSP. Não incluem URL, query string, token, payload
`postMessage`, cookie, código WiFi ou dados financeiros. Não é feita nenhuma
chamada de telemetria à loja.

Eventos instrumentados: carregamento/erro de recurso reportado pelo browser,
violação CSP da página principal, mensagens da origem EMIS, foco/desfoco,
visibilidade, `pagehide`, `pageshow` e `beforeunload`. O listener de erro do
iframe é best-effort; browsers frequentemente não expõem erros de navegação
de documentos no evento `error`. DevTools e a captura nativa do browser podem
mostrar URLs de rede com tokens, fora desta lista de eventos: não exportar HAR
nem partilhar capturas sem sanitização local.

## Hipótese e evidências

Hipótese principal: após o clique, a WebFrame ou o browser tenta navegar para
o MULTICAIXA Express e, ao regressar, a navegação/reinicialização do documento
cross-origin do frame não restaura o conteúdo visual. Também são plausíveis
uma navegação interna bloqueada por CSP, uma falha de protocolo/associação de
aplicação, um redirect/erro dentro da EMIS ou uma condição específica do
browser Samsung. A imagem não permite escolher entre estas causas.

A view não troca o `src` nem abre ou substitui o iframe no handler do clique.
O listener `message` não altera o iframe. A sandbox actual mantém as
permissões de scripts, formulários, mesma origem, pop-ups e navegação de topo
após gesto, sem permitir navegação de topo irrestrita. Não se deve removê-la
nem acrescentar permissões ou protocolos sem evidência do browser e instrução
EMIS.

## Manual EMIS e abertura em contexto de topo

O manual local `pagamentos/GPO Manual de Integração - v2.2.8 1.pdf`, versão
02.80, 2023-07-07, páginas 14 e 18, descreve o token de compra como único por
transacção e demonstra a WebFrame através de uma tag `<iframe>`. A página 18
documenta `postMessage` do host GPO e indica que `event.data` pode conter o ID
da transacção ou ser vazio. Não encontrei no manual uma autorização explícita
para abrir o mesmo URL/token directamente em contexto de topo, nem uma
instrução sobre handoff Android para o MULTICAIXA Express.

Por isso, não foi adicionado um botão externo que reutilize o URL do iframe.
Antes disso, a EMIS deve confirmar por escrito que a WebFrame aceita navegação
top-level e que o comportamento de handoff/retorno é suportado. Como cada
token é único por transacção, o teste comparativo não deve abrir o mesmo token
simultaneamente dentro e fora do iframe. Usar tokens distintos e autorizados
para homologação, sem cobranças reais.

### Comparação controlada A/B

Executar só em ambiente e com dispositivo/conta de homologação autorizados
pela EMIS, com a aplicação MULTICAIXA Express instalada e preparada para
homologação. Não usar produção, cartão ou conta real, e não concluir qualquer
operação financeira.

1. Usar o mesmo aparelho, versão Android, browser, rede, versão/estado da app
   e período de teste. Registar esses metadados sem dados pessoais.
2. **A — iframe:** carregar a rota individual de staging com um token de teste
   dedicado, activar `?gpo_diag=1`, guardar a gravação de ecrã e os eventos
   redigidos. Tocar uma única vez no botão EMIS. Registar se a app abre, se o
   browser regressa e se o iframe carrega novamente. Não completar pagamento.
3. **B — topo:** apenas com autorização explícita da EMIS para esta modalidade,
   usar uma página temporária de staging que navegue para a WebFrame com um
   token de teste diferente e válido, sem exibir ou registar o URL/token. Não
   deixar o mesmo token activo nos dois contextos. Repetir o mesmo toque e
   registar o resultado. A página de teste deve ser removida depois da
   homologação e nunca instalada em produção sem decisão técnica aprovada.
4. Se A falhar e B funcionar, o contexto/sandbox/retorno do iframe torna-se a
   hipótese forte; ainda é necessário validar a proposta com EMIS e testar
   regressão iPhone Safari.
5. Se A e B falharem, comparar erros de protocolo/redireccionamento e a
   associação da app com apoio EMIS e do fabricante. Não criar Android Intents,
   URIs, packages ou endpoints por tentativa.
6. Repetir A no iPhone Safari como controlo de regressão. Recolher resultado
   funcional e browser/OS, sem assumir que o comportamento iOS explica o
   Android.

O clique no botão pode iniciar a operação de pagamento mesmo que não seja
concluída. A EMIS deve fornecer o procedimento de homologação e assegurar que
o caso não liquida cobrança real. Se não existir um fluxo de teste seguro,
limitar a comparação ao carregamento da WebFrame, sem accionar o botão.

## Perguntas para a EMIS

1. A WebFrame e o botão MULTICAIXA Express suportam `iframe` sandboxed nos
   Android Chrome e Samsung Internet? Qual conjunto mínimo de tokens `sandbox`
   é oficialmente suportado?
2. Qual é o mecanismo exacto de handoff e regresso (redirect da janela,
   popup, Android App Link, URI scheme ou outro) e quais as condições de
   activação do utilizador? Fornecer documentação oficial, sem pedir à loja
   que invente um esquema.
3. A EMIS suporta oficialmente abrir a URL `/frame` com token de compra em
   contexto top-level? O token continua válido nesse contexto? Pode haver
   mais de um contexto/navegação por token ou é estritamente um token/uma
   sessão?
4. Após alternar para a aplicação e regressar ao browser, que conteúdo/estado
   deve a WebFrame apresentar? Qual o fluxo suportado se o frame ficar branco
   ou cinzento?
5. Quais origens/redireccionamentos da WebFrame, CSP e política de frames
   precisam de permitir para o fluxo Android? A EMIS consegue correlacionar o
   horário do teste e explicar a cadeia de navegação sem partilhar tokens?
6. Que eventos e payloads `postMessage` são esperados antes/depois do handoff,
   recusa, cancelamento e timeout? O host deve esperar outro evento no retorno
   da aplicação?
7. Quais versões Android, Chrome, Samsung Internet e MULTICAIXA Express estão
   actualmente certificadas para WebFrame?

## Ficheiros e validação

- `resources/views/store/pagamento-gpo.blade.php`: iframe, sandbox, polling e
  instrumentação opcional da página anfitriã.
- `app/Services/GpoService.php`: criação do token e composição do URL
  configurado; não foi alterado para este diagnóstico.
- `app/Http/Middleware/SecurityHeaders.php`: CSP de `frame-src` para EMIS; não
  foi alterado.
- `tests/Feature/GpoFrameDiagnosticsTest.php`: verifica instrumentação,
  redacção e preservação do iframe/CSP.

Rollback da instrumentação: retirar o bloco de diagnóstico desta view e o
teste correspondente. Nenhum serviço/controller financeiro, callback,
referência, confirmação de pagamento ou emissão de voucher é alterado por
esta instrumentação. Não foi feito push, deploy, acesso a produção ou
transacção real.
