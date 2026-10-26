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

A página publicitária é independente: mostra uma campanha elegível gerida no
painel AngolaWiFi ou, quando não existe campanha válida, os planos activos.
Usa estilos inline e recursos locais, sem formulários de pagamento ou iframe
EMIS. No formulário de campanhas, seleccionar o placement «Portal WiFi / SOL»
torna obrigatório definir um limite de frequência; campanhas inactivas,
agendadas fora do período, sem imagem disponível ou com bónus próprio sem
stock elegível não são apresentadas.
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

### Métricas do placement SOL

A impressão é enviada apenas depois de pelo menos 50% do cartão estar visível,
através de um pixel GIF da própria AngolaWiFi (`img-src 'self'`). Não usa
`fetch`, `connect-src`, cookies, dados pessoais nem dependência de sessão de
terceiro. Os URLs de impressão e clique são assinados por 30 dias, o que
permite que uma página SOL permaneça aberta por mais de 15 minutos sem perder
o tracking. São relativos à mesma origem, pelo que não dependem do
esquema/host observado pelo proxy e continuam cobertos por `img-src 'self'`;
a CSP não bloqueia navegação de links. A resposta HTML e o redirect de clique
usam `Cache-Control: no-store, private`. As assinaturas dependem da `APP_KEY`
e deixam de validar se esta for rodada.

Cada nonce é aceite uma só vez, por `Cache::add` atómico, e a chave de
deduplicação tem TTL de 31 dias (prazo do link + um dia de margem). Para o
cache Laravel baseado em base de dados, `ads:prune-sol-tracking-cache` remove
diariamente as chaves SOL expiradas; o scheduler Laravel precisa estar activo
no servidor. Assim, o volume tende a ficar próximo de 31 dias de chaves
únicas — por exemplo, 3.000 eventos/dia representam cerca de 93.000 chaves,
em vez de milhões acumulados por anos. Outros stores com TTL nativo expiram
as chaves segundo o respectivo backend. Os endpoints também têm limite de
pedidos.

A campanha tem de continuar activa/elegível: desactivação, fim do período ou
limite agregado atingido podem fazer a impressão ou clique falhar, mesmo que a
página permaneça aberta. Cliques redireccionam para o destino HTTP/HTTPS
guardado na campanha e não aceitam um destino fornecido pelo navegador.

A deduplicação SOL cobre a repetição do mesmo token; o limite configurado na
campanha é um máximo agregado por período, não uma frequência individual por
internauta. Há ainda uma deduplicação local best-effort por campanha e
separador durante 30 minutos, usando `sessionStorage` sem enviar um
identificador; pode não estar disponível em todos os captive browsers.
Não se cria identificador de utilizador para compensar cookies bloqueados.
Em produção, o cache usado pela aplicação deve ser partilhado por todas as
instâncias (a configuração padrão Laravel deste projecto usa cache de base de
dados), para que a deduplicação atómica de nonces funcione entre nós. A
contagem agregada usa a chave única campanha/período existente e uma operação
atómica `INSERT IGNORE`/`ON CONFLICT DO NOTHING` seguida de
`UPDATE ... WHERE impressions_count < frequency_limit`, dentro de transacção.
Isto faz o próprio motor de base de dados arbitrar actualizações concorrentes
e impede ultrapassar o limite. O comportamento foi coberto na suite com
pedidos e nonces distintos; a confirmação sob concorrência real ainda depende
de validar o motor/configuração efectivamente usado em produção.

### Acesso antes da autenticação WiFi

A página, imagem, JavaScript e os endpoints de impressão/clique são todos
servidos pela mesma origem HTTPS da loja: `angolawifi.ao` (TCP 443). O
JavaScript só carrega a impressão quando o anúncio fica visível; clique e
redireccionamento só ocorrem após interacção. Não há pedidos a domínios
externos para renderizar a campanha. O portal/captive gateway SOL tem de
permitir DNS e HTTPS para esse hostname antes da autenticação e não pode
bloquear os caminhos `/publicidade/portal-wifi`, `/ads/{id}/image`,
`/js/sol-ads.js`, `/ads/sol/{id}/impression/{nonce}` e
`/ads/sol/{id}/click/{nonce}`. Se a loja redireccionar para outro hostname
(por exemplo `www`), esse hostname e o redirect também precisam de ser
confirmados.

Uma whitelist SOL limitada apenas por domínio/endereços IP não consegue
restringir o acesso a caminhos: pode tornar outros caminhos públicos na rede
captive, embora a CSP continue a impedir a incorporação da homepage,
administração e pagamentos. Se a SOL exigir isolamento de rede por caminho,
será necessária uma regra L7 ou um hostname dedicado à publicidade, a acordar
com a equipa de rede SOL. O destino do anunciante não é automaticamente
permitido; a sua disponibilidade antes da autenticação depende da política
SOL para esse destino.

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
preservação de `frame-src` EMIS. `tests/Feature/SolAdCampaignIntegrationTest.php`
verifica o placement, campanhas elegíveis, fallback para planos, criativo,
links, assinatura sem expiração de 15 minutos, deduplicação e limite de
frequência com nonces distintos. A automatização Laravel verifica os cabeçalhos
da aplicação, não os do proxy ou router; o bloqueio efectivo pré-autenticação
precisa de ser confirmado na configuração do captive gateway SOL.

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
