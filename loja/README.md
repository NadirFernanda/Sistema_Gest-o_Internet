<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## Arquitectura da Loja — Dois tipos de planos

A loja trata dois tipos de planos de forma completamente diferente:

| | **Planos Individuais** | **Planos Familiares / Empresariais** |
|---|---|---|
| Identificação | Nenhuma | Nome, e-mail, telefone, NIF (opcional) |
| Entrega | Código WiFi imediato no ecrã | Activação de janela no SG |
| Integração SG | Nenhuma | `POST /api/janela-autovenda` |
| Modelo | `AutovendaOrder` | `FamilyPlanRequest` |
| Gateway | `PaymentCallbackController` | `FamilyPlanPaymentController` |

---

## Fluxo — Planos Familiares & Empresariais

### Visão geral

```
Cliente → Formulário → awaiting_payment → Página de pagamento
                                                  ↓
                                          Cliente paga
                                                  ↓
                                  Gateway → POST /payment/familia/webhook
                                                  ↓
                                  Loja → POST /api/janela-autovenda (SG)
                                                  ↓
                                  SG activa o plano → e-mail ao cliente ✅

                                          (se SG falhar)
                                                  ↓
                                  status = pending → admin activa manualmente
```

### Passo a passo

**1. Cliente escolhe o plano**
Os planos familiares/empresariais são carregados em tempo real do SG via `/sg/plan-templates`. O cliente clica "Comprar" e é redirecionado para o formulário.

**2. Formulário de identificação — `GET /solicitar-plano?plan_id=...`**
O cliente preenche: nome, e-mail, telefone, NIF (opcional), método de pagamento (Multicaixa Express ou PayPal).

**3. Submissão — `POST /solicitar-plano`**
O servidor (`FamilyPlanRequestController@store`):
- Valida os dados
- Cria um registo `FamilyPlanRequest` com `status = awaiting_payment`
- Gera uma referência única: `AW-000042` (formato `AW-` + ID com 6 dígitos)
- Envia e-mail de notificação ao admin
- Redireciona para `/pagar-plano/{id}`

> Nenhuma activação ocorre ainda. O pedido fica registado aguardando pagamento.

**4. Página de pagamento — `GET /pagar-plano/{id}`**
Renderizada por `FamilyPlanPaymentController@show`. Mostra instruções específicas por método:
- **Multicaixa Express:** passo a passo com a referência `AW-000042` e valor a pagar
- **PayPal:** botão de redirecionamento com instrução para incluir a referência na nota

Em ambiente `local`/`testing` aparece um botão **"Simular Pagamento Confirmado"** para testes sem gateway real.

**5. Cliente efectua o pagamento**
O cliente paga pelo método escolhido. A partir daqui o processo é 100% automático.

**6. Webhook do gateway — `POST /payment/familia/webhook`** *(CSRF exempt)*
O gateway envia `{ "reference": "AW-000042" }`. O servidor (`FamilyPlanPaymentController@webhook`):
- Localiza o pedido pela referência
- Confirma que está em `awaiting_payment`
- Chama `activate()`

**7. Activação no SG — `POST /api/janela-autovenda`**
A loja chama o SG com os dados do cliente. O SG (`AutovendaJanelaController@store`) executa:

- **Cliente novo:** cria `Cliente` + cria `Plano` (`estado=Ativo`, `proxima_renovacao = hoje + ciclo`)
- **Cliente já existente** (lookup por e-mail): encontra o cliente, verifica se já tem plano desse template
  - Tem plano activo: **estende** `proxima_renovacao` adicionando os dias do ciclo (renovação)
  - Não tem: cria plano novo para esse cliente

**8. Conclusão**
- Loja actualiza `status = activated`
- Envia e-mail de confirmação ao cliente com nome do plano, referência e confirmação de acesso
- A página `/pagar-plano/{id}` passa a mostrar "✅ Plano Activado!"

### Estados do pedido (`FamilyPlanRequest.status`)

| Status | Significado |
|--------|-------------|
| `awaiting_payment` | Pedido registado, aguarda confirmação de pagamento do gateway |
| `pending` | Pagamento confirmado, mas SG inacessível — admin deve activar manualmente |
| `activated` | Janela activada no SG com sucesso |
| `cancelled` | Pedido cancelado pelo admin |

### Fallback manual (admin)

Se o SG estiver inacessível durante o webhook, o pedido fica `pending` com nota explicativa. O admin acede a `/admin/pedidos-planos-familiares` e clica "Confirmar/Activar" para tentar novamente.

### Referências de código

| Componente | Ficheiro |
|---|---|
| Formulário + registo | `app/Http/Controllers/FamilyPlanRequestController.php` |
| Pagamento + webhook + activação | `app/Http/Controllers/FamilyPlanPaymentController.php` |
| API no SG | `PROJECTO/app/Http/Controllers/AutovendaJanelaController.php` |
| Proxy loja→SG | `app/Http/Controllers/StoreProxyController.php` |
| Admin panel | `app/Http/Controllers/Admin/FamilyPlanRequestAdminController.php` |
| Modelo | `app/Models/FamilyPlanRequest.php` |
| Vista formulário | `resources/views/pages/solicitar-plano.blade.php` |
| Vista pagamento | `resources/views/pages/pagar-plano.blade.php` |
| CSRF exemption | `bootstrap/app.php` |
| Rotas | `routes/web.php` — prefixo `/solicitar-plano`, `/pagar-plano`, `/payment/familia` |

---

## Roadmap — Pré-preenchimento por número de telefone (próxima fase)

> **Problema actual:** o cliente preenche nome, telefone, NIF, etc. de cada vez que renova.
>
> **Contexto Angola:** a maioria dos clientes não usa e-mail regularmente. O número de telefone é o identificador universal — toda a gente sabe o seu número de cor.

### Solução proposta: lookup por telefone (sem conta, sem password, sem e-mail)

O cliente não precisa de criar conta. No início do formulário de checkout existe um campo de pesquisa rápida:

```
Já é cliente? Introduza o seu número de telefone:
[ 9XX XXX XXX ]  [ Preencher automaticamente ]
```

Se o número existir na base de dados (de um pedido anterior), o formulário é preenchido automaticamente com os dados guardados. O cliente confirma, escolhe o plano e paga. Se for novo, preenche normalmente e os dados ficam guardados para a próxima vez.

**Vantagens:**
- Zero fricção — o cliente só precisa de saber o seu número de telefone
- Não depende de e-mail nem de password
- Não precisa de "criar conta" — os dados ficam na base de dados automaticamente na primeira compra
- Funciona igualmente bem em telemóvel

**Arquitectura prevista:**
- Na tabela `family_plan_requests`, o `customer_phone` já existe — basta fazer lookup por ele
- `GET /checkout/lookup?phone=9XXXXXXXX` → retorna JSON com dados do cliente (nome, email, nif) para preencher o form via JS
- Nenhuma migração nova necessária na fase inicial
- Opcional numa fase posterior: notificação de renovação por **WhatsApp** (via Twilio/Z-API) em vez de e-mail

---

## Fluxo de Deploy — Local → GitHub → Servidor de Produção

### 1. Publicar alterações do local para o GitHub

```bash
# No computador local (PowerShell ou terminal)
cd c:\Users\Administrator\Documents\SGA-MR.TEXAS\PROJECTO\loja
git add -A
git commit -m "descrição das alterações"
git push origin main
```

### 2. Deploy no servidor de produção (loja)

```bash
# No servidor de produção (SSH)
cd /var/www/sgmrtexas/loja
git fetch origin
git reset --hard origin/main

composer install --no-dev --optimize-autoloader

npm ci
npm run build

php artisan migrate --force

php artisan optimize:clear
php artisan optimize

sudo systemctl restart php8.4-fpm
sudo systemctl reload nginx
```

> **Porquê `git reset --hard origin/main` em vez de `git pull`?**
> O `git pull` falha com "unstaged changes" ou "not possible to fast-forward" quando há divergência entre o servidor e o repositório remoto.
> O `git fetch origin` + `git reset --hard origin/main` garante que o servidor fica **exactamente** igual ao último commit do GitHub, descartando qualquer alteração local no servidor.

### Pré-requisito: sudo sem password para reiniciar serviços

Para que o deploy não fique bloqueado a pedir password do sudo, execute **uma única vez** no servidor:

```bash
echo 'usuario ALL=(ALL) NOPASSWD: /bin/systemctl restart php8.4-fpm, /bin/systemctl reload nginx' | sudo tee /etc/sudoers.d/deploy-loja
```

Substitua `usuario` pelo nome de utilizador SSH real. Verifique com `sudo systemctl restart php8.4-fpm` — não deve pedir password.
