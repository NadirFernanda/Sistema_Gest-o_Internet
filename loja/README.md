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

## Planos Disponíveis


### Planos Disponíveis

#### Planos Individuais
| Plano | Preço | Duração | Velocidade | Download | Descrição |
|---|---|---|---|---|---|
| Diário | 200 Kz | 24 horas | Até 10 Mbps | Ilimitado | Internet para o dia inteiro, perfeito para quem precisa de conectividade contínua durante 24h. |
| Semanal | 500 Kz | 7 dias | Até 10 Mbps | Ilimitado | Plano de 7 dias para utilização recorrente, ideal para estudantes e profissionais. |
| Mensal | 1.000 Kz | 30 dias | Até 10 Mbps | Ilimitado | Plano de 30 dias com acesso estável e previsível, para uso contínuo em casa ou no escritório. |

#### Planos Familiares
| Plano | Preço | Duração | Velocidade | Descrição |
|---|---|---|---|---|
| Família 6MBPS | 27.500 Kz | 30 dias | 6 Mbps | Ideal para famílias de até 6 membros no agregado |
| Família 8MBPS | 32.500 Kz | 30 dias | 8 Mbps | Ideal para famílias com até 8 membros no agregado |
| Família 10MBPS | 35.750 Kz | 30 dias | 10 Mbps | Ideal para assistir streaming, como Netflix, canais de TV online etc... |

#### Planos Empresariais
| Plano | Preço | Duração | Velocidade | Descrição |
|---|---|---|---|---|
| Empresarial 25MBPS | 195.000 Kz | 30 dias | 25 Mbps | Ideal para micro e pequenas empresas. Taxa de instalação sob consulta. |
| Empresarial 50MBPS | 312.000 Kz | 30 dias | 50 Mbps | Ideal para médias empresas. Taxa de instalação sob consulta. |
| Empresarial 100MBPS | 561.000 Kz | 30 dias | 100 Mbps | Ideal para grandes empresas. Taxa de instalação sob consulta. |

#### Planos Institucionais
| Plano | Preço | Duração | Velocidade | Descrição |
|---|---|---|---|---|
| Institucional +150MBPS | 2.908.583 Kz | 30 dias | +150 Mbps | Ideal para instituições do Estado. Taxa de instalação sob consulta. |


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

## Configuração Inicial do Servidor VPS (fresh install)

> Use este processo quando configurar um **novo servidor** a partir do zero.
> Para actualizações de código num servidor já configurado, veja a secção **Fluxo de Deploy** abaixo.

### Infra-estrutura de produção

| Item | Valor |
|---|---|
| Fornecedor | Hetzner Cloud |
| Plano | CX23 |
| IP | `89.167.23.38` |
| OS | Ubuntu 22.04 / 24.04 LTS |
| Utilizador SSH | `fernanda` |
| Directório do SG | `/var/www/sgmr` |
| Directório da loja | `/var/www/sgmr/loja` |
| Loja (domínio) | `https://angolawifi.ao` |
| SG (domínio) | `https://sg.angolawifi.ao` |

### Stack instalada pelo script

| Componente | Versão |
|---|---|
| PHP | 8.4 (php8.4-fpm) |
| Servidor web | Nginx |
| Base de dados | MySQL 8.x |
| Node.js | 20 LTS |
| Gestor de pacotes PHP | Composer |

### Passo a passo

**1. Aceder ao servidor por SSH**

```bash
ssh fernanda@89.167.23.38
```

**2. Descarregar e executar o script de configuração**

```bash
curl -fsSL https://raw.githubusercontent.com/NadirFernanda/Sistema_Gest-o_Internet/main/loja/tools/setup-vps.sh -o setup-vps.sh
bash setup-vps.sh
```

O script vai pedir de forma interactiva:
- IP/domínio do servidor
- Nome da base de dados, utilizador e password MySQL
- Password root do MySQL (para criar a BD)
- URL e credenciais do SG (Sistema de Gestão)
- Token de acesso ao painel admin da loja (`SG_LOJA_ADMIN_TOKEN`)
- Configurações de e-mail SMTP

No final instala todas as dependências, clona o repositório, gera a `APP_KEY`, corre as migrações e configura o Nginx + php-fpm.

**3. Verificar que está tudo a funcionar**

```bash
systemctl status nginx
systemctl status php8.4-fpm
curl -s -o /dev/null -w "%{http_code}" http://89.167.23.38
# Deve devolver 200
```

**4. (Opcional) Configurar domínio e certificado SSL**

Se tiver um domínio apontado para o servidor:

```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d loja.seudominio.com
# Editar APP_URL no .env:
nano /var/www/sgmrtexas/loja/.env
# APP_URL=https://loja.seudominio.com
php artisan optimize:clear && php artisan optimize
```

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

### 2a. Deploy no servidor de produção (Sistema de Gestão — SG)

> ⚠️ **Fazer SEMPRE antes do deploy da loja.** O SG expõe as APIs que a loja consome (planos, templates, equipamentos, clientes). Se o SG tiver código novo mas migrações por aplicar, as APIs devolvem 500 e as secções da loja mostram ⚠️.

```bash

cd /var/www/sgmr
git fetch origin
git reset --hard origin/main

composer install --no-dev --optimize-autoloader

php artisan migrate --force   # ← CRÍTICO: aplica migrações novas (ex: coluna tipo em plan_templates)

php artisan optimize:clear
php artisan optimize

sudo systemctl restart php8.4-fpm
sudo systemctl reload nginx
```

### 2b. Deploy no servidor de produção (Loja)

```bash
# No servidor de produção (SSH)
cd /var/www/sgmr/loja
git fetch origin
git reset --hard origin/main

composer install --no-dev --optimize-autoloader

npm ci
npm run build

php artisan migrate --force

php artisan optimize:clear
php artisan optimize
php artisan config:clear

sudo systemctl restart php8.4-fpm
sudo systemctl reload nginx
```

> **Porquê `git reset --hard origin/main` em vez de `git pull`?**
> O `git pull` falha com "unstaged changes" ou "not possible to fast-forward" quando há divergência entre o servidor e o repositório remoto.
> O `git fetch origin` + `git reset --hard origin/main` garante que o servidor fica **exactamente** igual ao último commit do GitHub, descartando qualquer alteração local no servidor.

### Pré-requisito: sudo sem password para reiniciar serviços

Para que o deploy não fique bloqueado a pedir password do sudo, execute **uma única vez** no servidor:

```bash
echo 'fernanda ALL=(ALL) NOPASSWD: /bin/systemctl restart php8.4-fpm, /bin/systemctl reload nginx' | sudo tee /etc/sudoers.d/deploy-angolawifi
```

Verifique com `sudo systemctl restart php8.4-fpm` — não deve pedir password.

### Resolução de erros de permissões no deploy

Se o deploy falhar com erros de `Permission denied` em `.git/objects`, `storage/` ou `bootstrap/cache/` (acontece quando um `composer install` ou `php artisan` anterior correu como `root` ou `www-data`):

```bash
# Corrigir dono e permissões de toda a pasta do projecto
sudo chown -R fernanda:www-data /var/www/sgmr
sudo find /var/www/sgmr -type d -exec chmod 775 {} \;
sudo find /var/www/sgmr -type f -exec chmod 664 {} \;

# Garantir escrita em storage e bootstrap/cache pelo processo web
sudo chmod -R 775 /var/www/sgmr/storage /var/www/sgmr/bootstrap/cache
```

Depois re-correr o deploy normalmente.

---

## Migração para servidor já configurado (código já clonado)

> Use este processo quando o servidor já tem o código clonado (`git pull` feito) e o `.env` do SG configurado, mas falta configurar a loja e o Nginx.

### O que o script `configure-novo-servidor.sh` faz

1. Pede as credenciais da loja de forma interactiva
2. Cria `/var/www/sgmrtexas/loja/.env` com `APP_URL=https://angolawifi.ao`
3. Corre `composer install`, `key:generate`, `npm run build`, migrações e `optimize`
4. Cria dois virtual hosts Nginx:
   - `angolawifi.ao` → `/var/www/sgmrtexas/loja/public`
   - `sg.angolawifi.ao` → `/var/www/sgmrtexas/public`
5. Instala certbot e obtém certificados SSL (Let's Encrypt) para ambos os domínios
6. Configura `sudo` sem password para deploys futuros

### Pré-requisitos

- PHP 8.4, Nginx, PostgreSQL, Node 20, Composer já instalados (use `setup-vps.sh` numa instalação limpa)
- Código clonado em `/var/www/sgmr` e `/var/www/sgmr/loja`
- `.env` do SG (`/var/www/sgmr/.env`) já configurado
- Base de dados PostgreSQL já migrada do servidor antigo
- DNS dos domínios `angolawifi.ao` e `sg.angolawifi.ao` já a apontar para o IP `89.167.23.38`

### Executar

```bash
ssh fernanda@89.167.23.38
sudo -i

curl -fsSL https://raw.githubusercontent.com/NadirFernanda/Sistema_Gest-o_Internet/main/loja/tools/configure-novo-servidor.sh -o configure-novo-servidor.sh
bash configure-novo-servidor.sh
```

### Verificar após o script

```bash
curl -s -o /dev/null -w "%{http_code}" https://angolawifi.ao
# Deve devolver 200
curl -s -o /dev/null -w "%{http_code}" https://sg.angolawifi.ao
# Deve devolver 200

systemctl status nginx
systemctl status php8.4-fpm
```

### Se o certbot falhar (DNS ainda a propagar)

```bash
certbot --nginx -d angolawifi.ao -d www.angolawifi.ao
certbot --nginx -d sg.angolawifi.ao
```
