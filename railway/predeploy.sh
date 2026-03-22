#!/usr/bin/env bash
set -euo pipefail

echo "[railway] running database migrations..."
php artisan migrate --force
