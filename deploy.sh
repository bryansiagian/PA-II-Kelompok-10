#!/bin/bash
# =============================================================
# deploy.sh — E-Pharma deploy ke VPS Jagoanhosting
# Jalankan dari PC lokal: bash deploy.sh {setup|deploy|ssl|all}
# =============================================================

set -e

# ─── KONFIGURASI ──────────────────────────────────────────────
VPS_IP="IP_VPS_KAMU"       # Ganti dengan IP VPS dari Jagoanhosting
VPS_USER="root"
APP_DIR="/var/www/epharma"

# URL repo GitHub (sesuaikan dengan repo tim)
REPO_MAIN="https://github.com/USERNAME/distribusi_obat.git"
REPO_AUTH="https://github.com/USERNAME/auth_service.git"
REPO_REPORT="https://github.com/USERNAME/report_service.git"
# ──────────────────────────────────────────────────────────────

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'
log()    { echo -e "${GREEN}[✓] $1${NC}"; }
warn()   { echo -e "${YELLOW}[!] $1${NC}"; }
error()  { echo -e "${RED}[✗] $1${NC}"; exit 1; }
header() { echo -e "\n${BLUE}═══ $1 ═══${NC}"; }

# ─── 1. SETUP VPS (jalankan sekali) ───────────────────────────
setup_vps() {
    header "SETUP VPS"
    ssh ${VPS_USER}@${VPS_IP} bash << 'REMOTE'
        set -e
        apt-get update && apt-get upgrade -y
        # Install Docker
        if ! command -v docker &>/dev/null; then
            curl -fsSL https://get.docker.com | sh
            systemctl enable docker && systemctl start docker
            echo "Docker terinstall"
        else
            echo "Docker sudah ada"
        fi
        # Install Docker Compose plugin
        if ! docker compose version &>/dev/null 2>&1; then
            apt-get install -y docker-compose-plugin
        fi
        echo "VPS siap!"
REMOTE
    log "VPS setup selesai"
}

# ─── 2. DEPLOY ────────────────────────────────────────────────
deploy() {
    header "DEPLOY APLIKASI"

    # Cek .env ada
    [ -f ".env" ] || error "File .env tidak ditemukan. Buat dari .env.example dulu."

    # Buat direktori di VPS
    ssh ${VPS_USER}@${VPS_IP} "mkdir -p ${APP_DIR}/docker"

    # Upload file konfigurasi
    log "Upload file..."
    scp docker-compose.yml ${VPS_USER}@${VPS_IP}:${APP_DIR}/
    scp .env               ${VPS_USER}@${VPS_IP}:${APP_DIR}/
    scp docker/*.Dockerfile      ${VPS_USER}@${VPS_IP}:${APP_DIR}/docker/
    scp docker/nginx.conf        ${VPS_USER}@${VPS_IP}:${APP_DIR}/docker/
    scp docker/nginx-proxy.conf  ${VPS_USER}@${VPS_IP}:${APP_DIR}/docker/
    scp docker/supervisord.conf  ${VPS_USER}@${VPS_IP}:${APP_DIR}/docker/
    scp docker/entrypoint.sh     ${VPS_USER}@${VPS_IP}:${APP_DIR}/docker/
    scp docker/mysql-init.sql    ${VPS_USER}@${VPS_IP}:${APP_DIR}/docker/
    log "Upload selesai"

    # Clone / pull repo di VPS
    ssh ${VPS_USER}@${VPS_IP} bash << REMOTE
        set -e
        cd ${APP_DIR}

        # Clone repo jika belum ada, pull jika sudah ada
        clone_or_pull() {
            local dir=\$1
            local repo=\$2
            if [ ! -d "\$dir" ]; then
                echo "Cloning \$dir..."
                git clone \$repo \$dir
            else
                echo "Pulling \$dir..."
                git -C \$dir pull origin main
            fi
        }

        clone_or_pull distribusi_obat ${REPO_MAIN}
        clone_or_pull auth_service    ${REPO_AUTH}
        clone_or_pull report_service  ${REPO_REPORT}

        # Build dan jalankan
        docker compose down --remove-orphans || true
        docker compose build --no-cache
        docker compose up -d

        # Tunggu MySQL healthy
        echo "Menunggu MySQL siap..."
        until docker compose exec mysql mysqladmin ping -h localhost --silent 2>/dev/null; do
            echo -n "."; sleep 2
        done
        echo ""

        # Migrate semua service
        echo "Migrasi distribusi_obat..."
        docker compose exec distribusi_obat php artisan migrate --force
        docker compose exec distribusi_obat php artisan db:seed --force 2>/dev/null || true
        docker compose exec distribusi_obat php artisan storage:link 2>/dev/null || true
        docker compose exec distribusi_obat php artisan optimize

        echo "Migrasi auth_service..."
        docker compose exec auth_service php artisan migrate --force
        docker compose exec auth_service php artisan db:seed --force 2>/dev/null || true
        docker compose exec auth_service php artisan optimize

        echo "Migrasi report_service..."
        docker compose exec report_service php artisan migrate --force
        docker compose exec report_service php artisan optimize

        echo "Deploy selesai!"
        docker compose ps
REMOTE
    log "Aplikasi berjalan di VPS"
}

# ─── 3. SETUP SSL ─────────────────────────────────────────────
setup_ssl() {
    header "SETUP SSL (Certbot)"

    # Baca domain dan email dari .env
    MAIN_DOMAIN=$(grep '^MAIN_APP_URL' .env | cut -d'=' -f2 | sed 's|https\?://||')
    AUTH_DOMAIN=$(grep '^AUTH_SERVICE_URL' .env | cut -d'=' -f2 | sed 's|https\?://||')
    REPORT_DOMAIN=$(grep '^REPORT_SERVICE_URL' .env | cut -d'=' -f2 | sed 's|https\?://||')
    MAIL=$(grep '^MAIL_USERNAME' .env | cut -d'=' -f2)

    warn "Pastikan DNS sudah mengarah ke ${VPS_IP} sebelum lanjut!"
    warn "  Main:   ${MAIN_DOMAIN}"
    warn "  Auth:   ${AUTH_DOMAIN}"
    warn "  Report: ${REPORT_DOMAIN}"
    read -p "DNS sudah dikonfigurasi? (y/n): " dns_ok
    [ "$dns_ok" = "y" ] || { warn "SSL dilewati."; return; }

    ssh ${VPS_USER}@${VPS_IP} bash << REMOTE
        set -e
        cd ${APP_DIR}

        # Minta SSL untuk semua domain sekaligus
        docker compose run --rm certbot certonly \
            --webroot -w /var/www/certbot \
            --email "${MAIL}" \
            --agree-tos --no-eff-email \
            -d "${MAIN_DOMAIN}" \
            -d "www.${MAIN_DOMAIN}" \
            -d "${AUTH_DOMAIN}" \
            -d "${REPORT_DOMAIN}"

        # Reload nginx agar SSL aktif
        docker compose exec nginx_proxy nginx -s reload
        echo "SSL aktif!"
REMOTE
    log "SSL selesai untuk semua domain"
}

# ─── 4. UPDATE (tanpa rebuild) ────────────────────────────────
update() {
    header "UPDATE KODE"
    ssh ${VPS_USER}@${VPS_IP} bash << REMOTE
        set -e
        cd ${APP_DIR}
        git -C distribusi_obat pull origin main
        git -C auth_service pull origin main
        git -C report_service pull origin main

        docker compose build --no-cache
        docker compose up -d --force-recreate

        docker compose exec distribusi_obat php artisan migrate --force
        docker compose exec distribusi_obat php artisan optimize
        docker compose exec auth_service php artisan migrate --force
        docker compose exec auth_service php artisan optimize
        docker compose exec report_service php artisan migrate --force
        docker compose exec report_service php artisan optimize
        echo "Update selesai!"
REMOTE
    log "Update selesai"
}

# ─── MAIN ─────────────────────────────────────────────────────
case "$1" in
    setup)  setup_vps ;;
    deploy) deploy ;;
    ssl)    setup_ssl ;;
    update) update ;;
    all)
        setup_vps
        deploy
        setup_ssl
        ;;
    *)
        echo "Usage: bash deploy.sh {setup|deploy|ssl|update|all}"
        echo ""
        echo "  setup   — Install Docker di VPS (jalankan sekali)"
        echo "  deploy  — Upload file & jalankan semua container"
        echo "  ssl     — Setup SSL gratis via Certbot"
        echo "  update  — Pull kode terbaru & restart container"
        echo "  all     — setup + deploy + ssl sekaligus"
        ;;
esac