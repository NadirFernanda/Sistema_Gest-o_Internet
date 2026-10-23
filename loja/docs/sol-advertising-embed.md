# Incorporação publicitária AngolaWiFi no portal SOL

## Diagnóstico

As respostas HTML públicas inspeccionadas em produção (`/` e
`/checkout/diario`, GET sem iniciar pagamento) devolvem:

- `Content-Security-Policy` com `frame-ancestors 'none'`;
- `X-Frame-Options: DENY`.

Ambos bloqueiam a incorporação da AngolaWiFi em qualquer iframe, incluindo o
iframe do portal SOL. A política é definida em
`app/Http/Middleware/SecurityHeaders.php` e aplicada no grupo web em
`bootstrap/app.php`. A CSP permite `frame-src` EMIS para páginas AngolaWiFi
incorporarem a WebFrame; isso não autoriza terceiros a incorporar a loja. Não
foi encontrada configuração Nginx versionada no projecto. A resposta pública
tem `Server: nginx/1.18.0 (Ubuntu)`, uma CSP e um X-Frame-Options; isso não
substitui a inspecção do `nginx -T` no servidor para detectar cabeçalhos
adicionais configurados fora do Laravel.

Há um conflito possível se o Nginx/CDN também definir estes cabeçalhos: a
remoção de `X-Frame-Options` feita pela resposta Laravel não remove um cabeçalho
acrescentado depois pelo proxy; um `X-Frame-Options: DENY` duplicado continuará
a bloquear. Da mesma forma, CSP é cumulativa: uma segunda política que contém
`frame-ancestors 'none'` continua a bloquear mesmo quando a CSP Laravel permite
SOL. A configuração activa do servidor não está neste checkout; só `nginx -T`
e os cabeçalhos da rota após deploy podem confirmar este ponto.

As rotas públicas existentes usam cookie de sessão Secure/SameSite=Lax. O
conteúdo publicitário novo não lê nem exige sessão, autenticação ou cookies de
terceiros; assim, o browser pode limitar cookies third-party sem impedir a
apresentação. O middleware de métricas é ignorado na rota de publicidade para
não executar geolocalização externa nem associar essa impressão a uma sessão.

## Implementação isolada

`GET /publicidade/portal-wifi` (`ads.portal-wifi`) serve uma página independente
com planos activos, CSS inline e uma única imagem servida no próprio domínio.
Não carrega JavaScript, Google Fonts, pixels, API de estatísticas, nem inclui o
layout/cabeçalho/rodapé da loja. Os links dos planos usam `target="_top"` para
levar o cliente à loja/checkout de topo após clique. O portal SOL deve permitir
navegação de topo após activação explícita do utilizador no seu iframe; se a
sandbox deles bloquear esta navegação, será necessário acordar com SOL um
comportamento de link alternativo suportado pelo portal.

`target="_blank"` é uma alternativa possível se SOL confirmar que o seu iframe
permite pop-ups (por exemplo, sandbox com `allow-popups`) e que o portal aceita
abrir a loja num novo separador após um clique directo. Nesse caso, usar
`rel="noopener noreferrer"` e testar o comportamento em Android e iOS, incluindo
o bloqueio de pop-ups. A implementação mantém `target="_top"` até a equipa SOL
confirmar qual destas opções é suportada e pretendida; não se altera o destino
de navegação por suposição.

A variável `SOL_ADS_FRAME_ORIGINS` aceita uma lista separada por vírgulas de
origens exactas HTTPS (`scheme + host + porta opcional`, sem path). Deve ser
preenchida somente com a origem oficial fornecida pela equipa SOL. Cada valor
é validado; paths, HTTP, wildcard e portas inválidas são ignorados. Se a
variável estiver vazia ou não contiver origem válida, a rota mantém
`frame-ancestors 'none'` e `X-Frame-Options: DENY` (fail closed).
Se SOL servir o portal dentro de vários frames, `frame-ancestors` tem de
autorizar cada origem ancestral HTTPS da cadeia; pedir a SOL a lista completa,
não apenas o domínio visível no endereço do portal.

Quando a lista é válida, apenas a rota publicitária recebe CSP restrita a
recursos self (imagem local, CSS inline, sem scripts, sem subframes) e
`frame-ancestors` com as origens SOL configuradas. O X-Frame-Options é removido
somente nessa resposta porque `DENY`/`SAMEORIGIN` não suporta a allowlist de
origens; não é removido globalmente. Todas as outras páginas continuam com
`frame-ancestors 'none'` e `X-Frame-Options: DENY`. As páginas de pagamento
continuam a usar `frame-src https://pagamentonline.emis.co.ao`; nenhuma view ou
configuração EMIS foi alterada nesta implementação.

## Domínios necessários na whitelist pré-autenticação

Na página `/publicidade/portal-wifi`, o HTML e os estilos são inline e a imagem
é `/img/logo2.jpeg` no próprio host. Links de plano também apontam para o
próprio host. Assim, a whitelist do dispositivo deve permitir apenas
`angolawifi.ao` por HTTPS para carregar esta página e o seu recurso visual.
Não é necessário autorizar `sg.angolawifi.ao`, Google Fonts, EMIS, WhatsApp ou
outros terceiros para apresentar esta página. Confirmar com SOL como a sua
whitelist trata redireccionamento e destinos top-level após o cliente tocar
num plano; o checkout pode precisar de acesso à rede normal ou de whitelist
adicional que só deve ser decidida após esse teste.

## Testes e homologação

`tests/Feature/SolAdvertisingEmbedTest.php` verifica:

- origem HTTPS SOL configurada permitida, origem não listada ausente da CSP e
  um único cabeçalho CSP;
- configuração vazia/inválida bloqueada por CSP e X-Frame-Options;
- página dedicada sem JS ou CSS externo, com imagem self e links top-level;
- homepage, administração e pagamento EMIS continuam bloqueados contra framing
  externo e a allowlist `frame-src` EMIS permanece intacta.

Estes testes automatizados verificam as respostas Laravel, não um router SOL.
Antes de produção, obter a origem HTTPS exacta e confirmar que não há headers
de clickjacking duplicados no Nginx/CDN (`nginx -T` e `curl -sS -D -` na rota).
Instalar primeiro em staging, testar o iframe e os links em Android Chrome,
Samsung Internet e iPhone Safari, e verificar que o clique em plano abre o
checkout no contexto de topo. O router deve permitir a origem de incorporação
e a navegação top-level por activação de utilizador. Confirmar também a
allowlist pré-autenticação e o retorno à loja.

## Deploy e rollback

1. Não configurar `SOL_ADS_FRAME_ORIGINS` até receber por escrito a origem
   exacta HTTPS oficial do SOL.
2. Fazer deploy do código em staging; verificar a rota sem configuração
   (deve negar framing) e, com origem de homologação autorizada, verificar
   apenas a rota publicitária (sem X-Frame-Options e CSP exacta).
3. Confirmar no Nginx/CDN que não acrescenta `X-Frame-Options: DENY` ou outra
   CSP `frame-ancestors` à rota permitida. Se houver regra global, criar
   excepção apenas para `/publicidade/portal-wifi`, preservando os headers
   restritivos em todas as outras rotas. Confirmar o resultado HTTP efectivo;
   headers repetidos fazem o browser aplicar a política mais restritiva.
4. Após aprovação, configurar a origem SOL exacta, limpar cache de config
   Laravel e repetir o teste HTTP e no router.
5. Rollback: remover `SOL_ADS_FRAME_ORIGINS` (a rota falha fechada) ou reverter
   o deploy. As demais páginas permanecem protegidas em todos os casos.

Não houve deploy, alteração de base de dados, teste físico nem ensaio num
router SOL nesta tarefa. O funcionamento final de incorporação e de links
depende da origem, whitelist e sandbox confirmadas pela equipa SOL.
