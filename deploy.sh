#!/bin/bash
# ─────────────────────────────────────────────────────────────────────────────
# EZ-Store Deploy Script
# Usage:
#   ./deploy.sh          → Full deploy (pull + build + migrate + restart)
#   ./deploy.sh tenants  → Jalankan tenant migrations saja (setelah update)
#   ./deploy.sh restart  → Restart semua container tanpa rebuild
# ─────────────────────────────────────────────────────────────────────────────
set -e

ACTION="${1:-full}"

case "$ACTION" in
  full)
    echo ">>> [1/5] Pulling latest code..."
    git pull origin main

    echo ">>> [2/5] Building Docker image..."
    docker compose build app queue

    echo ">>> [3/5] Starting services..."
    docker compose up -d --remove-orphans

    echo ">>> [4/5] Running tenant migrations..."
    # Wait for app to finish startup migrations
    sleep 10
    docker compose exec app php artisan tenants:migrate --force

    echo ">>> [5/5] Restarting queue worker..."
    docker compose restart queue

    echo ""
    echo "✓ Deploy selesai!"
    docker compose ps
    ;;

  tenants)
    echo ">>> Menjalankan tenant migrations..."
    docker compose exec app php artisan tenants:migrate --force
    echo "✓ Selesai."
    ;;

  restart)
    echo ">>> Restarting containers..."
    docker compose restart
    echo "✓ Selesai."
    docker compose ps
    ;;

  logs)
    docker compose logs -f --tail=100
    ;;

  *)
    echo "Usage: $0 [full|tenants|restart|logs]"
    exit 1
    ;;
esac
