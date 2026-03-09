# Módulo de Autovenda e Revenda – Loja Online AngolaWiFi

Este documento descreve os requisitos funcionais e técnicos do módulo de **Autovenda** (loja online para cliente final) e do módulo de **Agente Revendedor** da Loja AngolaWiFi.

---

## 1. Objetivo do Módulo de Autovenda

Permitir que o internauta final compre **planos de internet LuandaWiFi** de forma **100% autónoma**, através da loja online, com:

- Pagamento digital (Multicaixa Express e PayPal).
- Entrega imediata dos **códigos de acesso WiFi**.
- Entrega dos códigos através de:
  - Tela de confirmação (copy & paste).
  - WhatsApp.
  - E-mail.

---

## 2. Página da Loja (Autovenda)

Página pública acessível a qualquer utilizador, sem necessidade de login para comprar.

### 2.1 Catálogo de Planos

A página deve exibir claramente os planos disponíveis, por exemplo:

- Plano Diário – 24 horas.
- Plano Semanal – 7 dias.
- Plano Mensal – 30 dias.

Para **cada plano**, devem ser mostrados:

- Nome do plano.
- Velocidade / limite de uso (ex.: ilimitado).
- Duração (ex.: 60 minutos, 24 horas, 7 dias, 30 dias).
- Preço em Kz.
- Botão **"Comprar Agora"**.
 - Opcionalmente, uma imagem / thumbnail representando o plano.

Obs.: Os clientes finais compram **1 código por vez**.

---

## 3. Fluxo de Compra (Checkout)

### 3.1 Dados do Cliente (Formulário)

Para o módulo de autovenda existem dois cenários distintos:

**a) Planos individuais rápidos (Hora, Dia, Semana, Mês)**

- Não é exigido qualquer dado pessoal para concluir a compra.
- O cliente seleciona apenas o plano e o método de pagamento.
- O código WiFi é apresentado diretamente na tela de confirmação.
- E-mail/WhatsApp são totalmente opcionais (na implementação atual, não são
  recolhidos para estes 4 planos individuais).

**b) Outros planos / fluxos que exijam identificação**

Nestes casos, o formulário pode solicitar:

- Primeiro e Último Nome.
- E-mail (com validação de formato).
- Telefone / WhatsApp.

Campos opcionais:

- NIF (para emissão de fatura, se desejado).

Regra geral:

- Não é necessário criar conta para comprar (checkout rápido / guest checkout).

### 3.2 Resumo do Pedido

Antes do pagamento, o sistema apresenta um resumo contendo:

- Plano selecionado.
- Quantidade de código (sempre **1 código por compra**).
- Valor total a pagar.

Obs.: A compra é sempre de **um voucher por vez**, tal como acontece em outros sistemas locais (ex.: recarga via Multicaixa Express).

---

## 4. Métodos de Pagamento

O sistema deve suportar os seguintes meios:

### 4.1 Multicaixa Express

- Integração com gateway de pagamento compatível (ex.: EMIS / PSP local).
- Pagamento via app bancária / referência Multicaixa.
- Confirmação automática do pagamento pelo gateway.

### 4.2 PayPal (ou outro sistema internacional)

- Pagamento com saldo PayPal ou cartão internacional.
- O pedido só é confirmado após **pagamento aprovado**.

---

## 5. Entrega Automática do Serviço

Após a **confirmação do pagamento**:

1. O sistema gera automaticamente o **código de acesso WiFi**.
2. Entrega imediata do(s) código(s) via:
   - E-mail (mensagem de texto que permite copy & paste).
   - WhatsApp.
3. Exibição de uma **mensagem de sucesso** na tela (incluindo código e instruções básicas).

### 5.1 Conteúdo mínimo do E-mail

- Agradecimento pela compra.
- Plano adquirido.
- Código(s) de acesso.
- Instruções básicas de utilização.
- Contactos de suporte AngolaWiFi.

---

## 6. Área do Cliente (Autovenda)

Funcionalidades mínimas:

- Histórico de compras.
- Nova compra rápida (atalho para voltar ao catálogo com dados pré-preenchidos quando possível).

---

## 7. Painel Administrativo

Funcionalidades internas do backoffice:

- Dashboard de vendas (diárias / mensais / anuais).
- Gestão de planos e preços.
- Gestão do stock de códigos WiFi.
- Relatórios de vendas por período.
- Estado dos pagamentos (Multicaixa / PayPal).

---

## 8. Fluxo Resumido de Autovenda

1. Cliente entra na loja online.
2. Escolhe o plano de internet.
3. Para planos individuais rápidos (Hora, Dia, Semana, Mês), NÃO é necessário
  preencher dados pessoais: o cliente escolhe apenas o plano e o método de
  pagamento.
4. Para outros fluxos onde seja necessária identificação, o cliente preenche
  os dados básicos (nome, e-mail, telefone/WhatsApp, opcionalmente NIF).
5. Seleciona o método de pagamento.
6. Efetua o pagamento (Multicaixa Express ou PayPal).
7. Sistema confirma o pagamento.
8. Código WiFi é gerado e entregue automaticamente (tela + e-mail + WhatsApp,
  quando aplicável).

---

## 9. Requisitos Técnicos Recomendados (Autovenda)

- Integração com gateway de pagamento local (Multicaixa Express).
- Integração PayPal oficial.
- Sistema de carregamento e gestão automática de códigos WiFi.
- Logs de transações e entregas.
- Design responsivo (mobile-first, Angola-first).

### 9.1 Estados internos da ordem de autovenda

Para suportar os fluxos de pagamento e entrega, cada compra rápida (1 código) é
tratada como uma "ordem de autovenda" com os seguintes estados internos
principais:

- `pending` – ordem recém-criada a partir do checkout, antes da escolha/arranque do pagamento.
- `awaiting_payment` – método de pagamento escolhido (Multicaixa Express ou PayPal) e cliente a caminho do gateway.
- `paid` – pagamento confirmado pelo gateway ou backoffice; a partir daqui o sistema gera e entrega o código WiFi.
- `cancelled` – ordem cancelada (pelo cliente ou operador) antes da confirmação do pagamento.
- `failed` – erro irrecuperável no fluxo de pagamento.
- `expired` – referência/janela de pagamento expirada.

Os métodos de pagamento são registados com identificadores internos, por
exemplo: `multicaixa_express` e `paypal`, permitindo a integração com múltiplos
PSPs sem depender diretamente dos nomes comerciais nas regras de negócio.

---

# Módulo de Agente Revendedor – Loja Online AngolaWiFi

Este módulo complementa a autovenda, permitindo que parceiros revendedores comprem códigos em quantidade, com descontos, para revenda ao cliente final.

## 1. Página de Adesão ("Quero ser revendedor")

Ao clicar no ícone/botão **"Quero ser revendedor"**, o sistema exibe um formulário de adesão com os campos obrigatórios:

- Nome Completo.
- Nº do BI ou NIF.
- Morada Completa.
- E-mail (com validação de formato).
- Telefone / WhatsApp.
- Local de Instalação Pretendido (dropdown com Províncias/Municípios ou campo texto livre).
- Assunto (fixo): `Quero ser agente revendedor` (pré-preenchido e não editável).
- Corpo da mensagem (fixo):

  > Saudações prezados,  
  > Venho pelo intermédio deste manifestar o interesse para ser agente revendedor do serviço AngolaWiFi.

**Botão:** `Enviar Pedido`.

### 1.1 Funcionamento

- Ao enviar, os dados devem:
  - Ser enviados por e-mail para o administrador.
  - Ser registados na base de dados do e-commerce.
- O candidato recebe automaticamente uma mensagem informando que o pedido está em análise, juntamente com os **requisitos para ser revendedor**.

---

## 2. Condições de Revenda

Exibidas na própria página de adesão (antes do botão de submissão), incluindo:

- O revendedor pode adquirir quantos códigos desejar, sem limite.
- O sistema aplica **descontos percentuais** sobre o valor dos códigos, conforme escalão:
  - 1.º escalão: 10.000,00 Kz → 10% de desconto.
  - 2.º escalão: 20.000,00 Kz → 15% de desconto.
  - 3.º escalão: 30.000,00 Kz → 20% de desconto.
  - 4.º escalão: 40.000,00 Kz → 25% de desconto.
  - 5.º escalão: acima de 100.000,00 Kz → 40% de desconto.
- Compra mínima: **10.000 Kz**.
- Após a compra, os créditos são disponibilizados em **formato CSV** na conta do revendedor.
- O revendedor pode revender ao cliente final na quantidade que desejar (1 código ou mais).

---

## 3. Área do Revendedor (após aprovação)

Painel de controlo exclusivo, contendo:

- Saldo de créditos (em Kz e nº de códigos disponíveis).
- Histórico de compras (download dos ficheiros CSV e/ou PDF).
- Comprar novos códigos (reutilizando o checkout da loja, com **desconto aplicado automaticamente**).
- Extratos / relatórios (datas, quantidades, clientes atendidos).

---

## 4. Fluxo Resumido do Módulo Revendedor

1. Interessado preenche o formulário de adesão.
2. Dados são enviados para a administração e gravados na base de dados.
3. Administração valida o pedido.
4. Após validação, o utilizador passa a ter acesso à **Área de Revenda**.
5. Compra mínima de 10.000 Kz, com desconto de acordo ao escalão.
6. O sistema gera um CSV com códigos, disponível no painel do revendedor.
7. O revendedor revende os códigos ao cliente final.

---

# Regra de Negócio – Renovação Automática de Janela

Sempre que o cliente efetuar o pagamento de um plano de internet através da loja online, o sistema deve:

1. Confirmar automaticamente o pagamento (referência, transferência ou gateway).
2. Identificar o plano **ativo** do cliente.
3. Acrescentar automaticamente a janela correspondente ao período pago (ex.: +30 dias num plano mensal).
4. Atualizar a nova data de expiração.
5. Enviar notificação automática (WhatsApp + e-mail).
6. Atualizar o estado do cliente para **"Ativo"** caso estivesse suspenso por falta de pagamento.

---

# Observações de Escopo

- **Planos individuais / dispositivos:** conforme instruções de Autovenda (acima).
- **Planos familiar / empresas:** alinhar com a loja da LuandaWiFi já existente.
- **Loja de equipamentos / produtos:** fluxo convencional de e-commerce.
- **Agente revendedor:** conforme especificação deste documento.

Este documento serve como base para a implementação progressiva dos módulos de Autovenda e Revenda na aplicação `loja` (Laravel).

---

# Arquitetura de Integração – Loja x Sistema de Gestão de Planos

Esta secção documenta como a aplicação `loja` (Laravel) se integra com o sistema
de gestão de planos (core), com os gateways de pagamento e com o backoffice
administrativo.

## 1. Papéis de cada sistema

- **Loja (Laravel `loja`):**
  - Expor o catálogo público (autovenda, planos família/empresas em leitura).
  - Gerir o fluxo de checkout, ordens de autovenda e revenda.
  - Integrar com gateways de pagamento (Multicaixa Express, PayPal).
  - Enviar notificações ao cliente (e-mail, WhatsApp).
  - Disponibilizar um backoffice próprio de e-commerce (ordens, revendedores,
    relatórios).

- **Sistema de Gestão de Planos (core):**
  - Fonte de verdade dos planos familiares/empresariais.
  - Gestão de contas/clientes, janelas de acesso, expirações.
  - Implementar as regras de renovação automática de janela.
  - (Opcional) Gestão central do stock de códigos WiFi.

## 2. Planos família/empresas – leitura via API do core

Os planos individuais da autovenda podem continuar definidos localmente na loja
(`config/store_plans.php`).

Os planos familiares e empresariais, por sua vez, devem ser carregados sempre
do sistema de gestão, através de uma API dedicada.

### 2.1 Exemplo de endpoint no core

`GET /api/plans`

Query params recomendados:

- `type` – filtros de tipo de plano, por exemplo `family`, `business`.

Resposta (exemplo simplificado):

```json
{
  "data": [
    {
      "id": "fam_10mb_3users",
      "name": "Plano Familiar 10 Mbps (3 utilizadores)",
      "type": "family",
      "speed": "10 Mbps",
      "max_users": 3,
      "duration_days": 30,
      "price_kwanza": 15000,
      "description": "Plano familiar com até 3 utilizadores simultâneos."
    }
  ]
}
```

### 2.2 Consumo na loja

Na aplicação `loja`, um serviço dedicado (ex.: `CorePlansService`) usa HTTP
com autenticação (token, API key ou OAuth) para chamar o endpoint acima e
preencher a secção de **Planos Familiares & Empresariais**.

Benefícios:

- Alterações de planos no core refletem automaticamente na loja.
- A lógica de negócio dos planos complexos permanece centralizada no core.

## 3. Pagamentos – visão de alto nível

Fluxo completo, do ponto de vista da loja:

1. Cliente escolhe plano na loja e preenche dados (autovenda ou revenda).
2. Loja cria uma `AutovendaOrder` (ou ordem de revenda) com `status =
   awaiting_payment`.
3. Loja chama o gateway de pagamento (Multicaixa Express / PayPal) e obtém
   uma `payment_reference` e/ou `checkout_url`.
4. Loja grava esses dados na ordem e redireciona o cliente para o ambiente do
   PSP ou apresenta instruções de pagamento.
5. O PSP confirma o pagamento através de um callback/webhook para a loja.
6. A loja valida o callback, marca a ordem como `paid` (ou `failed/expired`).
7. Para clientes geridos no core, a loja chama a API de confirmação de
   pagamento do sistema de gestão de planos (ver secção 4).
8. Após confirmação do core, a loja gera/recebe o código WiFi, marca a ordem
   como sincronizada com o core e envia as notificações definitivas.

## 4. Integração loja → sistema de gestão (renovação de janela)

Quando o pagamento é confirmado na loja, é necessário informar o core para que
este aplique a regra de **Renovação Automática de Janela**.

### 4.1 Endpoint sugerido no core

`POST /api/hotspot/payments/confirm`

Body (JSON) sugerido:

```json
{
  "order_id": "autovenda-12345",
  "customer_identifier": "cliente_abc",  
  "plan_code": "mensal_30d_10mb",      
  "amount": 10000,
  "currency": "AOA",
  "payment_method": "multicaixa_express",
  "payment_gateway_ref": "EMIS-REF-XYZ",
  "paid_at": "2026-03-05T14:23:00Z"
}
```

Onde:

- `order_id` – identificador interno da ordem na loja (para rastreio cruzado).
- `customer_identifier` – identificador do cliente no core (ID interno,
  username, NIF ou telefone, conforme modelo do core).
- `plan_code` – código do plano no core (não precisa ser igual ao `plan_id`
  da loja, mas deve ser mapeável).
- `amount` / `currency` – valor efetivamente pago.
- `payment_method` – `multicaixa_express`, `paypal`, etc.
- `payment_gateway_ref` – referência do PSP (EMIS, PayPal, etc.).
- `paid_at` – data/hora do pagamento.

Resposta de sucesso esperada:

```json
{
  "status": "ok",
  "customer_id": "cliente_abc",
  "plan_code": "mensal_30d_10mb",
  "new_expiration": "2026-04-05T23:59:59Z",
  "window_added_days": 30
}
```

Resposta de erro (exemplo):

```json
{
  "status": "error",
  "code": "CUSTOMER_NOT_FOUND",
  "message": "Cliente não encontrado no sistema de gestão."
}
```

### 4.2 Regra de negócio dentro do core

O core, ao receber `payments/confirm`, deve:

1. Validar o pagamento (integridade dos dados, idempotência).
2. Identificar o plano ativo do cliente.
3. Acrescentar automaticamente a janela correspondente ao período pago (ex.:
   +30 dias num plano mensal).
4. Atualizar a data de expiração.
5. Atualizar o estado do cliente para **"Ativo"** caso estivesse suspenso.
6. Opcionalmente, registar logs internos e/ou disparar notificações próprias.

Na loja, o resultado desta chamada é usado para atualizar a ordem com o estado
de sincronização com o core.

## 5. Integração com gateways de pagamento (PSP)

Cada gateway (Multicaixa Express, PayPal, etc.) terá os seus próprios
endpoints e formatos, mas o padrão recomendado na loja é:

- Rota para iniciar pagamento (chamada a partir do checkout):
  - Ex.: `POST /checkout` → cria ordem e chama o PSP.
- Rotas de callback/retorno:
  - Ex.: `POST /payment/multicaixa/callback` (webhook/autorização oficial).
  - Ex.: `POST /payment/paypal/webhook`.
  - Ex.: `GET /payment/paypal/return` (quando o utilizador volta ao site).

Em todas as rotas de callback da loja:

- Validar assinaturas e tokens fornecidos pelo PSP (segurança).
- Localizar a ordem usando a referência do gateway.
- Atualizar `status` da ordem (`paid`, `failed`, `expired`, `cancelled`).
- Em caso de sucesso (`paid`), chamar o endpoint do core descrito na secção 4.

## 6. Backoffice próprio da loja

O módulo administrativo da loja deve oferecer uma visão clara sobre o estado
das integrações e dos fluxos de negócio.

Funcionalidades recomendadas:

- **Painel de Autovenda:**
  - Lista de ordens de autovenda (filtros por data, plano, estado,
    método de pagamento).
  - Indicadores de sincronização com o core (ex.: `sync_status`, `synced_at`).
  - Ação de "forçar" re-sincronização com o core em caso de falha.
- **Painel de Revenda:**
  - Pedidos de revenda recebidos, estados (`pending`, `approved`, `rejected`).
  - Gestão dos revendedores aprovados.
  - Compras de créditos/códigos com aplicação dos descontos por escalão.
- **Integrações:**
  - Logs de chamadas aos PSPs (request/response resumido, estado).
  - Logs de chamadas ao core (sucesso/erro) para auditoria e suporte.

## 7. Notificações ao cliente

As notificações ao cliente podem ser disparadas pela loja, pelo core ou por
ambos. Recomenda-se centralizar a lógica de mensagem na loja para manter a
experiência consistente com o front-office.

Padrão sugerido:

- **Após pagamento confirmado e janela aplicada no core:**
  - Loja envia e-mail com:
    - Confirmação da compra.
    - Plano adquirido.
    - Código(s) de acesso WiFi e/ou instruções de login.
    - Nova data de expiração (se aplicável).
  - Loja envia mensagem via WhatsApp (quando a integração estiver disponível)
    com resumo similar em formato compacto.

## 8. Considerações de segurança e operação

- Todas as chamadas internas (loja ↔ core, loja ↔ PSP) devem usar HTTPS.
- Autenticação entre loja e core:
  - Token de API com rotação periódica ou OAuth2 client credentials.
- Logs de integração devem omitir/anonimizar dados sensíveis (por exemplo,
  dados pessoais em texto livre).
- Devem existir mecanismos de idempotência no core para evitar aplicar a mesma
  janela duas vezes em caso de reenvio de callbacks.
- A loja deve implementar rate limits mínimos nas rotas públicas de checkout e
  nas rotas de callback, quando aplicável.

---

Esta arquitetura garante que a loja online se mantém focada na experiência de
compra, enquanto o sistema de gestão central continua responsável pela regra
de negócio crítica dos planos e janelas. A presente secção serve como
especificação de referência para a equipa que irá implementar as integrações
entre estes dois mundos.

Fulxo de minha conta 
Como usar na prática

Ir para /minha-conta pelo menu da loja.
Informar o email que usou nas compras (nos casos em que forneceu email).
Ver o histórico de compras associadas àquele email e, se quiser, clicar em “Comprar novamente” para ir direto ao checkout do respetivo plano.