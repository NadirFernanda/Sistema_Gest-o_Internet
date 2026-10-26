# Incorporação publicitária AngolaWiFi no portal SOL

## Origens autorizadas

O portal SOL confirmou HTTPS, hostname `na.solnms.net` e portas 443, 8443 e
8445. O parser de `SOL_ADS_FRAME_ORIGINS` em
`app/Http/Middleware/SecurityHeaders.php` separa origens por vírgulas (e
remove espaços em redor de cada valor); espaços sozinhos não são separadores.
Configurar exactamente:

```env
SOL_ADS_FRAME_ORIGINS="https://na.solnms.net,https://na.solnms.net:8443,https://na.solnms.net:8445"
```

O middleware valida origens HTTPS sem path e só aceita estas três origens
confirmadas. A directiva `frame-ancestors` com a allowlist aplica-se somente à
rota `GET /publicidade/portal-wifi`. Homepage, checkout, administração e
pagamento EMIS continuam com `frame-ancestors 'none'` e
`X-Frame-Options: DENY`. Se a variável estiver vazia ou sem uma origem
autorizada, a rota publicitária também falha fechada com esses cabeçalhos.

## Botão «VER A LOJA»

A página publicitária é independente: mostra planos activos, estilos inline e
recursos locais, sem layout da loja, formulários de pagamento ou iframe EMIS.
O botão «VER A LOJA» abre `https://angolawifi.ao/` em resposta directa ao clique,
com `window.open(url, '_blank')` sem a opção `noopener`. Isso permite usar o
retorno para distinguir um bloqueio: quando o browser devolve uma janela, o
código define imediatamente `opener = null`; quando o retorno é nulo (ou a
chamada falha), navega o contexto `_top` para a loja. Não há chamadas de rede,
promessas ou temporizadores antes da tentativa de abertura. Não se tenta a
navegação top-level quando uma nova janela foi aberta, evitando abrir os dois
destinos.

O link visível «Abrir nesta janela» (`target="_top"`) permanece disponível se
o browser ou o portal não permitir detecção/abertura de pop-ups ou se o
JavaScript não carregar. Os links de cada plano também usam `_top` para levar
o cliente ao checkout fora do iframe publicitário. O portal SOL confirmou que
não usa sandbox no iframe, não tem iframes aninhados e permite navegação de
topo.

## Cabeçalhos, proxy e configuração

`SecurityHeaders` é aplicado no grupo web e produz uma única CSP na resposta
Laravel. Na rota publicitária, quando configurada, espera-se uma política com
`script-src 'self'`, `frame-src 'none'` e:

```text
frame-ancestors https://na.solnms.net https://na.solnms.net:8443 https://na.solnms.net:8445
```

`X-Frame-Options` não é enviado apenas nessa resposta porque não suporta uma
allowlist de origens. Nas restantes páginas HTML, a CSP contém
`frame-ancestors 'none'`, `X-Frame-Options: DENY` e mantém
`frame-src https://pagamentonline.emis.co.ao` para o fluxo EMIS existente.
Assim, permitir o portal SOL como ancestral não autoriza a loja, o checkout,
páginas administrativas nem a página EMIS a serem incorporados.

Não existe configuração Nginx versionada neste checkout; `public/.htaccess`
não define CSP nem X-Frame-Options. Não é possível inspeccionar a configuração
activa do Nginx/CDN sem acesso ao servidor. Cabeçalhos adicionais no proxy
podem anular a política da aplicação: CSP é cumulativa e um
`X-Frame-Options: DENY` acrescentado pelo proxy continuará a bloquear a rota.
No servidor, inspeccionar `nginx -T` e criar uma excepção apenas para
`/publicidade/portal-wifi`, se necessário, sem remover protecções das outras
rotas.

Após alterar `.env`, limpar e reconstruir a cache de configuração Laravel:

```sh
php artisan config:clear
php artisan config:cache
```

Verificar a resposta da rota sem seguir redireccionamentos e confirmar que
responde directamente com HTTP 200, os três `frame-ancestors` exactos, um só
`Content-Security-Policy` e sem `X-Frame-Options`:

```sh
curl -sS -D - -o /dev/null https://angolawifi.ao/publicidade/portal-wifi
```

Comparar também a homepage, uma página de checkout, administração e pagamento
EMIS; estas devem manter CSP restritiva e `X-Frame-Options: DENY`. Verificar
no Nginx/CDN os cabeçalhos realmente enviados ao cliente, incluindo duplicados
e redireccionamentos.

## Testes e homologação

`tests/Feature/SolAdvertisingEmbedTest.php` verifica as três origens oficiais,
a rejeição de outras origens e a configuração vazia, o botão e o link de
fallback top-level, a segurança de framing das páginas sensíveis e a
preservação de `frame-src` EMIS. A automatização Laravel verifica os
cabeçalhos da aplicação, não os do proxy ou router.

Após publicação em staging, testar o portal SOL em Android e iOS: confirmar a
apresentação do anúncio, tocar em «VER A LOJA», confirmar que abre apenas a
loja fora do iframe e que a aba aberta não tem acesso a `window.opener`.
Testar também com pop-ups bloqueados e usar «Abrir nesta janela». Confirmar
que os links de plano abrem o checkout no contexto de topo e que o fluxo EMIS
continua no seu checkout normal.

## Publicação e rollback

1. Configurar a variável exacta acima no ambiente do servidor.
2. Publicar o commit, executar `php artisan config:clear` e
   `php artisan config:cache`.
3. Verificar a resposta HTTP, os cabeçalhos efectivos Nginx/CDN e os testes
   reais no portal SOL em staging antes de produção.
4. Para rollback, remover `SOL_ADS_FRAME_ORIGINS` e limpar/reconstruir a cache
   de configuração; a rota publicitária volta a negar framing.

Não fazer push nem deploy sem uma acção explícita de publicação.
