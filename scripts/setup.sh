#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
# Standard PHP 8.4+/Composer environment; cloud-local tools are reused when present.
if [ -x /workspace/.toolchain/bin/php ]; then
  export PATH="/workspace/.toolchain/bin:$PATH"
fi
if command -v composer >/dev/null 2>&1; then
  composer install --no-interaction --prefer-dist
elif [ -f /workspace/.toolchain/composer-new.phar ]; then
  php /workspace/.toolchain/composer-new.phar install --no-interaction --prefer-dist
else
  echo 'Install PHP 8.4+ and Composer 2.9.5+ first (see README.md).' >&2
  exit 1
fi
npm ci
if [ ! -f .env ]; then cp .env.example .env; fi
if ! php -r 'exit(preg_match("/^APP_KEY=.+$/m", file_get_contents(".env")) ? 0 : 1);'; then php artisan key:generate --no-interaction; fi
mkdir -p storage/app/public
# The PostgreSQL server and target database must already exist.
php artisan migrate --seed --force
if [ ! -L public/storage ]; then php artisan storage:link; fi
php artisan filament:assets
npm run build
