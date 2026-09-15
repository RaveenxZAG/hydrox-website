#!/bin/bash
set -euo pipefail

# Keep expansion here rather than in cPanel's nested shell command.
cd "$(dirname "$0")/.."
export PATH="/usr/local/bin:/usr/bin:/bin:$PATH"
if [ ! -f .env ] && [ -f /home/hydro851/persistent/hydrox-website/.env ]; then
    ln -s /home/hydro851/persistent/hydrox-website/.env .env
fi
if [ ! -f .env ]; then
    echo 'ERROR - .env file missing'
    exit 1
fi
find storage bootstrap/cache -type d -exec chmod 775 {} +
find storage bootstrap/cache -type f ! -name .gitignore -exec chmod 664 {} +

hydrox_php_bin=''
for candidate in \
    /opt/cpanel/ea-php83/root/usr/bin/php \
    /opt/cpanel/ea-php82/root/usr/bin/php \
    /opt/alt/php83/usr/bin/php /opt/alt/php82/usr/bin/php \
    /usr/local/bin/ea-php83 /usr/local/bin/ea-php82 \
    /usr/bin/ea-php83 /usr/bin/ea-php82 \
    /usr/local/bin/php /usr/bin/php; do
    if [ -x "$candidate" ] && "$candidate" -r 'exit(PHP_VERSION_ID >= 80200 && PHP_SAPI === "cli" ? 0 : 1);'; then
        hydrox_php_bin="$candidate"
        break
    fi
done
if [ -z "$hydrox_php_bin" ]; then
    echo 'ERROR - No suitable PHP 8.2+ CLI binary found'
    exit 1
fi
echo "Using PHP - $hydrox_php_bin"
"$hydrox_php_bin" -v
"$hydrox_php_bin" artisan hydrox:deploy --no-interaction

# Only this explicitly selected cPanel checkout publishes the public website.
# Other clones (including the Portal) must never alter hydrox.au.
if [ "$(pwd -P)" = /home/hydro851/repositories/hydrox-website-sqlite ]; then
    hydrox_public_root=/home/hydro851/public_html
    hydrox_backup_root=/home/hydro851/persistent/hydrox-website/backups
    test -d "$hydrox_public_root"
    if [ -e "$hydrox_public_root/storage" ] && [ ! -L "$hydrox_public_root/storage" ]; then
        echo 'ERROR - public_html/storage is a real file or directory; refusing to replace it'
        exit 1
    fi
    mkdir -p "$hydrox_backup_root"
    chmod 750 "$hydrox_backup_root"
    if [ -f "$hydrox_public_root/index.php" ]; then
        cp -p "$hydrox_public_root/index.php" "$hydrox_backup_root/public-index-$(date -u +%Y%m%dT%H%M%S)-$$.php"
    fi
    # Preserve cPanel's PHP handler, ACME files, and unrelated files. No --delete.
    shopt -s dotglob nullglob
    for hydrox_asset in public/*; do
        case "${hydrox_asset##*/}" in
            index.php|storage|.htaccess|.user.ini) continue ;;
        esac
        cp -a "$hydrox_asset" "$hydrox_public_root/"
    done
    ln -sfnT /home/hydro851/persistent/hydrox-website/storage/app/public "$hydrox_public_root/storage"
    cp scripts/cpanel-public-index.php "$hydrox_public_root/.hydrox-index-next.php"
    chmod 644 "$hydrox_public_root/.hydrox-index-next.php"
    mv -f "$hydrox_public_root/.hydrox-index-next.php" "$hydrox_public_root/index.php"
    echo 'Live public files published from hydrox-website-sqlite; previous index backed up.'
fi
