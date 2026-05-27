#!/bin/bash
# ============================================================
#  RAYNET-OS — Interactive Install Script
#  Usage: git clone https://github.com/raynet-uk/raynet-cms.git . && bash install.sh
#  Developed by RAYNET Liverpool (G4BDS & M7NDN)
# ============================================================

# ── Colours & styles ─────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
WHITE='\033[1;37m'
BOLD='\033[1m'
DIM='\033[2m'
NC='\033[0m'

INSTALL_DIR=$(pwd)

# ── UI helpers ────────────────────────────────────────────────
ok()     { echo -e "  ${GREEN}✓${NC}  $1"; }
fail()   { echo -e "  ${RED}✗${NC}  ${RED}$1${NC}"; exit 1; }
info()   { echo -e "  ${CYAN}→${NC}  $1"; }
warn()   { echo -e "  ${YELLOW}⚠${NC}  ${YELLOW}$1${NC}"; }
ask()    { echo -e "  ${YELLOW}?${NC}  ${BOLD}$1${NC}"; }
label()  { echo -e "  ${DIM}$1${NC}"; }

divider() {
    echo -e "\n  ${DIM}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
}

step() {
    local num=$1
    local title=$2
    local total=9
    echo ""
    echo -e "  ${BLUE}${BOLD}[$num/$total]${NC} ${WHITE}${BOLD}$title${NC}"
    echo -e "  ${DIM}$(printf '─%.0s' {1..62})${NC}"
}

progress_bar() {
    local current=$1
    local total=9
    local filled=$(( current * 40 / total ))
    local empty=$(( 40 - filled ))
    local bar="${GREEN}"
    for ((i=0; i<filled; i++)); do bar+="█"; done
    bar+="${DIM}"
    for ((i=0; i<empty; i++)); do bar+="░"; done
    bar+="${NC}"
    echo -e "\n  Progress: [${bar}] ${BOLD}${current}/${total}${NC}\n"
}

header() {
    clear
    echo ""
    echo -e "${BLUE}${BOLD}"
    echo "  ██████╗  █████╗ ██╗   ██╗███╗   ██╗███████╗████████╗"
    echo "  ██╔══██╗██╔══██╗╚██╗ ██╔╝████╗  ██║██╔════╝╚══██╔══╝"
    echo "  ██████╔╝███████║ ╚████╔╝ ██╔██╗ ██║█████╗     ██║   "
    echo "  ██╔══██╗██╔══██║  ╚██╔╝  ██║╚██╗██║██╔══╝     ██║   "
    echo "  ██║  ██║██║  ██║   ██║   ██║ ╚████║███████╗   ██║   "
    echo "  ╚═╝  ╚═╝╚═╝  ╚═╝   ╚═╝   ╚═╝  ╚═══╝╚══════╝   ╚═╝   "
    echo -e "${NC}"
    echo -e "  ${WHITE}${BOLD}RAYNET-OS${NC} ${DIM}— Installation Wizard${NC}"
    echo -e "  ${DIM}Developed by RAYNET Liverpool · G4BDS & M7NDN${NC}"
    echo -e "  ${DIM}For RAYNET UK affiliated groups · raynet-uk.net${NC}"
    divider
}

# ── Detect PHP ────────────────────────────────────────────────
detect_php() {
    for bin in \
        /usr/local/bin/ea-php84 \
        /usr/local/bin/ea-php83 \
        /usr/local/bin/ea-php82 \
        /usr/local/bin/ea-php81 \
        php8.4 php8.3 php8.2 \
        /usr/local/bin/php \
        /usr/bin/php \
        php; do
        if [ -x "$bin" ] 2>/dev/null || command -v "$bin" &>/dev/null; then
            TEST=$("$bin" -r "echo PHP_VERSION;" 2>/dev/null)
            [ -z "$TEST" ] && continue
            PHP_VER=$(echo "$TEST" | tr -d "\r\n ")
            MAJOR=$(echo "$PHP_VER" | cut -d. -f1 | tr -dc "0-9")
            MINOR=$(echo "$PHP_VER" | cut -d. -f2 | tr -dc "0-9")
            if [ -n "$MAJOR" ] && [ -n "$MINOR" ] && [ "$MAJOR" -ge 8 ] && [ "$MINOR" -ge 2 ]; then
                PHP="$bin"
                return 0
            fi
        fi
    done
    return 1
}

# ── Detect account user ───────────────────────────────────────
detect_account_user() {
    ACCOUNT_USER=""
    local dir="$INSTALL_DIR"
    for i in 1 2 3 4; do
        local owner
        owner=$(stat -c '%U' "$dir" 2>/dev/null)
        if [ -n "$owner" ] && [ "$owner" != "root" ] && [ "$owner" != "nobody" ]; then
            ACCOUNT_USER="$owner"
            return 0
        fi
        dir=$(dirname "$dir")
    done
    [ -d "/home" ] && ACCOUNT_USER=$(ls /home 2>/dev/null | head -1)
}

# ── Step 0: Ownership ─────────────────────────────────────────
fix_ownership() {
    step "0" "Fixing File Ownership"
    detect_account_user
    if [ -n "$ACCOUNT_USER" ] && [ "$ACCOUNT_USER" != "root" ]; then
        info "Account user: ${BOLD}$ACCOUNT_USER${NC}"
        chown -R "$ACCOUNT_USER":"$ACCOUNT_USER" "$INSTALL_DIR" 2>/dev/null && \
            ok "Ownership set to ${BOLD}$ACCOUNT_USER${NC}" || \
            warn "Could not set ownership — continuing anyway"
    else
        warn "Could not detect account user"
    fi
    chmod -R 755 "$INSTALL_DIR" 2>/dev/null
    mkdir -p storage/logs storage/framework/cache storage/framework/sessions \
             storage/framework/views storage/app/public bootstrap/cache 2>/dev/null
    chmod -R 775 storage bootstrap/cache 2>/dev/null
    ok "Permissions set"
    progress_bar 1
}

# ── Step 1: Preflight ─────────────────────────────────────────
preflight() {
    step "1" "Pre-flight Checks"

    if ! detect_php; then
        fail "PHP 8.2+ not found. Install PHP 8.2 or higher and try again."
    fi
    ok "PHP ${BOLD}$PHP_VER${NC} detected at ${DIM}$PHP${NC}"

    local all_ok=true
    for ext in pdo pdo_mysql mbstring openssl curl zip fileinfo; do
        if $PHP -r "echo extension_loaded('$ext') ? 'yes' : 'no';" 2>/dev/null | grep -q "yes"; then
            ok "Extension: ${BOLD}$ext${NC}"
        else
            warn "Extension possibly missing: $ext"
            all_ok=false
        fi
    done

    if [ ! -f ".env.example" ] && [ ! -f ".env" ]; then
        fail "No .env.example found. Run this from the RAYNET-OS root directory."
    fi
    ok "Directory structure valid"
    progress_bar 2
}

# ── Step 2: Environment ───────────────────────────────────────
setup_env() {
    step "2" "Environment Configuration"

    if [ ! -f ".env" ]; then
        cp .env.example .env
        ok "Created ${BOLD}.env${NC} from .env.example"
    else
        warn ".env already exists — updating values"
    fi

    echo ""
    echo -e "  ${WHITE}${BOLD}Please provide your site configuration:${NC}"
    echo -e "  ${DIM}Press Enter to accept defaults where shown in [brackets]${NC}"
    echo ""

    ask "Site URL (e.g. https://yourgroup.net):"
    read -r APP_URL
    APP_URL=$(echo "${APP_URL:-https://example.com}" | tr -d '[:space:]')

    ask "Database host [localhost]:"
    read -r DB_HOST
    DB_HOST=${DB_HOST:-localhost}

    ask "Database name:"
    read -r DB_DATABASE

    ask "Database username:"
    read -r DB_USERNAME

    ask "Database password:"
    read -rs DB_PASSWORD
    echo ""

    ask "Mail host (optional, e.g. mail.yourgroup.net):"
    read -r MAIL_HOST

    ask "Mail from address (optional):"
    read -r MAIL_FROM

    ask "Mail password (optional):"
    read -rs MAIL_PASS
    echo ""

    sed -i "s|APP_URL=.*|APP_URL=$APP_URL|g"             .env
    sed -i "s|DB_HOST=.*|DB_HOST=$DB_HOST|g"             .env
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=$DB_DATABASE|g" .env
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=$DB_USERNAME|g" .env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|g" .env
    [ -n "$MAIL_HOST" ] && sed -i "s|MAIL_HOST=.*|MAIL_HOST=$MAIL_HOST|g" .env
    [ -n "$MAIL_FROM" ] && sed -i "s|MAIL_FROM_ADDRESS=.*|MAIL_FROM_ADDRESS=\"$MAIL_FROM\"|g" .env
    [ -n "$MAIL_PASS" ] && sed -i "s|MAIL_PASSWORD=.*|MAIL_PASSWORD=$MAIL_PASS|g" .env
    sed -i "s|APP_KEY=.*|APP_KEY=|g" .env

    ok ".env configured successfully"
    progress_bar 3
}

# ── Step 3: Composer ──────────────────────────────────────────
install_deps() {
    step "3" "Installing PHP Dependencies"

    info "Downloading composer.phar using $PHP..."
    curl -sS https://getcomposer.org/installer | $PHP -- --quiet 2>/dev/null
    if [ ! -f "composer.phar" ]; then
        fail "Failed to download composer.phar. Check curl is available."
    fi
    COMPOSER="$PHP composer.phar"
    ok "Composer ready"

    info "Running composer install ${DIM}(this may take 1-2 minutes)${NC}..."

    if [ "$(whoami)" = "root" ] && [ -n "$ACCOUNT_USER" ] && [ "$ACCOUNT_USER" != "root" ]; then
        su -s /bin/bash "$ACCOUNT_USER" -c \
            "cd $INSTALL_DIR && $COMPOSER install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs 2>&1" \
            | grep -v "OPcache" | grep -E "^(Installing|Generating|  -)" | tail -5
    else
        $COMPOSER install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs 2>&1 \
            | grep -v "OPcache" | grep -E "^(Installing|Generating|  -)" | tail -5
    fi

    if [ ! -d "vendor" ]; then
        fail "vendor/ directory not created — composer install failed."
    fi
    ok "Dependencies installed ${DIM}($(ls vendor | wc -l) packages)${NC}"
    progress_bar 4
}

# ── Step 4: App key ───────────────────────────────────────────
generate_key() {
    step "4" "Application Key"

    $PHP artisan key:generate --force 2>&1 | grep -v "OPcache"
    KEY=$(grep "^APP_KEY=" .env | cut -d= -f2)
    if [ -z "$KEY" ]; then
        fail "APP_KEY is still empty — key generation failed."
    fi
    ok "Application key generated"
    progress_bar 5
}

# ── Step 5: Database ──────────────────────────────────────────
run_migrations() {
    step "5" "Database Migrations"

    info "Running migrations..."
    $PHP artisan migrate --force 2>&1 | grep -v "OPcache" | grep -E "(DONE|FAIL|INFO)" | head -20
    ok "Database migrations complete"
    progress_bar 6
}

# ── Step 6: Seed roles ────────────────────────────────────────
seed_roles() {
    step "6" "Seeding Roles & Permissions"

    $PHP artisan db:seed --class=SpatieRoleSeeder --force 2>&1 | grep -v "OPcache" | grep -v "^$" && \
        ok "Roles and permissions seeded" || warn "Role seeding had issues"
    progress_bar 7
}

# ── Step 7: Storage & permissions ────────────────────────────
setup_storage() {
    step "7" "Storage & Permissions"

    rm -f public/storage 2>/dev/null
    $PHP artisan storage:link 2>&1 | grep -v "OPcache"
    ok "Storage link created"

    if [ -n "$ACCOUNT_USER" ] && [ "$ACCOUNT_USER" != "root" ]; then
        chown -R "$ACCOUNT_USER":"$ACCOUNT_USER" "$INSTALL_DIR" 2>/dev/null && \
            ok "Final ownership set to ${BOLD}$ACCOUNT_USER${NC}"
    fi
    chmod -R 775 storage bootstrap/cache 2>/dev/null
    find storage -type f -exec chmod 664 {} \; 2>/dev/null
    ok "Permissions finalised"
    progress_bar 8
}

# ── Step 8: Cache & document root ────────────────────────────
finalise() {
    step "8" "Cache & Web Server"

    $PHP artisan route:clear  2>&1 | grep -v "OPcache" && ok "Routes cleared"
    $PHP artisan view:clear   2>&1 | grep -v "OPcache" && ok "Views cleared"
    $PHP artisan config:clear 2>&1 | grep -v "OPcache" && ok "Config cleared"
    $PHP artisan cache:clear  2>&1 | grep -v "OPcache" && ok "Cache cleared"

    echo ""
    echo -e "  ${WHITE}${BOLD}Document Root${NC}"
    echo -e "  ${DIM}Set your domain's document root to:${NC}"
    echo ""
    echo -e "  ${CYAN}${BOLD}$INSTALL_DIR/public${NC}"
    echo ""
    echo -e "  ${DIM}In cPanel → Domains → Edit Document Root${NC}"

    PARENT_DIR=$(dirname "$INSTALL_DIR")
    FOLDER_NAME=$(basename "$INSTALL_DIR")
    if [ -d "$PARENT_DIR" ] && [ -w "$PARENT_DIR" ]; then
        echo ""
        ask "Auto-create redirect .htaccess in parent directory? (y/N)"
        read -r AUTO_HT
        if [[ "$AUTO_HT" =~ ^[Yy]$ ]]; then
            cat > "$PARENT_DIR/.htaccess" << HTEOF
RewriteEngine On
RewriteRule ^(.*)$ ${FOLDER_NAME}/public/\$1 [L]
HTEOF
            [ -n "$ACCOUNT_USER" ] && chown "$ACCOUNT_USER":"$ACCOUNT_USER" "$PARENT_DIR/.htaccess" 2>/dev/null
            ok "Created redirect .htaccess in parent directory"
        fi
    fi
    progress_bar 9
}

# ── Summary ───────────────────────────────────────────────────
summary() {
    divider
    echo -e "  ${GREEN}${BOLD}✓ RAYNET-OS installed successfully!${NC}"
    divider

    echo -e "  ${WHITE}${BOLD}What happens next:${NC}"
    echo ""
    echo -e "  ${CYAN}1.${NC} Visit ${WHITE}${BOLD}$APP_URL${NC} in your browser"
    echo -e "  ${CYAN}2.${NC} Complete the setup wizard ${DIM}(group name, callsign, admin account)${NC}"
    echo -e "  ${CYAN}3.${NC} You'll be automatically logged in after setup"
    echo ""

    echo -e "  ${WHITE}${BOLD}Cron job ${DIM}(add to cPanel → Cron Jobs):${NC}"
    echo ""
    echo -e "  ${YELLOW}* * * * * cd $INSTALL_DIR && $PHP artisan schedule:run >> /dev/null 2>&1${NC}"
    echo ""

    divider
    echo -e "  ${DIM}RAYNET-OS · Built by RAYNET Liverpool · G4BDS & M7NDN${NC}"
    echo -e "  ${DIM}github.com/raynet-uk/raynet-cms · 73 de RAYNET Liverpool 📻${NC}"
    echo ""
}

# ── Main ──────────────────────────────────────────────────────
main() {
    header

    echo -e "  ${WHITE}This script will install RAYNET-OS with no manual steps needed.${NC}"
    echo -e "  ${DIM}It handles ownership, dependencies, database, permissions and more.${NC}"
    echo ""
    echo -e "  ${DIM}Running from: ${CYAN}$INSTALL_DIR${NC}"
    echo ""
    ask "Ready to begin installation? (y/N)"
    read -r CONFIRM
    if [[ ! "$CONFIRM" =~ ^[Yy]$ ]]; then
        echo ""
        echo -e "  ${DIM}Installation cancelled.${NC}"
        echo ""
        exit 0
    fi

    fix_ownership
    preflight
    setup_env
    install_deps
    generate_key
    run_migrations
    seed_roles
    setup_storage
    finalise
    summary
}

main
