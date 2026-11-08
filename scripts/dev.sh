#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
if [ -x /workspace/.toolchain/bin/php ]; then export PATH="/workspace/.toolchain/bin:$PATH"; fi
exec php artisan serve --no-reload --host=0.0.0.0 --port="${PORT:-8000}"
