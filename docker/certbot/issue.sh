#!/bin/bash
# Run this ONCE to issue the initial wildcard SSL certificate via Cloudflare DNS.
# Prerequisites:
#   1. docker/certbot/cloudflare.ini is filled with your Cloudflare API token
#   2. DNS wildcard A record (*) already points to this server's IP
#   3. docker compose is running

set -e

DOMAIN="${1:-ezstore.com}"

echo ">>> Issuing wildcard SSL for ${DOMAIN} and *.${DOMAIN}"

docker compose run --rm certbot certonly \
    --dns-cloudflare \
    --dns-cloudflare-credentials /cloudflare.ini \
    --dns-cloudflare-propagation-seconds 30 \
    -d "${DOMAIN}" \
    -d "*.${DOMAIN}" \
    --email "admin@${DOMAIN}" \
    --agree-tos \
    --no-eff-email

echo ">>> Certificate issued! Reloading Nginx..."
docker compose exec nginx nginx -s reload

echo ">>> Done. Certificate stored in the ssl_certs volume."
