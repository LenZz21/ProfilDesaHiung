#!/usr/bin/env bash
set -euo pipefail

echo "[railway] clearing Laravel caches..."
php artisan optimize:clear >/dev/null 2>&1 || true

db_connection="${DB_CONNECTION:-}"
db_url="${DB_URL:-}"
db_host="${DB_HOST:-}"
db_port="${DB_PORT:-}"
db_database="${DB_DATABASE:-}"

# Fallback: map Railway MySQL plugin vars when DB_* is empty.
if [ -z "${db_url}" ] && [ -n "${MYSQL_URL:-}" ]; then
  export DB_URL="${MYSQL_URL}"
  db_url="${DB_URL}"
fi

if [ -z "${db_host}" ] && [ -n "${MYSQLHOST:-}" ]; then
  export DB_HOST="${MYSQLHOST}"
  db_host="${DB_HOST}"
fi

if [ -z "${db_port}" ] && [ -n "${MYSQLPORT:-}" ]; then
  export DB_PORT="${MYSQLPORT}"
  db_port="${DB_PORT}"
fi

if [ -z "${db_database}" ] && [ -n "${MYSQLDATABASE:-}" ]; then
  export DB_DATABASE="${MYSQLDATABASE}"
  db_database="${DB_DATABASE}"
fi

if [ -z "${DB_USERNAME:-}" ] && [ -n "${MYSQLUSER:-}" ]; then
  export DB_USERNAME="${MYSQLUSER}"
fi

if [ -z "${DB_PASSWORD:-}" ] && [ -n "${MYSQLPASSWORD:-}" ]; then
  export DB_PASSWORD="${MYSQLPASSWORD}"
fi

if [[ "${db_url}" == *'${{'* ]]; then
  echo "[railway] fatal: DB_URL still contains unresolved template expression: ${db_url}"
  echo "[railway] check variable reference and database service name casing."
  exit 1
fi

echo "[railway] database env summary: DB_CONNECTION=${db_connection:-<empty>} DB_HOST=${db_host:-<empty>} DB_PORT=${db_port:-<empty>} DB_DATABASE=${db_database:-<empty>} DB_URL_SET=$([ -n "${db_url}" ] && echo yes || echo no)"

if [ "${db_connection}" = "mysql" ] && [ -z "${db_url}" ] && { [ -z "${db_host}" ] || [ "${db_host}" = "127.0.0.1" ] || [ "${db_host}" = "localhost" ]; }; then
  echo "[railway] fatal: MySQL is selected but DB host is still local (${db_host:-<empty>})."
  echo "[railway] set DB_URL=\${{MySQL.MYSQL_URL}} or set DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD from Railway service variables."
  exit 1
fi

echo "[railway] running database migrations..."

max_attempts=10
attempt=1

while true; do
  if php artisan migrate --force; then
    echo "[railway] migrations completed"
    break
  fi

  if [ "$attempt" -ge "$max_attempts" ]; then
    echo "[railway] migration failed after ${max_attempts} attempts"
    exit 1
  fi

  attempt=$((attempt + 1))
  echo "[railway] migration failed, retrying in 5s (attempt ${attempt}/${max_attempts})..."
  sleep 5
done
