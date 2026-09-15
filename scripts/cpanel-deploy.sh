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
