# Validação Android da WebFrame EMIS

## Correcção local

As quatro páginas de pagamento mantêm a WebFrame EMIS em iframe, como no
manual GPO disponível no repositório. A única alteração de navegação deste
patch acrescenta `allow-top-navigation-by-user-activation` à sandbox:

- `resources/views/store/pagamento-gpo.blade.php`
- `resources/views/pages/pagar-plano-gpo.blade.php`
- `resources/views/reseller/pagamento-gpo.blade.php`
- `resources/views/reseller/manutencao-gpo.blade.php`

O token permite que conteúdo embutido navegue o contexto de topo apenas depois
de uma activação explícita do utilizador. Não remove a sandbox, não permite
navegação irrestrita, não altera a URL/token EMIS e não constrói deep links.
`allow-popups` já estava presente; a especificação HTML também o considera
permissão para navegação a protocolos personalizados. Não foi acrescentada uma
permissão redundante nem um esquema URI não confirmado pela EMIS.

Chrome Android e Safari iOS têm mecanismos distintos de abertura de aplicações
externas. O Safari funcionar demonstra que o handoff funciona nesse ambiente,
mas não demonstra que o caminho de navegação usado pela Frame satisfaz as
restrições de activação do Chrome Android. A permissão acrescentada remove uma
restrição real de navegação de topo do iframe; não corrige um protocolo/URL
inválido, ausência da aplicação, bloqueio do browser ou perda do gesto de
utilizador dentro do fluxo EMIS. O código da loja não pode inspeccionar os
documentos cross-origin da Frame para provar qual destes casos ocorre.

O aviso de cookies é um overlay fixo no fundo e só aparece quando não existe
consentimento guardado. Pode interceptar um toque apenas se cobrir fisicamente
a área tocada. Não foi alterado: comparar o mesmo caso com o aviso visível e
oculto, registando se o clique alcançou a Frame.

## Teste automatizado

`GpoAndroidFramePermissionsTest` verifica nas quatro views que a sandbox
preserva scripts, formulários, origem e pop-ups, acrescenta navegação de topo
dependente de gesto e não concede `allow-top-navigation` irrestrito. Este teste
estático não simula Android nem substitui a homologação.

## Procedimento Android real

Executar apenas em staging, com token e credenciais de homologação EMIS:

1. Instalar/aprovar MULTICAIXA Express no dispositivo Android de teste e abrir
   a loja no Chrome. Registar modelo, versão Android/Chrome e estado da app.
2. Confirmar no DOM renderizado que o iframe da WebFrame tem o atributo
   `sandbox` corrigido; confirmar que o checkout é uma página normal e que não
   há modal a criar a Frame. Repetir com aviso de cookies visível e aceite.
3. Ligar o Android por USB a uma estação autorizada, activar depuração USB e
   usar `chrome://inspect/#devices`. Abrir DevTools para o separador da loja,
   activar Preserve log em Console e Network, limpar os registos e iniciar o
   caso de homologação.
4. Tocar uma vez em **ABRIR MULTICAIXA EXPRESS**. Registar gravação de ecrã,
   hora do clique, se o toque ocorreu dentro da Frame, mudança para a app e
   erros da consola relacionados com sandbox, user activation, CSP, popup,
   protocolo ou navegação. Em Network, registar host/status/redirects sem
   exportar query strings ou tokens.
5. Repetir no Samsung Internet. A inspecção remota depende da versão e do
   suporte do browser; se não estiver disponível, guardar vídeo/logs do aparelho
   e recolher console com suporte do fabricante. Não assumir que
   `chrome://inspect` inspecciona Samsung Internet.
6. Repetir em iPhone Safari para regressão, no mesmo ambiente de homologação.
   Comparar apenas o resultado funcional, não presumir que os motores/browser
   têm as mesmas regras de navegação para apps.
7. Fazer casos com app instalada e ausente, cancelamento e interrupção de rede.
   Estes casos validam o handoff/UX; não concluir pagamento nem emitir voucher
   com base no retorno do browser. Validar sucesso financeiro separadamente
   pelo mecanismo EMIS seguro e já homologado.

Sanitizar evidências antes de partilhar: remover tokens da Frame, cookies,
headers de autorização, dados bancários, telefone, identificadores de sessão e
referências não necessárias. O clique e a abertura da app devem ser comprovados
pela gravação; erros de protocolo/redireccionamento devem ser correlacionados
com console/diagnóstico EMIS.

## Estado

Automatizado: validação estática dos quatro atributos de sandbox.

Pendente: execução em Android Chrome/Samsung Internet e iPhone real, staging
EMIS, captura do erro/handoff e confirmação pela EMIS do mecanismo de abertura
usado. Não declarar o problema resolvido até estes testes demonstrarem a
abertura no Android. Esta correcção não altera controladores financeiros,
referências, callbacks, confirmação ou entrega.
