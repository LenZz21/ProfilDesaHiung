#!/usr/bin/env bash
set -euo pipefail

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
