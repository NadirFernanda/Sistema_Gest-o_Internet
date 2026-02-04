---

## Deploy e Atualização em Produção (Passo a Passo)

### 1. Acesse o servidor e entre na pasta do sistema

```bash
ssh usuario@SEU_SERVIDOR
cd /var/www/sgmrtexas
```

### 2. Baixe as últimas alterações do repositório

```bash
git pull
```

### 3. Instale/atualize dependências PHP

```bash
composer install --no-dev --optimize-autoloader
```

### 4. Instale/atualize dependências do front-end (Vite)

```bash
npm install
npm run build
```

### 5. Gere a chave da aplicação (se necessário)

```bash
php artisan key:generate
```

### 6. Execute as migrações do banco de dados

```bash
php artisan migrate --force
```

### 7. Gere o link de storage

```bash
php artisan storage:link
```

### 8. Limpe e gere o cache de configs, rotas e views

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 9. Ajuste permissões das pastas (se necessário)

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 10. Reinicie serviços PHP-FPM e Nginx (se necessário)

```bash
sudo systemctl restart php8.2-fpm
sudo systemctl reload nginx
```

### 11. Agende o cron do Laravel Scheduler

```bash
crontab -e
# Adicione (ou confirme) esta linha:
* * * * * php /var/www/sgmrtexas/artisan schedule:run >> /dev/null 2>&1
```

Pronto! O sistema estará atualizado e rodando em produção.
## Sistema de Gestão de Internet (Laravel)

> Sistema de gestão de clientes, planos, cobranças, estoque de equipamentos e alertas, desenvolvido em Laravel 12 com Blade.


## Tecnologias

- PHP ^8.2
- Laravel ^12
- Blade (views)
- Composer
- Node.js / npm (para Vite e assets front‑end)
- Banco de dados relacional (MySQL/PostgreSQL/SQLite, conforme configuração do `.env`)

---

## Funcionalidades Principais

- Autenticação com sessão nativa do Laravel (login/logout)
- Proteção de rotas administrativas (`/dashboard`, `/clientes`, `/planos`, `/alertas` etc.)
- Layout com partials (header e sidebar)
- Gestão de clientes
- Gestão de planos
- Relatório de cobranças
- Estoque de equipamentos
- Alertas de vencimento de planos

---

## Fluxos do Sistema (Resumo)

### 1. Login e Acesso
- Acesse `/login` e entre com seu e‑mail e senha cadastrados.
- Após login, o utilizador é redirecionado para `/dashboard`.
- Todas as áreas administrativas exigem autenticação.

### 2. Dashboard
- Tela inicial após login, com atalhos para Clientes, Planos, Alertas, Estoque de Equipamentos e Relatórios.
- Menu de Relatórios para acesso ao relatório de cobranças.

### 3. Gestão de Clientes
- Listagem de clientes com BI, nome, contacto e ações.
- Cadastro, edição e exclusão de clientes.
- Ficha individual de cada cliente.

### 4. Gestão de Planos
- Listagem de planos por cliente, com descrição, preço, ciclo, datas e status.
- Cadastro, edição e remoção de planos.
- Exibição de status com badge colorido.


### 5. Relatório de Cobranças
- Listagem e filtros por cliente, descrição, status, valor e datas.
- Exportação para Excel.
- **Relatórios automáticos:** O sistema gera e envia automaticamente relatórios de cobranças em três períodos:
  - **Diário:** Relatório das cobranças do dia, enviado por e-mail e salvo em `storage/app/relatorios`.
  - **Semanal:** Relatório das cobranças da semana atual, enviado por e-mail e salvo em `storage/app/relatorios`.
  - **Mensal:** Relatório das cobranças do mês atual, enviado por e-mail e salvo em `storage/app/relatorios`.
  - Os comandos são agendados via Laravel Scheduler e podem ser executados manualmente:
    - `php artisan relatorio:cobrancas-diario`
    - `php artisan relatorio:cobrancas-semanal`
    - `php artisan relatorio:cobrancas-mensal`
  - O e-mail de envio é definido pela variável `MAIL_FROM_ADDRESS` no `.env`.

### 6. Estoque de Equipamentos
- Cadastro e gestão de equipamentos em estoque (nome, descrição, modelo, número de série, quantidade).
- Exportação do estoque para Excel.

### 7. Alertas
- Visualização de alertas de vencimento de planos próximos.
- Filtros por quantidade de dias.

---

## Instalação e Execução (Ambiente Local)

### 1. Clonar o repositório

```bash
git clone <URL-DO-REPOSITORIO>
cd PROJECTO
```

### 2. Arquivo de ambiente

```bash
cp .env.example .env   # em Windows, pode usar copy .env.example .env
```

Configure as variáveis de ambiente no ficheiro `.env` (base de dados, e‑mail, etc.).

### 3. Dependências PHP

```bash
composer install
```

### 4. Chave da aplicação

```bash
php artisan key:generate
```

### 5. Migrações

Configure o banco de dados no `.env` e depois execute:

```bash
php artisan migrate
```

Opcionalmente, utilize os seeders para popular dados iniciais.

### 6. Dependências front‑end (Vite)

```bash
npm install
npm run dev   # ou npm run build em produção
```

### 7. Servidor de desenvolvimento

```bash
php artisan serve
```

Acesse no navegador:

- http://localhost:8000

> Dica: o projeto já possui um script de conveniência no `composer.json` chamado `setup`, que automatiza parte desses passos. Use com cuidado em ambiente local:
>
> ```bash
> composer run setup
> ```

---

## Deploy em Produção (Exemplo: Ubuntu + Nginx + PostgreSQL)

Este é um guia resumido do passo a passo utilizado para subir o sistema em produção no domínio `sgmrtexas.isp-bie.ao`, com PostgreSQL como base de dados. Adapte nomes de usuário, senhas e caminhos conforme o seu servidor.

### 1. DNS

- Criar um registo `A` no provedor de DNS:
  - Host: `sgmrtexas`
  - Tipo: `A`
  - TTL: `3600`
  - Valor (IP): `89.167.5.159`

### 2. Preparar o servidor (Ubuntu)

Instalar PHP, Nginx, Git e extensões necessárias:

```bash
sudo apt update
sudo apt install -y nginx git php8.2 php8.2-fpm php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-pgsql
```

Instalar Composer (se ainda não existir):

```bash
cd /usr/local/bin
sudo curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar composer
```

### 3. PostgreSQL (base de dados de produção)

Instalar PostgreSQL e criar base/usuário:

```bash
sudo apt install -y postgresql postgresql-contrib

sudo -u postgres psql
CREATE DATABASE sgmrtexas OWNER sgmr_user ENCODING 'UTF8';
CREATE USER sgmr_user WITH PASSWORD 'senha_forte_aqui';
GRANT ALL PRIVILEGES ON DATABASE sgmrtexas TO sgmr_user;
\q
```

### 4. Clonar o projeto no servidor

```bash
cd /var/www
sudo git clone https://github.com/SEU_USUARIO/SEU_REPOSITORIO.git sgmrtexas
cd sgmrtexas
sudo chown -R $USER:$USER .
```
> Em produção, recomenda-se usar **SSH** em vez de HTTPS para não precisar digitar usuário/senha ou token a cada deploy. Ver secção "Acesso ao GitHub via SSH no servidor" mais abaixo.

### 5. Configurar o `.env` para produção (PostgreSQL)

```bash
cp .env.example .env
```

Editar o ficheiro `.env` com:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sgmrtexas.isp-bie.ao

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sgmrtexas
DB_USERNAME=sgmr_user
DB_PASSWORD=senha_forte_aqui
```

Configurar também MAIL_*, QUEUE_CONNECTION, etc., conforme o ambiente.

### 6. Instalar dependências e preparar a aplicação

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan storage:link

php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Ajustar permissões para o servidor web:

```bash
sudo chown -R www-data:www-data /var/www/sgmrtexas/storage /var/www/sgmrtexas/bootstrap/cache
sudo find /var/www/sgmrtexas/storage -type d -exec chmod 775 {} \;
sudo find /var/www/sgmrtexas/bootstrap/cache -type d -exec chmod 775 {} \;
```

### 7. Configurar o Nginx

Criar o virtual host:

```bash
sudo nano /etc/nginx/sites-available/sgmrtexas.isp-bie.ao
```

Exemplo de configuração:

```nginx
server {
  listen 80;
  server_name sgmrtexas.isp-bie.ao;

  root /var/www/sgmrtexas/public;
  index index.php index.html;

  add_header X-Frame-Options "SAMEORIGIN";
  add_header X-Content-Type-Options "nosniff";

  charset utf-8;

  location / {
    try_files $uri $uri/ /index.php?$query_string;
  }

  location ~ \.php$ {
    include snippets/fastcgi-php.conf;
    fastcgi_pass unix:/run/php/php8.2-fpm.sock;
  }

  location ~ /\.ht {
    deny all;
  }
}
```

Ativar o site e recarregar o Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/sgmrtexas.isp-bie.ao /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 8. HTTPS com Let’s Encrypt (recomendado)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d sgmrtexas.isp-bie.ao
```

Escolher a opção que força redirecionamento HTTP → HTTPS.

### 9. Cron e filas (se usado)

**Agendador (cron):**

```bash
crontab -e
```

Adicionar:

```cron
* * * * * php /var/www/sgmrtexas/artisan schedule:run >> /dev/null 2>&1
```

**Queue worker (Supervisor, opcional):**

```bash
sudo apt install -y supervisor
sudo nano /etc/supervisor/conf.d/sgmrtexas-queue.conf
```

Conteúdo:

```ini
[program:sgmrtexas-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/sgmrtexas/artisan queue:work --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/sgmrtexas-queue.log
```

Ativar:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start sgmrtexas-queue:*
```

---

## Acesso ao GitHub via SSH no servidor (deploy sem pedir senha)

Para que o servidor de produção consiga fazer `git clone` e `git pull` do repositório privado sem pedir usuário/senha, foi configurado acesso via **SSH key**. O fluxo é o seguinte:

### 1. Gerar chave SSH no servidor

No servidor (como usuário de deploy, ex.: `usuario`):

```bash
cd ~
ssh-keygen -t ed25519 -C "deploy-sgmrtexas"
```

- Quando perguntar o caminho do ficheiro, pode aceitar o padrão (`/home/usuario/.ssh/id_ed25519`).
- Quando perguntar passphrase, pode deixar em branco (Enter duas vezes) para uso automático.

### 2. Copiar a chave pública

Ainda no servidor:

```bash
cat ~/.ssh/id_ed25519.pub
```

Copiar a **linha inteira** que começa com `ssh-ed25519` (por exemplo, contendo o sufixo `deploy-sgmrtexas`).

### 3. Adicionar a chave à conta GitHub

Na interface web do GitHub, logado como o utilizador que tem acesso ao repositório:

1. Acesse `Settings` (menu da foto de perfil).
2. No menu lateral, clique em **SSH and GPG keys**.
3. Clique em **New SSH key**.
4. Preencha:
   - **Title**: um nome descritivo, ex.: `isp-bie servidor`.
   - **Key type**: `Authentication Key`.
   - **Key**: cole a linha copiada do `id_ed25519.pub`.
5. Clique em **Add SSH key** e confirme a senha, se solicitado.

Se o repositório estiver numa organização (ex.: `Sistema_Gest-o_Internet`), pode ser necessário ainda **autorizar a chave para essa organização** (Enable SSO), seguindo o link que aparece após adicionar a chave.

### 4. Testar a conexão SSH a partir do servidor

De volta ao servidor:

```bash
ssh -T git@github.com
```

- Na primeira vez, pode perguntar se deseja confiar na chave do GitHub. Responda `yes`.
- Se tudo estiver correto, a saída esperada é algo como:

  ```text
  Hi NOME_DE_USUARIO! You've successfully authenticated, but GitHub does not provide shell access.
  ```

### 5. Clonar o repositório usando SSH

Com a chave autorizada, o clone em `/var/www` passa a ser feito via SSH, sem pedir usuário/senha:

```bash
cd /var/www
sudo git clone git@github.com:SISTEMA_OU_USUARIO/SGA-MR-TEXAS.git sgmrtexas
sudo chown -R usuario:usuario sgmrtexas

cd /var/www/sgmrtexas
ls artisan
```

Depois disso, segue-se normalmente com a configuração do `.env`, instalação de dependências e migrações.

---

## Autenticação

- Acesse `/login` para entrar no sistema.
- Após login, o utilizador é redirecionado para `/dashboard`.
- Todas as páginas administrativas exigem autenticação.
- Para sair, use o botão de logout (requisição POST para `/logout`).

---

## Estrutura de Layout (Views)

- Layout base: `resources/views/layouts/app.blade.php`
- Partials: `resources/views/layouts/partials/header.blade.php` e `resources/views/layouts/partials/sidebar.blade.php`
- Páginas principais estendem o layout base, utilizando `@extends('layouts.app')`.

---

## Comandos Úteis

- Rodar testes de aplicação (PHPUnit / Artisan Test):

  ```bash
  composer test
  ```

- Ambiente de desenvolvimento integrado (servidor, fila, logs e Vite):

  ```bash
  composer dev
  ```

- Setup rápido (instala dependências, gera `.env`, chave, migrações e build):

  ```bash
  composer run setup
  ```

---

## Boas Práticas adotadas no Repositório

- Arquivo `.env` e variações **não são versionados** (`.gitignore` configurado).
- Pastas `vendor/` e `node_modules/` fora do versionamento Git.
- Arquivos de cache, logs e builds (`public/build`, `storage/*.key`, etc.) ignorados.
- `.gitattributes` configurado para normalizar final de linha (EOL) e facilitar diffs de PHP, Blade, CSS, HTML e Markdown.

Para colaboração, recomenda‑se ainda no GitHub:

- Proteger a branch principal (`main`/`master`) exigindo pull requests.
- Ativar verificação em 2 fatores (2FA) na conta.
- Manter ao menos um e‑mail secundário verificado, para recuperação de acesso.

---

## Histórico detalhado das configurações do repositório público

Esta secção resume, passo a passo, as principais configurações feitas neste repositório público desde a criação até o CI.

### 1. Criação do repositório e push do código

1. Repositório criado no GitHub, configurado como **público**.
2. Projeto Laravel existente na pasta local `PROJECTO` foi ligado ao repositório remoto com os comandos (exemplo):

cd /var/www/sgmrtexas
git pull
npm install
npm run build
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
  ```

### 2. Configuração de arquivos de controle (.gitignore e .gitattributes)

1. O arquivo `.gitignore` foi configurado para:
  - Ignorar arquivos sensíveis de ambiente: `.env`, `.env.backup`, `.env.production`.
  - Ignorar dependências: `vendor/` (Composer) e `node_modules/` (npm).
  - Ignorar artefatos de build e cache: `public/build`, `public/hot`, `public/storage`, `/storage/*.key`, `/storage/pail`, caches e logs.
  - Ignorar configurações de IDE/editor: `.idea`, `.vscode`, `.fleet`, `.zed`, etc.

2. O arquivo `.gitattributes` foi configurado para:
  - Forçar normalização de fim de linha: `* text=auto eol=lf` (evita conflitos entre Windows/Linux/Mac).
  - Melhorar visualização de diffs para tipos de ficheiros específicos: Blade, CSS, HTML, Markdown e PHP.
  - Excluir alguns ficheiros de distribuição (como `.github` e `CHANGELOG.md`) em exportações (usar `export-ignore`).

### 3. Organização e documentação do projeto (README)

1. O `README.md` original padrão do Laravel foi substituído por uma documentação específica deste sistema, contendo:
  - Descrição do sistema de gestão de internet (clientes, planos, cobranças, estoque e alertas).
  - Tecnologias utilizadas (PHP 8.2, Laravel 12, Blade, Composer, Node/Vite, BD relacional).
  - Resumo dos fluxos principais (login, dashboard, gestão de clientes, planos, cobranças, estoque, alertas).
  - Passo a passo de instalação em ambiente local (clone, `.env`, `composer install`, `php artisan key:generate`, migrações, `npm install`, `npm run dev`, `php artisan serve`).
  - Comandos úteis (`composer test`, `composer dev`, `composer run setup`).
  - Secção de boas práticas de repositório (esta secção).

### 4. Configuração de integração contínua (GitHub Actions)

1. Foi criada a pasta de workflows do GitHub Actions:

  - `.github/`
  - `.github/workflows/`

2. Foi adicionado o workflow de CI em `.github/workflows/ci.yml` com as seguintes características:
  - Disparo em `push` e `pull_request` para as branches `main` e `master`.
  - Runner `ubuntu-latest` com **PHP 8.2**.
  - Serviço de base de dados **MySQL 8.0** configurado como container.
  - Instalação de dependências PHP via `composer install`.
  - Cópia automática de `.env.example` para `.env` no ambiente de CI.
  - Geração de `APP_KEY` com `php artisan key:generate`.
  - Configuração das variáveis de conexão ao MySQL no `.env` do CI.
  - Execução de migrações com `php artisan migrate --force`.
  - Execução dos testes automatizados com `composer test`.

Com isso, a cada **push** ou **pull request**, o GitHub valida automaticamente se o projeto ainda compila e se os testes passam.

### 5. Recomendações de segurança e colaboração na conta GitHub

Embora estas configurações sejam feitas diretamente na interface do GitHub (e não no código), foram definidas as seguintes boas práticas recomendadas:

1. **Proteção da branch principal** (`main`/`master`):
  - Exigir pull requests para qualquer alteração na branch principal.
  - (Opcional) Exigir pelo menos uma aprovação de reviewer antes do merge.

2. **Segurança da conta**:
  - Ativar autenticação em dois fatores (2FA) na conta GitHub do proprietário.
  - Manter pelo menos **um e‑mail secundário verificado** para recuperação de acesso, conforme aviso do próprio GitHub.

3. **Colaboração**:
  - Adicionar colaboradores como `Write` ou `Maintain` apenas para pessoas de confiança.
  - Usar pull requests para histórico claro de revisões e auditoria.

---

Para dúvidas técnicas do framework, consulte também a [documentação oficial do Laravel](https://laravel.com/docs).
Na imagem, o botão "Exportar Excel" está visível na interface de cobranças, mas ele serve para exportação manual do relatório filtrado na tela.

Os relatórios automáticos (diário, semanal, mensal) que implementamos NÃO aparecem como botões na interface. Eles são gerados automaticamente pelo sistema e:

São salvos na pasta storage/app/relatorios do servidor.
São enviados por e-mail para o endereço configurado no .env.
Resumo:

O botão "Exportar Excel" é para exportação manual.
Os relatórios automáticos não têm botão na interface, pois são gerados e enviados automaticamente pelo agendamento do Laravel (Scheduler).
Se quiser que esses relatórios automáticos também apareçam como botões para download na interface, posso implementar isso para você! Deseja adicionar esses botões na tela?

