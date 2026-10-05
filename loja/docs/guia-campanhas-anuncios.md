# Guia do utilizador: campanhas e anúncios

Este guia explica como a equipa AngolaWiFi pode criar campanhas próprias, oferecer vouchers WiFi como bónus, gerir anúncios de empresas externas e consultar os resultados.

## 1. Aceder ao gestor

1. Entre no painel administrativo da loja com a sua conta autorizada.
2. Abra **Anúncios** no painel ou aceda a `/admin/anuncios`.
3. Na lista verá as campanhas, o tipo, a posição, o período, o estado e as métricas.
4. Seleccione **+ Nova campanha** para criar uma campanha.

Só utilizadores autorizados no painel administrativo podem gerir campanhas.

## 2. Escolher o tipo de campanha

### Campanha própria AngolaWiFi — gratuita

Use este tipo para divulgar produtos, planos, novidades e promoções da própria AngolaWiFi. Não há cobrança pela veiculação. O anúncio aparece identificado publicamente como **Campanha promocional AngolaWiFi**.

Exemplo de divulgação: “Oferta especial: compre o Plano X e receba um voucher WiFi de 24 horas.”

Uma campanha própria pode ser apenas informativa ou pode oferecer um voucher automaticamente após uma compra elegível.

### Publicidade de anunciante

Use este tipo para uma empresa ou marca externa que contratou um espaço publicitário. Indique o nome do anunciante. O anúncio aparece identificado como **Publicidade · [nome do anunciante]**.

O painel gere a publicação e as métricas, mas não recebe pagamentos nem cobra automaticamente o anunciante. O preço, o período contratado e a facturação devem ser acordados e tratados fora do sistema.

## 3. Preencher os dados da campanha

Preencha os campos apresentados:

| Campo | O que indicar |
| --- | --- |
| Tipo de campanha | Promoção gratuita AngolaWiFi ou publicidade de anunciante. |
| Nome do anunciante | Obrigatório para campanhas externas. As campanhas próprias são identificadas como AngolaWiFi. |
| Posição | Página inicial ou catálogo de equipamentos. |
| Título | Mensagem principal, curta e clara. |
| Descrição | Detalhes da oferta ou do produto, se necessário. |
| Imagem | Ficheiro JPG, PNG ou WebP, até 5 MB. |
| Link de destino | Para anunciantes externos, indique o endereço HTTP/HTTPS da página de destino. Em campanhas próprias é opcional: se ficar vazio, campanhas com bónus levam ao checkout do primeiro plano elegível; as restantes levam à página inicial. |
| Texto do botão | Por exemplo, “Saber mais”, “Ver oferta” ou “Comprar”. |
| Início e fim | Datas opcionais para programar a campanha. |
| Campanha activa | Marque apenas quando a campanha estiver revista e pronta para publicação. |

Uma campanha nova começa desactivada. Enquanto estiver desactivada, fora das datas definidas ou sem uma posição elegível, não aparece aos visitantes. Para interromper temporariamente uma campanha, edite-a e desmarque **Campanha activa**.

O botão de imagem usa as opções **Escolher ficheiro** e **Nenhum ficheiro seleccionado** em português. Ao seleccionar uma imagem, o nome do ficheiro aparece junto ao botão.

## 4. Criar uma oferta com voucher WiFi gratuito

Esta opção está disponível apenas para **campanhas próprias AngolaWiFi**:

1. Seleccione **Campanha própria AngolaWiFi**.
2. Marque **A campanha oferece um voucher WiFi gratuito após uma compra**.
3. Seleccione um ou mais **planos de compra elegíveis** — os produtos que dão direito à oferta.
4. Seleccione o **voucher WiFi gratuito a entregar**.
5. Escreva no título e na descrição as condições da oferta de forma explícita.
6. Antes de activar a campanha, confirme que há códigos disponíveis em **Administração → WiFi Codes** para o plano de voucher escolhido.
7. Guarde e active a campanha.

**Exemplo:** seleccionar “Plano Semanal” como plano elegível e “Voucher Diário” como bónus. O cliente verá a oferta no checkout do Plano Semanal e, se concluir o pagamento, receberá o código do plano semanal comprado e um segundo código diário gratuito.

O bónus é um **voucher WiFi adicional**, não saldo monetário ou crédito numa carteira. A validade é a que está definida no plano do voucher. Não é possível prometer uma quantidade de saldo em dinheiro com esta função.

### Como o cliente recebe o bónus

- A oferta é apresentada no checkout antes de o cliente pagar.
- A campanha elegível fica associada à encomenda quando esta é criada. Se a campanha terminar depois disso, a oferta dessa encomenda mantém-se.
- Depois de o pagamento ser confirmado, o sistema reserva do stock um código do plano comprado e, separadamente, um código do plano bónus.
- O código bónus é mostrado na página de confirmação e incluído no e-mail e WhatsApp, quando esses canais foram fornecidos pelo cliente.
- Se houver mais de uma campanha própria elegível para o plano comprado, é aplicada a campanha criada mais recentemente.

**Importante:** o stock é necessário tanto para o plano comprado como para o plano bónus. Se o pagamento for confirmado, mas já não houver stock do voucher bónus, o pagamento e o código comprado continuam válidos; a encomenda fica assinalada no painel de recargas para a equipa resolver manualmente o bónus e contactar o cliente.

## 5. Onde os anúncios aparecem

- **Página inicial:** abaixo do destaque principal e das estatísticas, antes dos planos individuais.
- **Equipamentos:** antes da lista de produtos.

O sistema pode escolher aleatoriamente entre campanhas activas e elegíveis para a mesma posição. Não são apresentados anúncios no checkout, na página de pagamento, na confirmação, na conta do cliente, no suporte nem no painel do revendedor. A oferta de voucher, quando aplicável, é mostrada no checkout do plano elegível como informação da promoção, não como um espaço publicitário geral.

## 6. Consultar resultados

Na lista de campanhas, cada linha apresenta:

- **Impressões:** contagem técnica quando pelo menos metade do anúncio entra no ecrã. A mesma campanha não volta a contar na mesma sessão durante 30 minutos.
- **Cliques:** cliques que passam pelo link de encaminhamento do anúncio.
- **CTR:** percentagem calculada como `cliques ÷ impressões × 100`.

As métricas são indicativas; não representam utilizadores únicos e não garantem vendas. Bloqueadores de anúncios, tráfego automático e cliques repetidos podem afectar os totais. O sistema não usa essas métricas para segmentar pessoas.

## 7. Editar ou eliminar

- **Editar:** permite corrigir a mensagem, datas, posição, regras da oferta ou imagem. Ao substituir a imagem, a anterior é removida.
- **Desactivar:** mantém os dados da campanha e as métricas, mas deixa de a mostrar aos visitantes.
- **Eliminar:** remove a campanha e as métricas associadas. Uma encomenda já criada mantém os dados do voucher bónus nela registados para que a oferta possa ser concluída.

## 8. Lista de verificação antes de publicar

- O tipo de campanha está correcto?
- A imagem e o link de destino foram revistos?
- A posição e o período estão correctos?
- A mensagem deixa claras as condições da promoção?
- Para uma oferta com voucher, foram escolhidos os planos elegíveis e o plano de bónus?
- Há stock disponível de códigos para o voucher bónus?
- A campanha está activa somente quando deve ser publicada?
