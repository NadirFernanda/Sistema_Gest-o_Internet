#!/usr/bin/env bash
# =============================================================================
#  setup-vps.sh — Configuração inicial do servidor VPS (Ubuntu 22.04 / 24.04)
#  Loja AngolaWiFi — Laravel 12 / PHP 8.4 / MySQL / Nginx
#
#  Executar como root:
#    bash setup-vps.sh
# =============================================================================
set -e

# ── Cores ──────────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
ok()   { echo -e "${GREEN}[OK]${NC} $*"; }
info() { echo -e "${YELLOW}[>>]${NC} $*"; }
err()  { echo -e "${RED}[!!] $*${NC}"; exit 1; }

[[ $EUID -ne 0 ]] && err "Execute como root: sudo bash setup-vps.sh"

# ── Variáveis configuráveis ─────────────────────────────────────────────────
APP_DIR="/var/www/sgmrtexas/loja"
REPO_URL="https://github.com/NadirFernanda/Sistema_Gest-o_Internet.git"
REPO_SUBDIR="loja"          # o repositório tem a loja numa subdirectoria
NGINX_CONF="/etc/nginx/sites-available/loja"
PHP_VERSION="8.4"
NODE_MAJOR="20"

# ── Pedir dados ao utilizador ────────────────────────────────────────────────
echo ""
echo "========================================================"
echo "  Configuração VPS — Loja AngolaWiFi"
echo "========================================================"
echo ""

read -rp "IP / Domínio do servidor (ex: 89.167.23.38 ou loja.exemple.com): " SERVER_HOST
read -rp "Nome da base de dados MySQL [loja_angolawifi]: " DB_NAME
DB_NAME=${DB_NAME:-loja_angolawifi}
read -rp "Utilizador MySQL para a loja [lojauser]: " DB_USER
DB_USER=${DB_USER:-lojauser}
read -rsp "Password MySQL para o utilizador '$DB_USER': " DB_PASS
echo ""
read -rsp "Password root MySQL (para criar a BD/utilizador): " DB_ROOT_PASS
echo ""
read -rp "URL do SG principal (ex: https://sg.angolawifi.com): " SG_URL
read -rp "SG Client ID da loja: " SG_CLIENT_ID
read -rsp "SG Client Secret da loja: " SG_CLIENT_SECRET
echo ""
read -rsp "Token admin da loja (SG_LOJA_ADMIN_TOKEN): " SG_ADMIN_TOKEN
echo ""
read -rp "E-mail remetente (ex: noreply@angolawifi.com): " MAIL_FROM
read -rp "Nome remetente (ex: AngolaWiFi): " MAIL_FROM_NAME
read -rp "Host SMTP (ex: smtp.gmail.com): " MAIL_HOST
read -rp "Porto SMTP [587]: " MAIL_PORT
MAIL_PORT=${MAIL_PORT:-587}
read -rp "Utilizador SMTP: " MAIL_USER
read -rsp "Password SMTP: " MAIL_PASS
echo ""

echo ""
info "A iniciar instalação..."

# =============================================================================
# 1. Sistema base
# =============================================================================
info "A actualizar lista de pacotes..."
apt-get update -qq
apt-get upgrade -y -qq
apt-get install -y -qq curl wget git unzip zip software-properties-common gnupg2 ca-certificates lsb-release

ok "Pacotes base instalados"

# =============================================================================
# 2. PHP 8.4
# =============================================================================
info "A instalar PHP ${PHP_VERSION}..."
add-apt-repository -y ppa:ondrej/php > /dev/null 2>&1
apt-get update -qq
apt-get install -y -qq \
    php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-cli \
    php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-pdo \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-zip \
    php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-tokenizer \
    php${PHP_VERSION}-fileinfo \
    php${PHP_VERSION}-dom \
    php${PHP_VERSION}-json \
    php${PHP_VERSION}-ctype

ok "PHP ${PHP_VERSION} instalado: $(php${PHP_VERSION} -r 'echo PHP_VERSION;')"

# =============================================================================
# 3. MySQL
# =============================================================================
info "A instalar MySQL..."
apt-get install -y -qq mysql-server

# Configurar base de dados e utilizador
mysql -u root -p"${DB_ROOT_PASS}" <<SQL 2>/dev/null || \
mysql -u root <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

ok "MySQL configurado — BD: ${DB_NAME} / Utilizador: ${DB_USER}"

# =============================================================================
# 4. Nginx
# =============================================================================
info "A instalar Nginx..."
apt-get install -y -qq nginx
ok "Nginx instalado"

# =============================================================================
# 5. Composer
# =============================================================================
if ! command -v composer &>/dev/null; then
    info "A instalar Composer..."
    EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"
    if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]; then
        rm composer-setup.php
        err "Checksum do instalador do Composer inválido"
    fi
    php composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
    rm composer-setup.php
    ok "Composer instalado: $(composer --version)"
else
    ok "Composer já instalado: $(composer --version)"
fi

# =============================================================================
# 6. Node.js
# =============================================================================
info "A instalar Node.js ${NODE_MAJOR}..."
curl -fsSL https://deb.nodesource.com/setup_${NODE_MAJOR}.x | bash - > /dev/null 2>&1
apt-get install -y -qq nodejs
ok "Node.js instalado: $(node -v) / npm: $(npm -v)"

# =============================================================================
# 7. Clonar repositório
# =============================================================================
info "A clonar repositório..."
mkdir -p /var/www/sgmrtexas

if [ -d "${APP_DIR}" ]; then
    info "Directório ${APP_DIR} já existe — a fazer pull..."
    cd "${APP_DIR}"
    git fetch origin
    git reset --hard origin/main
else
    # O repo tem a pasta "loja" dentro. Clona o repo raiz e cria symlink/mv
    TMP_CLONE=$(mktemp -d)
    git clone "${REPO_URL}" "${TMP_CLONE}"
    mv "${TMP_CLONE}/${REPO_SUBDIR}" "${APP_DIR}"
    rm -rf "${TMP_CLONE}"
fi

ok "Repositório clonado em ${APP_DIR}"

# =============================================================================
# 8. Ficheiro .env
# =============================================================================
info "A gerar .env de produção..."
APP_KEY_PLACEHOLDER="__GERAR__"

cat > "${APP_DIR}/.env" <<ENV
APP_NAME=AngolaWiFi
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://${SERVER_HOST}

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
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

# ── SG (Sistema de Gestão) ────────────────────────────────
SG_URL=${SG_URL}
SG_CLIENT_ID=${SG_CLIENT_ID}
SG_CLIENT_SECRET=${SG_CLIENT_SECRET}
SG_LOJA_ADMIN_TOKEN=${SG_ADMIN_TOKEN}
SG_ACTIVE_CLIENTS_PATH=/api/stats/active-clients

VITE_APP_NAME="AngolaWiFi"
ENV

ok ".env criado"

# =============================================================================
# 9. Composer install + APP_KEY + Assets
# =============================================================================
cd "${APP_DIR}"

info "A instalar dependências PHP (composer)..."
composer install --no-dev --optimize-autoloader --quiet

info "A gerar APP_KEY..."
php artisan key:generate --force

info "A instalar dependências Node.js..."
npm ci --silent

info "A compilar assets (Vite)..."
npm run build

ok "Dependências e assets prontos"

# =============================================================================
# 10. Permissões
# =============================================================================
info "A configurar permissões..."
chown -R www-data:www-data "${APP_DIR}"
chmod -R 755 "${APP_DIR}"
chmod -R 775 "${APP_DIR}/storage"
chmod -R 775 "${APP_DIR}/bootstrap/cache"
ok "Permissões configuradas"

# =============================================================================
# 11. Migrações
# =============================================================================
info "A executar migrações..."
php artisan migrate --force
ok "Migrações aplicadas"

# =============================================================================
# 12. Optimize
# =============================================================================
info "A optimizar Laravel..."
php artisan optimize:clear
php artisan optimize
ok "Optimização concluída"

# =============================================================================
# 13. php-fpm — aumentar timeouts
# =============================================================================
PHP_FPM_POOL="/etc/php/${PHP_VERSION}/fpm/pool.d/www.conf"
if [ -f "${PHP_FPM_POOL}" ]; then
    sed -i 's/^pm.max_children.*/pm.max_children = 20/'      "${PHP_FPM_POOL}"
    sed -i 's/^pm.start_servers.*/pm.start_servers = 4/'     "${PHP_FPM_POOL}"
    sed -i 's/^pm.min_spare_servers.*/pm.min_spare_servers = 2/' "${PHP_FPM_POOL}"
    sed -i 's/^pm.max_spare_servers.*/pm.max_spare_servers = 6/' "${PHP_FPM_POOL}"
fi

# =============================================================================
# 14. Nginx — virtual host
# =============================================================================
info "A configurar Nginx..."
cat > "${NGINX_CONF}" <<NGINX
server {
    listen 80;
    server_name ${SERVER_HOST};

    root ${APP_DIR}/public;
    index index.php;

    charset utf-8;
    client_max_body_size 16M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass  unix:/var/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 120;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Assets compilados pelo Vite — cache longo
    location /build/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files \$uri =404;
    }
}
NGINX

ln -sf "${NGINX_CONF}" /etc/nginx/sites-enabled/loja
# Remover default se existir
rm -f /etc/nginx/sites-enabled/default

nginx -t && ok "Configuração Nginx válida" || err "Erro na configuração Nginx"

# =============================================================================
# 15. Reiniciar serviços
# =============================================================================
info "A reiniciar serviços..."
systemctl restart php${PHP_VERSION}-fpm
systemctl reload nginx
systemctl enable php${PHP_VERSION}-fpm
systemctl enable nginx
ok "Serviços em execução"

# =============================================================================
# 16. sudo sem password para deploys futuros
# =============================================================================
info "A configurar sudo sem password para restart de serviços..."
SUDOERS_LINE="root ALL=(ALL) NOPASSWD: /bin/systemctl restart php${PHP_VERSION}-fpm, /bin/systemctl reload nginx"
echo "${SUDOERS_LINE}" > /etc/sudoers.d/deploy-loja
chmod 0440 /etc/sudoers.d/deploy-loja
ok "Sudo configurado"

# =============================================================================
# Concluído
# =============================================================================
echo ""
echo "========================================================"
echo -e "${GREEN}  Instalação concluída com sucesso!${NC}"
echo "========================================================"
echo ""
echo "  URL da loja:    http://${SERVER_HOST}"
echo "  Directório:     ${APP_DIR}"
echo "  PHP-FPM:        php${PHP_VERSION}-fpm"
echo "  Base de dados:  ${DB_NAME} @ localhost"
echo ""
echo "  Para verificar os serviços:"
echo "    systemctl status nginx"
echo "    systemctl status php${PHP_VERSION}-fpm"
echo ""
echo "  Para ver os logs de erro Laravel:"
echo "    tail -f ${APP_DIR}/storage/logs/laravel.log"
echo ""
echo "  Para deploys futuros, siga o README (secção Deploy)."
echo ""
