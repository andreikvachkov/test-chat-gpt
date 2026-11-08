#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
project_root="$(pwd)"
# An optional local override supports managed-cloud CA/proxy mounts without shipping them to users.
compose_args=(--env-file .env.docker -f compose.yaml)
if [ -n "${KAMALI_COMPOSE_EXTRA:-}" ]; then compose_args+=(-f "$KAMALI_COMPOSE_EXTRA"); fi
compose() { docker compose "${compose_args[@]}" "$@"; }
initialize() {
  if [ "$(id -u)" -eq 0 ]; then
    echo 'Run this command as your regular WSL Linux user, not root.' >&2
    exit 1
  fi
  docker run --rm --user "$(id -u):$(id -g)" \
    --mount "type=bind,source=$project_root,target=/var/www/html" --workdir /var/www/html \
    -e KAMALI_LOCAL_UID="$(id -u)" -e KAMALI_LOCAL_GID="$(id -g)" \
    php:8.4-fpm-bookworm php docker/init-env.php
}
command="${1:-help}"
if [ "$#" -gt 0 ]; then shift; fi
case "$command" in
  init) initialize ;;
  setup)
    initialize
    compose config --quiet
    compose build app
    compose up -d --wait db
    compose stop vite
    # Only the dependency volumes need initial ownership; the checkout uses the WSL user's UID.
    compose run --rm --no-deps --user 0:0 app sh -ec 'chown -R "$LOCAL_UID:$LOCAL_GID" /var/www/html/vendor /var/www/html/node_modules'
    compose run --rm --no-deps app composer install --no-interaction --prefer-dist
    compose run --rm --no-deps node npm ci
    compose run --rm --no-deps app php docker/prepare.php
    compose run --rm --no-deps node npm run build
    compose up -d --wait app web scheduler
    compose exec -T app php docker/check-ready.php
    ;;
  up)
    compose stop vite
    compose run --rm --no-deps app php -r 'if (is_file("public/hot")) unlink("public/hot");'
    compose up -d --wait db app web scheduler
    compose exec -T app php docker/check-ready.php
    ;;
  dev) compose --profile dev up -d --wait db app web scheduler vite ;;
  down) compose --profile dev down ;;
  status) compose --profile dev ps ;;
  logs) compose logs --tail=100 "$@" ;;
  build)
    compose run --rm --no-deps node npm ci
    compose run --rm --no-deps node npm run build
    ;;
  test)
    compose up -d --wait db
    compose exec -T db sh /docker-entrypoint-initdb.d/10-test-database.sh
    compose run --rm --no-deps \
      -e APP_ENV=testing -e DB_DATABASE=kamali_test \
      -e SESSION_DRIVER=array -e CACHE_STORE=array \
      -e QUEUE_CONNECTION=sync -e MAIL_MAILER=array \
      app php artisan test "$@"
    ;;
  admin)
    if [ "$#" -ne 1 ]; then echo 'Usage: bash scripts/docker.sh admin email@example.com' >&2; exit 2; fi
    compose exec app php artisan shop:admin "$1"
    ;;
  artisan) compose exec -T app php artisan "$@" ;;
  *)
    echo 'Usage: bash scripts/docker.sh {init|setup|up|dev|down|status|logs|build|test|admin EMAIL|artisan ...}'
    ;;
esac
