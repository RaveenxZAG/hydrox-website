#!/usr/bin/env bash
set -euo pipefail

# ==============================================================================
# Hydrox Website - SQLite Daily Backup Script
# Safe for cPanel cron execution (HostPapa environment)
# ==============================================================================

# 1. Locate project root directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "${SCRIPT_DIR}")"

# If deployed in persistent cPanel structure, default to repository path
if [ -d "/home/hydro851/repositories/hydrox-website" ]; then
    PROJECT_ROOT="/home/hydro851/repositories/hydrox-website"
fi

cd "${PROJECT_ROOT}"

# 2. Locate cPanel PHP binary
PHP_BIN=""
CANDIDATES=(
    "/usr/local/bin/ea-php83"
    "/usr/local/bin/ea-php82"
    "/opt/cpanel/ea-php83/root/usr/bin/php"
    "/opt/cpanel/ea-php82/root/usr/bin/php"
    "$(command -v php 2>/dev/null || true)"
)

for candidate in "${CANDIDATES[@]}"; do
    if [ -n "${candidate}" ] && [ -x "${candidate}" ]; then
        PHP_BIN="${candidate}"
        break
    fi
done

if [ -z "${PHP_BIN}" ]; then
    echo "[ERROR] No valid PHP binary found for backup execution." >&2
    exit 1
fi

echo "[INFO] Running Hydrox SQLite backup using ${PHP_BIN}..."
"${PHP_BIN}" artisan hydrox:backup-sqlite --compress --keep=14

echo "[INFO] Backup execution completed successfully."
exit 0
