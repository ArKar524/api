#!/usr/bin/env bash
set -euo pipefail

COMPOSE_FILE="${COMPOSE_FILE:-compose.yaml}"
APP_SERVICE="${APP_SERVICE:-app}"
WEB_SERVICE="${WEB_SERVICE:-nginx}"

if ! command -v docker >/dev/null 2>&1; then
  echo "docker is not installed."
  exit 1
fi

echo "==> Building latest images from ${COMPOSE_FILE}"
docker compose -f "${COMPOSE_FILE}" build "${APP_SERVICE}" "${WEB_SERVICE}"

echo "==> Recreating containers with new code"
docker compose -f "${COMPOSE_FILE}" up -d --no-deps "${APP_SERVICE}" "${WEB_SERVICE}"

echo "==> Running Laravel migrations"
docker compose -f "${COMPOSE_FILE}" exec -T "${APP_SERVICE}" php artisan migrate --force

echo "==> Refreshing Laravel caches"
docker compose -f "${COMPOSE_FILE}" exec -T "${APP_SERVICE}" php artisan optimize:clear
docker compose -f "${COMPOSE_FILE}" exec -T "${APP_SERVICE}" php artisan optimize

echo "==> Deployment completed."
