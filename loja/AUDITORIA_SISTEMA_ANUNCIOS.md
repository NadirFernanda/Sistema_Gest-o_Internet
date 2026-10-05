# Auditoria e sistema de anúncios — AngolaWiFi

**Data:** 4 de Outubro de 2026
**Preço de desenvolvimento:** **350.000 AOA**

## Objectivo

Implementar um gestor de campanhas da loja com dois usos: divulgar gratuitamente promoções próprias da AngolaWiFi e apresentar anúncios pagos de empresas externas. O sistema não é uma rede social nem uma plataforma de leilão publicitário: não inclui perfis de utilizadores, segmentação individual, orçamento automático ou cobrança integrada.

## Onde colocar anúncios

| Página | Posição recomendada | Porquê |
| --- | --- | --- |
| Página inicial (`/`) | Abaixo do destaque principal e das estatísticas, antes dos planos individuais | Dá visibilidade a campanhas próprias e anúncios sem interromper o fluxo de compra dos planos. |
| Catálogo de equipamentos (`/equipamentos`) | Antes da grelha de produtos | Mantém campanhas num contexto relacionado com conectividade, acessórios e equipamentos. |

Não colocar anúncios no checkout, pagamento, confirmação de encomenda, conta do cliente, suporte ou painel do revendedor. Nessas páginas o utilizador está a concluir uma tarefa; publicidade pode causar distracção, reduzir confiança e prejudicar conversões. A posição da página inicial apresenta o anúncio em destaque abaixo do carrossel e pode abrir uma janela promocional após alguns segundos, no máximo uma vez por sessão, com controlos para fechar; não se usam intersticiais noutras páginas.

## Como funciona

- A equipa administradora cria e gere campanhas em `/admin/anuncios`.
- Cada campanha contém tipo, título, descrição, imagem, destino, texto do botão, posição e período de activação.
- **Campanha própria AngolaWiFi:** gratuita, criada para promover os próprios planos, produtos e ofertas. O anunciante é identificado como AngolaWiFi e não existe cobrança pela veiculação.
- Uma campanha própria pode também oferecer um voucher WiFi gratuito: a administração selecciona um ou mais planos de compra elegíveis e o plano do voucher bónus. A oferta elegível é apresentada no checkout; após confirmação do pagamento, o código bónus é atribuído do stock e incluído na confirmação, e-mail e WhatsApp.
- **Publicidade de anunciante:** campanha de uma empresa externa, com o nome do anunciante, para venda comercial de espaço publicitário.
- Os dois tipos usam o mesmo gestor, posições e métricas. A etiqueta pública distingue “Campanha promocional AngolaWiFi” de “Publicidade · [anunciante]”.
- A campanha começa pausada e só aparece quando activada e dentro das datas definidas.
- As campanhas elegíveis numa posição são escolhidas aleatoriamente. Para compras abrangidas por mais de uma promoção, aplica-se a campanha própria elegível mais recente.
- O painel apresenta impressões, cliques e CTR calculado a partir desses totais.
- A imagem é carregada no servidor da loja; o destino aceita URLs HTTP/HTTPS.
- As imagens públicas são servidas por uma rota da aplicação, sem depender de um link simbólico de armazenamento no servidor.
- As rotas administrativas são protegidas pelo middleware `sg-admin`.

O prémio implementado é um segundo código de acesso WiFi de um plano existente, não um saldo monetário numa carteira do cliente ou do SG. A validade do bónus é a validade do plano de voucher seleccionado. A oferta é registada na encomenda quando esta é criada; se a campanha terminar enquanto o cliente conclui o pagamento, a oferta dessa encomenda mantém-se. É necessário carregar stock de códigos do plano bónus. Se o stock se esgotar entre checkout e confirmação de pagamento, a encomenda é assinalada no painel de recargas para intervenção da equipa.

## Métricas e privacidade

Uma impressão é contabilizada quando pelo menos metade do anúncio entra no ecrã. A mesma campanha não volta a contar para a mesma sessão durante 30 minutos. Os cliques são contabilizados quando o utilizador segue o link de encaminhamento.

O sistema não guarda endereços IP, perfis, localização ou comportamento de navegação para publicidade e não utiliza pixels de terceiros. As métricas são agregadas e indicativas; não garantem vendas nem alcance único. Robôs, bloqueadores e cliques repetidos podem afectar os números. A Política de Privacidade foi actualizada para descrever a contagem das campanhas.

## Preço de desenvolvimento

**Preço proposto para o desenvolvimento do sistema: 350.000 AOA.**

O valor refere-se à implementação do gestor comum para campanhas próprias gratuitas, bónus de voucher, anúncios externos, posições, contagem de impressões e cliques e painel de métricas. O preço cobrado aos anunciantes pela veiculação é separado e deve ser definido com base no tráfego real e na procura comercial; não há dados suficientes para estabelecer uma tabela de publicidade fiável antes de um piloto.

## Operação

1. Aplicar as migrações no deploy com `php artisan migrate --force`.
2. Confirmar que `public/storage` está ligado a `storage/app/public` (`php artisan storage:link`) para disponibilizar as imagens carregadas.
3. Para uma campanha com bónus, seleccionar os planos de compra elegíveis e o plano voucher a oferecer; confirmar que há stock disponível.
4. Criar a campanha em `/admin/anuncios`, rever imagem, destino e regras e activá-la quando estiver pronta.
5. Verificar no painel de recargas as encomendas pagas com bónus sem código, caso o stock do plano bónus se esgote.
6. Acompanhar as métricas durante um piloto e confirmar que são coerentes com o tráfego real das páginas.

## Limites desta primeira versão

Não inclui gestão directa pelo anunciante, pagamento integrado, orçamento automático, segmentação por perfil/localização, anúncios em vídeo, saldo monetário ou crédito na carteira do SG, relatórios avançados por período nem garantia de impressões ou vendas. A definição do preço comercial das campanhas deve aguardar dados reais de tráfego.

## Guia para utilizadores

As instruções passo a passo para criar campanhas e ofertas estão em [`docs/guia-campanhas-anuncios.md`](docs/guia-campanhas-anuncios.md).
