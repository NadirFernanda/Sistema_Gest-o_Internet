#!/usr/bin/env bash
# =============================================================================
#  configure-novo-servidor.sh
#  Configura a Loja (angolawifi.ao) e o SG (sg.angolawifi.ao) num servidor
#  que já tem o código clonado e o .env do SG configurado.
#
#  Pré-requisitos no servidor:
#    - git pull já feito nos dois projectos
#    - /var/www/sgmrtexas/.env já configurado (SG)
#    - PHP 8.4, Nginx, PostgreSQL, Node 20, Composer já instalados
#
#  Executar como root (ou utilizador sudo):
#    bash configure-novo-servidor.sh
# =============================================================================
set -e

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; CYAN='\033[0;36m'; NC='\033[0m'
ok()   { echo -e "${GREEN}[OK]${NC} $*"; }
info() { echo -e "${YELLOW}[>>]${NC} $*"; }
err()  { echo -e "${RED}[!!] $*${NC}"; exit 1; }
hdr()  { echo -e "\n${CYAN}══ $* ══${NC}"; }

[[ $EUID -ne 0 ]] && err "Execute como root: sudo bash configure-novo-servidor.sh"

# ── Caminhos ───────────────────────────────────────────────────────────────
SG_DIR="/var/www/sgmrtexas"
LOJA_DIR="/var/www/sgmrtexas/loja"
PHP_VERSION="8.4"

[[ -d "$SG_DIR" ]]   || err "Directório $SG_DIR não encontrado. Certifique-se de que o repositório foi clonado."
[[ -d "$LOJA_DIR" ]] || err "Directório $LOJA_DIR não encontrado."

SG_ENV="${SG_DIR}/.env"

# ── Função auxiliar: lê valor de uma variável do .env ──────────────────────
env_get() {
    local key="$1" file="$2"
    grep -E "^${key}=" "$file" 2>/dev/null | head -1 | cut -d'=' -f2- | tr -d '"' | tr -d "'"
}

# =============================================================================
# 1. Recolher dados
# =============================================================================
hdr "Configuração da Loja — .env"
echo ""

# ── Ler BD do .env do SG (evitar erros de digitação) ──────────────────────
if [[ -f "$SG_ENV" ]]; then
    SG_DB_NAME=$(env_get DB_DATABASE "$SG_ENV")
    SG_DB_USER=$(env_get DB_USERNAME "$SG_ENV")
    SG_DB_PASS=$(env_get DB_PASSWORD "$SG_ENV")
    echo -e "${CYAN}Valores lidos do .env do SG (${SG_ENV}):${NC}"
    echo -e "  DB_DATABASE = ${GREEN}${SG_DB_NAME}${NC}"
    echo -e "  DB_USERNAME = ${GREEN}${SG_DB_USER}${NC}"
    echo -e "  DB_PASSWORD = ${GREEN}(preenchido)${NC}"
    echo ""
fi

read -rp  "Nome da base de dados PostgreSQL da loja [${SG_DB_NAME:-loja_angolawifi}]: " DB_NAME
DB_NAME=${DB_NAME:-${SG_DB_NAME:-loja_angolawifi}}
read -rp  "Utilizador PostgreSQL da loja [${SG_DB_USER:-lojauser}]: " DB_USER
DB_USER=${DB_USER:-${SG_DB_USER:-lojauser}}
read -rsp "Password PostgreSQL do utilizador '$DB_USER' [enter para usar o do .env do SG]: " DB_PASS; echo ""
DB_PASS=${DB_PASS:-$SG_DB_PASS}

echo ""
read -rp  "URL do SG (Sistema de Gestão) [https://sg.angolawifi.ao]: " SG_URL
SG_URL=${SG_URL:-https://sg.angolawifi.ao}
read -rp  "SG_CLIENT_ID da loja: " SG_CLIENT_ID
read -rsp "SG_CLIENT_SECRET da loja: " SG_CLIENT_SECRET; echo ""
read -rsp "SG_LOJA_ADMIN_TOKEN: " SG_ADMIN_TOKEN; echo ""

echo ""
read -rp  "E-mail remetente (ex: noreply@angolawifi.ao): " MAIL_FROM
read -rp  "Nome remetente [AngolaWiFi]: " MAIL_FROM_NAME
MAIL_FROM_NAME=${MAIL_FROM_NAME:-AngolaWiFi}
read -rp  "Host SMTP (ex: smtp.gmail.com): " MAIL_HOST
read -rp  "Porto SMTP [587]: " MAIL_PORT
MAIL_PORT=${MAIL_PORT:-587}
read -rp  "Utilizador SMTP: " MAIL_USER
read -rsp "Password SMTP: " MAIL_PASS; echo ""

echo ""

# =============================================================================
# 2. Base de dados PostgreSQL (já migrada do servidor antigo)
# =============================================================================
hdr "Base de dados PostgreSQL"
info "Base de dados já migrada — a verificar ligação..."

export PGPASSWORD="${DB_PASS}"
if psql -U "${DB_USER}" -d "${DB_NAME}" -h 127.0.0.1 -c '\q' 2>/dev/null; then
    ok "Ligação PostgreSQL OK — BD '$DB_NAME' acessível"
else
    echo -e "${YELLOW}[!!]${NC} Não foi possível ligar à BD '$DB_NAME' com o utilizador '$DB_USER'."
    echo -e "     Verifique se o PostgreSQL está em execução e se as credenciais estão correctas."
    echo -e "     Para criar manualmente: sudo -u postgres createdb ${DB_NAME} && sudo -u postgres createuser ${DB_USER}"
fi
unset PGPASSWORD

# =============================================================================
# 3. Criar .env da Loja
# =============================================================================
hdr "Ficheiro .env da Loja"

# Garantir que a extensão PHP para PostgreSQL está instalada
if ! php -m 2>/dev/null | grep -q pgsql; then
    info "A instalar php${PHP_VERSION}-pgsql..."
    apt-get install -y -qq php${PHP_VERSION}-pgsql
    systemctl restart php${PHP_VERSION}-fpm
    ok "php${PHP_VERSION}-pgsql instalado"
fi

cat > "${LOJA_DIR}/.env" <<ENV
APP_NAME=AngolaWiFi
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://angolawifi.ao

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=${MAIL_HOST}
MAIL_PORT=${MAIL_PORT}
MAIL_USERNAME=${MAIL_USER}
MAIL_PASSWORD=${MAIL_PASS}
MAIL_FROM_ADDRESS="${MAIL_FROM}"
MAIL_FROM_NAME="${MAIL_FROM_NAME}"

# ── SG (Sistema de Gestão) ─────────────────────────────────────────────────
SG_URL=${SG_URL}
SG_CLIENT_ID=${SG_CLIENT_ID}
SG_CLIENT_SECRET=${SG_CLIENT_SECRET}
SG_LOJA_ADMIN_TOKEN=${SG_ADMIN_TOKEN}
SG_OAUTH_TOKEN_PATH=/api/oauth/token
SG_ACTIVE_CLIENTS_PATH=/api/stats/active-clients

VITE_APP_NAME="AngolaWiFi"
ENV

ok ".env da loja criado"

# =============================================================================
# 4. Dependências e assets da Loja
# =============================================================================
hdr "Loja — composer / npm / artisan"

cd "${LOJA_DIR}"

info "composer install..."
composer install --no-dev --optimize-autoloader --quiet

info "Gerar APP_KEY..."
php artisan key:generate --force

info "npm ci e compilar assets..."
npm ci --silent
npm run build

info "Permissões..."
chown -R www-data:www-data "${LOJA_DIR}"
chmod -R 755 "${LOJA_DIR}"
chmod -R 775 "${LOJA_DIR}/storage"
chmod -R 775 "${LOJA_DIR}/bootstrap/cache"

info "Migrações da loja (apenas estrutura nova; dados já migrados)..."
php artisan migrate --force

info "Optimizar loja..."
php artisan optimize:clear
php artisan optimize

ok "Loja pronta"

# =============================================================================
# 5. Nginx — configurar virtual hosts (HTTP para já; certbot a seguir)
# =============================================================================
hdr "Nginx — Virtual Hosts"

# ── Loja: angolawifi.ao ────────────────────────────────────────────────────
cat > /etc/nginx/sites-available/angolawifi-loja <<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name angolawifi.ao www.angolawifi.ao;

    root ${LOJA_DIR}/public;
    index index.php;

    charset utf-8;
    client_max_body_size 16M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass  unix:/var/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include       fastcgi_params;
        fastcgi_read_timeout 120;
    }

    location ~ /\.(?!well-known).* { deny all; }

    location /build/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files \$uri =404;
    }
}
NGINX

# ── SG: sg.angolawifi.ao ───────────────────────────────────────────────────
cat > /etc/nginx/sites-available/angolawifi-sg <<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name sg.angolawifi.ao;

    root ${SG_DIR}/public;
    index index.php;

    charset utf-8;
    client_max_body_size 32M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass  unix:/var/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include       fastcgi_params;
        fastcgi_read_timeout 120;
    }

    location ~ /\.(?!well-known).* { deny all; }

    location /build/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files \$uri =404;
    }
}
NGINX

# ── Activar sites e remover default ───────────────────────────────────────
ln -sf /etc/nginx/sites-available/angolawifi-loja /etc/nginx/sites-enabled/angolawifi-loja
ln -sf /etc/nginx/sites-available/angolawifi-sg   /etc/nginx/sites-enabled/angolawifi-sg
rm -f /etc/nginx/sites-enabled/default
rm -f /etc/nginx/sites-enabled/loja   # remover config antiga do setup-vps.sh se existir

nginx -t && ok "Configuração Nginx válida" || err "Erro na configuração Nginx — verifique com: nginx -t"

systemctl restart php${PHP_VERSION}-fpm
systemctl reload nginx
ok "Nginx e PHP-FPM reiniciados"

# =============================================================================
# 6. SSL — Certbot (Let's Encrypt)
# =============================================================================
hdr "SSL — Let's Encrypt"

# Instalar certbot se necessário
if ! command -v certbot &>/dev/null; then
    info "A instalar certbot..."
    apt-get install -y -qq certbot python3-certbot-nginx
    ok "Certbot instalado"
fi

info "A obter certificado para angolawifi.ao e www.angolawifi.ao..."
certbot --nginx \
    --non-interactive \
    --agree-tos \
    --redirect \
    --email "${MAIL_FROM}" \
    -d angolawifi.ao \
    -d www.angolawifi.ao \
    && ok "Certificado SSL da loja emitido" \
    || { echo -e "${RED}[!!]${NC} Certbot falhou para angolawifi.ao — DNS validado? (${RED}certbot --nginx -d angolawifi.ao -d www.angolawifi.ao${NC})"; }

info "A obter certificado para sg.angolawifi.ao..."
certbot --nginx \
    --non-interactive \
    --agree-tos \
    --redirect \
    --email "${MAIL_FROM}" \
    -d sg.angolawifi.ao \
    && ok "Certificado SSL do SG emitido" \
    || { echo -e "${RED}[!!]${NC} Certbot falhou para sg.angolawifi.ao — DNS validado? (${RED}certbot --nginx -d sg.angolawifi.ao${NC})"; }

# =============================================================================
# 7. Actualizar APP_URL da loja para HTTPS (após certbot)
# =============================================================================
hdr "Actualizar APP_URL para HTTPS"

cd "${LOJA_DIR}"
php artisan optimize:clear
php artisan optimize
ok "Cache limpa e regenerada com APP_URL=https://angolawifi.ao"

# =============================================================================
# 8. sudo sem password para deploys futuros
# =============================================================================
SUDOERS_LINE="fernanda ALL=(ALL) NOPASSWD: /bin/systemctl restart php${PHP_VERSION}-fpm, /bin/systemctl reload nginx"
echo "${SUDOERS_LINE}" > /etc/sudoers.d/deploy-angolawifi
chmod 0440 /etc/sudoers.d/deploy-angolawifi
ok "Sudo configurado para utilizador 'fernanda'"

# =============================================================================
# Concluído
# =============================================================================
echo ""
echo "════════════════════════════════════════════════════════"
echo -e "${GREEN}  Configuração concluída!${NC}"
echo "════════════════════════════════════════════════════════"
echo ""
echo "  Loja:   https://angolawifi.ao"
echo "  SG:     https://sg.angolawifi.ao"
echo ""
echo "  Directórios:"
echo "    Loja → ${LOJA_DIR}"
echo "    SG   → ${SG_DIR}"
echo ""
echo "  Logs de erro:"
echo "    tail -f ${LOJA_DIR}/storage/logs/laravel.log"
echo "    tail -f ${SG_DIR}/storage/logs/laravel.log"
echo ""
echo "  Verificar serviços:"
echo "    systemctl status nginx"
echo "    systemctl status php${PHP_VERSION}-fpm"
echo ""
echo "  Se certbot falhou (DNS ainda a propagar), execute manualmente:"
echo "    certbot --nginx -d angolawifi.ao -d www.angolawifi.ao"
echo "    certbot --nginx -d sg.angolawifi.ao"
echo ""
