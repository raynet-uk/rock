#!/bin/bash

# ============================================================
# RAYNET-OS — Interactive Install Script
# Usage: git clone https://github.com/raynet-uk/raynet-cms.git . && bash install.sh
# Developed by RAYNET-UK volunteers
# ============================================================

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
WHITE='\033[1;37m'
BOLD='\033[1m'
DIM='\033[2m'
NC='\033[0m'

INSTALL_DIR=$(pwd)
TOTAL_STEPS=8

TL='╔'; TR='╗'; BL='╚'; BR='╝'; H='═'; V='║'; ML='╠'; MR='╣'

box_top() { echo -e "${1:-$CYAN}${TL}$(printf '%0.s═' {1..68})${TR}${NC}"; }
box_mid() { echo -e "${1:-$CYAN}${ML}$(printf '%0.s═' {1..68})${MR}${NC}"; }
box_bot() { echo -e "${1:-$CYAN}${BL}$(printf '%0.s═' {1..68})${BR}${NC}"; }
box_empty() { echo -e "${1:-$CYAN}${V}$(printf '%0.s ' {1..68})${V}${NC}"; }

ok()   { echo -e "  ${GREEN}✔${NC}  $1"; }
fail() { echo -e "  ${RED}✘${NC}  ${RED}$1${NC}"; exit 1; }
info() { echo -e "  ${CYAN}→${NC}  $1"; }
warn() { echo -e "  ${YELLOW}⚠${NC}  ${YELLOW}$1${NC}"; }
ask()  { echo -e "  ${YELLOW}?${NC}  ${BOLD}$1${NC}"; }

step() {
    local num=$1 title=$2
    local filled=$(( num * 50 / TOTAL_STEPS ))
    local empty=$(( 50 - filled ))
    local pct=$(( num * 100 / TOTAL_STEPS ))
    echo ""
    box_top "$BLUE"
    box_empty "$BLUE"
    printf "${BLUE}${V}${NC}${BOLD}${WHITE}  STEP %s/%s${NC}  ${CYAN}%s${NC}%*s${BLUE}${V}${NC}\n" \
        "$num" "$TOTAL_STEPS" "$title" "$(( 68 - 10 - ${#num} - ${#TOTAL_STEPS} - ${#title} ))" ""
    box_empty "$BLUE"
    local bar="  ["
    for ((i=0; i<filled; i++)); do bar+="█"; done
    for ((i=0; i<empty; i++)); do bar+="░"; done
    bar+="]  ${pct}%"
    printf "${BLUE}${V}${NC}${GREEN}%s${NC}%*s${BLUE}${V}${NC}\n" "$bar" "$(( 68 - ${#bar} + 6 ))" ""
    box_empty "$BLUE"
    box_bot "$BLUE"
    echo ""
}

detect_php() {
    for bin in /usr/local/bin/ea-php84 /usr/local/bin/ea-php83 /usr/local/bin/ea-php82 \
               php8.4 php8.3 php8.2 /usr/local/bin/php /usr/bin/php php; do
        if [ -x "$bin" ] 2>/dev/null || command -v "$bin" &>/dev/null; then
            TEST=$("$bin" -r "echo PHP_VERSION;" 2>/dev/null)
            [ -z "$TEST" ] && continue
            PHP_VER=$(echo "$TEST" | tr -d "\r\n ")
            MAJOR=$(echo "$PHP_VER" | cut -d. -f1 | tr -dc "0-9")
            MINOR=$(echo "$PHP_VER" | cut -d. -f2 | tr -dc "0-9")
            if [ -n "$MAJOR" ] && [ -n "$MINOR" ] && [ "$MAJOR" -ge 8 ] && [ "$MINOR" -ge 2 ]; then
                PHP="$bin"; return 0
            fi
        fi
    done
    return 1
}

detect_account_user() {
    ACCOUNT_USER=""
    local dir="$INSTALL_DIR"
    for i in 1 2 3 4; do
        local owner; owner=$(stat -c '%U' "$dir" 2>/dev/null)
        if [ -n "$owner" ] && [ "$owner" != "root" ] && [ "$owner" != "nobody" ]; then
            ACCOUNT_USER="$owner"; return 0
        fi
        dir=$(dirname "$dir")
    done
    [ -d "/home" ] && ACCOUNT_USER=$(ls /home 2>/dev/null | head -1)
}

header() {
    clear
    echo ""
    box_top "$CYAN"
    box_empty "$CYAN"
    echo -e "${CYAN}${V}${NC}${BOLD}${BLUE}   ██████╗  █████╗ ██╗   ██╗███╗   ██╗███████╗████████╗      ${CYAN}${V}${NC}"
    echo -e "${CYAN}${V}${NC}${BOLD}${BLUE}   ██╔══██╗██╔══██╗╚██╗ ██╔╝████╗  ██║██╔════╝╚══██╔══╝      ${CYAN}${V}${NC}"
    echo -e "${CYAN}${V}${NC}${BOLD}${BLUE}   ██████╔╝███████║ ╚████╔╝ ██╔██╗ ██║█████╗     ██║         ${CYAN}${V}${NC}"
    echo -e "${CYAN}${V}${NC}${BOLD}${BLUE}   ██╔══██╗██╔══██║  ╚██╔╝  ██║╚██╗██║██╔══╝     ██║         ${CYAN}${V}${NC}"
    echo -e "${CYAN}${V}${NC}${BOLD}${BLUE}   ██║  ██║██║  ██║   ██║   ██║ ╚████║███████╗   ██║         ${CYAN}${V}${NC}"
    echo -e "${CYAN}${V}${NC}${BOLD}${BLUE}   ╚═╝  ╚═╝╚═╝  ╚═╝   ╚═╝   ╚═╝  ╚═══╝╚══════╝   ╚═╝         ${CYAN}${V}${NC}"
    box_empty "$CYAN"
    box_mid "$CYAN"
    box_empty "$CYAN"
    printf "${CYAN}${V}${NC}     ${WHITE}${BOLD}%-63s${NC}${CYAN}${V}${NC}\n" "RAYNET-OS  ·  Installation Wizard"
    printf "${CYAN}${V}${NC}     ${DIM}%-63s${NC}${CYAN}${V}${NC}\n" "Developed by RAYNET Liverpool  ·  G4BDS & M7NDN"
    printf "${CYAN}${V}${NC}     ${DIM}%-63s${NC}${CYAN}${V}${NC}\n" "For RAYNET UK affiliated groups  ·  raynet-uk.net"
    box_empty "$CYAN"
    box_bot "$CYAN"
    echo ""
}

fix_ownership() {
    step 0 "Fixing file ownership"
    detect_account_user
    if [ -n "$ACCOUNT_USER" ] && [ "$ACCOUNT_USER" != "root" ]; then
        info "Account user: ${BOLD}$ACCOUNT_USER${NC}"
        chown -R "$ACCOUNT_USER":"$ACCOUNT_USER" "$INSTALL_DIR" 2>/dev/null && \
            ok "Ownership set to ${BOLD}$ACCOUNT_USER${NC}" || warn "Could not set ownership — continuing"
    else
        warn "Could not detect account user"
    fi
    chmod -R 755 "$INSTALL_DIR" 2>/dev/null
    mkdir -p storage/logs storage/framework/cache storage/framework/sessions \
             storage/framework/views storage/app/public bootstrap/cache 2>/dev/null
    chmod -R 775 storage bootstrap/cache 2>/dev/null
    ok "Permissions set"
}

preflight() {
    step 1 "Pre-flight checks"
    if ! detect_php; then
        fail "PHP 8.2+ not found. Install PHP 8.2 or higher and retry."
    fi
    ok "PHP ${BOLD}$PHP_VER${NC} found at ${DIM}$PHP${NC}"
    for ext in pdo pdo_mysql mbstring openssl curl zip fileinfo; do
        if $PHP -r "echo extension_loaded('$ext') ? 'yes' : 'no';" 2>/dev/null | grep -q "yes"; then
            ok "Extension: ${BOLD}$ext${NC}"
        else
            warn "Extension possibly missing: $ext"
        fi
    done
    if [ ! -f ".env.example" ] && [ ! -f ".env" ]; then
        fail "No .env.example found. Run from the RAYNET-OS root directory."
    fi
    ok "Directory structure valid"
}

setup_env() {
    step 2 "Environment configuration"
    if [ ! -f ".env" ]; then
        cp .env.example .env
        ok "Created ${BOLD}.env${NC} from template"
    else
        warn ".env already exists — values will be updated"
    fi
    echo ""
    box_top "$YELLOW"
    printf "${YELLOW}${V}${NC}  ${BOLD}%-66s${NC}${YELLOW}${V}${NC}\n" "Site configuration"
    printf "${YELLOW}${V}${NC}  ${DIM}%-66s${NC}${YELLOW}${V}${NC}\n" "Press Enter to accept defaults shown in [brackets]"
    box_bot "$YELLOW"
    echo ""
    ask "Site URL (e.g. https://yourgroup.net):"; read -r APP_URL
    APP_URL=$(echo "${APP_URL:-https://example.com}" | tr -d '[:space:]')
    ask "Database host [localhost]:"; read -r DB_HOST; DB_HOST=${DB_HOST:-localhost}
    ask "Database name:"; read -r DB_DATABASE
    ask "Database username:"; read -r DB_USERNAME
    ask "Database password:"; read -rs DB_PASSWORD; echo ""
    ask "Mail host (optional):"; read -r MAIL_HOST
    ask "Mail from address (optional):"; read -r MAIL_FROM
    ask "Mail password (optional):"; read -rs MAIL_PASS; echo ""
    sed -i "s|APP_URL=.*|APP_URL=$APP_URL|g"             .env
    sed -i "s|DB_HOST=.*|DB_HOST=$DB_HOST|g"             .env
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=$DB_DATABASE|g" .env
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=$DB_USERNAME|g" .env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|g" .env
    [ -n "$MAIL_HOST" ] && sed -i "s|MAIL_HOST=.*|MAIL_HOST=$MAIL_HOST|g" .env
    [ -n "$MAIL_FROM" ] && sed -i "s|MAIL_FROM_ADDRESS=.*|MAIL_FROM_ADDRESS=\"$MAIL_FROM\"|g" .env
    [ -n "$MAIL_PASS" ] && sed -i "s|MAIL_PASSWORD=.*|MAIL_PASSWORD=$MAIL_PASS|g" .env
    sed -i "s|APP_KEY=.*|APP_KEY=|g" .env
    ok ".env configured"
}

install_deps() {
    step 3 "Installing PHP dependencies"
    info "Downloading composer …"
    curl -sS https://getcomposer.org/installer | $PHP -- --quiet 2>/dev/null
    [ ! -f "composer.phar" ] && fail "Failed to download composer.phar — check curl is available."
    COMPOSER="$PHP composer.phar"
    ok "Composer ready"
    info "Running composer install ${DIM}(this may take 1–2 minutes)${NC} …"
    if [ "$(whoami)" = "root" ] && [ -n "$ACCOUNT_USER" ] && [ "$ACCOUNT_USER" != "root" ]; then
        su -s /bin/bash "$ACCOUNT_USER" -c \
            "cd $INSTALL_DIR && $COMPOSER install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs 2>&1" \
            | grep -v "OPcache" | grep -E "^(Installing|Generating| -)" | tail -5
    else
        $COMPOSER install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs 2>&1 \
            | grep -v "OPcache" | grep -E "^(Installing|Generating| -)" | tail -5
    fi
    [ ! -d "vendor" ] && fail "vendor/ directory not created — composer install failed."
    ok "Dependencies installed ${DIM}($(ls vendor | wc -l) packages)${NC}"
}

generate_key() {
    step 4 "Application key"
    $PHP artisan key:generate --force 2>&1 | grep -v "OPcache"
    KEY=$(grep "^APP_KEY=" .env | cut -d= -f2)
    [ -z "$KEY" ] && fail "APP_KEY is empty — key generation failed."
    ok "Application key generated"
}

run_migrations() {
    step 5 "Database migrations"
    info "Running migrations …"
    $PHP artisan migrate --force 2>&1 | grep -v "OPcache" | grep -E "(DONE|FAIL|INFO)" | head -20
    ok "Migrations complete"
}

seed_roles() {
    step 6 "Seeding roles & permissions"
    $PHP artisan db:seed --class=SpatieRoleSeeder --force 2>&1 | grep -v "OPcache" | grep -v "^$" && \
        ok "Roles and permissions seeded" || warn "Role seeding had issues"
}

setup_storage() {
    step 7 "Storage & permissions"
    rm -f public/storage 2>/dev/null
    $PHP artisan storage:link 2>&1 | grep -v "OPcache"
    ok "Storage link created"
    if [ -n "$ACCOUNT_USER" ] && [ "$ACCOUNT_USER" != "root" ]; then
        chown -R "$ACCOUNT_USER":"$ACCOUNT_USER" "$INSTALL_DIR" 2>/dev/null && \
            ok "Ownership finalised ${DIM}($ACCOUNT_USER)${NC}"
    fi
    chmod -R 775 storage bootstrap/cache 2>/dev/null
    find storage -type f -exec chmod 664 {} \; 2>/dev/null
    ok "Permissions finalised"
}

finalise() {
    step 8 "Cache & web server"
    $PHP artisan route:clear  2>&1 | grep -v "OPcache" && ok "Routes cleared"
    $PHP artisan view:clear   2>&1 | grep -v "OPcache" && ok "Views cleared"
    $PHP artisan config:clear 2>&1 | grep -v "OPcache" && ok "Config cleared"
    $PHP artisan cache:clear  2>&1 | grep -v "OPcache" && ok "Cache cleared"
    echo ""
    box_top "$YELLOW"
    printf "${YELLOW}${V}${NC}  ${BOLD}%-66s${NC}${YELLOW}${V}${NC}\n" "Document root"
    printf "${YELLOW}${V}${NC}  ${DIM}%-66s${NC}${YELLOW}${V}${NC}\n" "Set in cPanel → Domains → Edit Document Root"
    box_empty "$YELLOW"
    printf "${YELLOW}${V}${NC}  ${CYAN}${BOLD}%-66s${NC}${YELLOW}${V}${NC}\n" "$INSTALL_DIR/public"
    box_bot "$YELLOW"
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
            ok "Redirect .htaccess created"
        fi
    fi
}

summary() {
    echo ""
    box_top "$GREEN"
    box_empty "$GREEN"
    printf "${GREEN}${V}${NC}  ${GREEN}${BOLD}%-66s${NC}${GREEN}${V}${NC}\n" "✔  RAYNET-OS installed successfully!"
    box_empty "$GREEN"
    box_mid "$GREEN"
    box_empty "$GREEN"
    printf "${GREEN}${V}${NC}  ${WHITE}${BOLD}%-66s${NC}${GREEN}${V}${NC}\n" "What to do next:"
    box_empty "$GREEN"
    printf "${GREEN}${V}${NC}  ${CYAN}1.${NC}  Visit ${WHITE}${BOLD}$APP_URL${NC}%*s${GREEN}${V}${NC}\n" "$(( 58 - ${#APP_URL} ))" ""
    printf "${GREEN}${V}${NC}  ${CYAN}2.${NC}  %-64s${GREEN}${V}${NC}\n" "Complete the setup wizard (group name, callsign, admin account)"
    printf "${GREEN}${V}${NC}  ${CYAN}3.${NC}  %-64s${GREEN}${V}${NC}\n" "You will be logged in automatically after setup"
    box_empty "$GREEN"
    box_mid "$GREEN"
    box_empty "$GREEN"
    printf "${GREEN}${V}${NC}  ${WHITE}${BOLD}%-66s${NC}${GREEN}${V}${NC}\n" "Cron job (add in cPanel → Cron Jobs):"
    box_empty "$GREEN"
    printf "${GREEN}${V}${NC}  ${YELLOW}* * * * * cd $INSTALL_DIR && $PHP artisan schedule:run >> /dev/null 2>&1${NC}%*s${GREEN}${V}${NC}\n" \
        "$(( 68 - 50 - ${#INSTALL_DIR} - ${#PHP} ))" ""
    box_empty "$GREEN"
    box_mid "$GREEN"
    box_empty "$GREEN"
    printf "${GREEN}${V}${NC}  ${DIM}%-66s${NC}${GREEN}${V}${NC}\n" "RAYNET-OS  ·  Built by RAYNET Liverpool  ·  G4BDS & M7NDN"
    printf "${GREEN}${V}${NC}  ${DIM}%-66s${NC}${GREEN}${V}${NC}\n" "github.com/raynet-uk/raynet-cms  ·  73 de RAYNET Liverpool 📻"
    box_empty "$GREEN"
    box_bot "$GREEN"
    echo ""
}

main() {
    header
    box_top "$DIM"
    printf "${DIM}${V}${NC}  %-66s${DIM}${V}${NC}\n" "This script installs RAYNET-OS with no manual steps required."
    printf "${DIM}${V}${NC}  %-66s${DIM}${V}${NC}\n" "It handles ownership, dependencies, database, and permissions."
    box_empty "$DIM"
    printf "${DIM}${V}${NC}  Running from: ${CYAN}%-52s${NC}${DIM}${V}${NC}\n" "$INSTALL_DIR"
    box_bot "$DIM"
    echo ""
    ask "Ready to begin installation? (y/N)"
    read -r CONFIRM
    if [[ ! "$CONFIRM" =~ ^[Yy]$ ]]; then
        echo -e "\n  ${DIM}Installation cancelled.${NC}\n"
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
