#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/../.."
compose_args=(--env-file .env.docker -f compose.yaml)
if [ -n "${KAMALI_COMPOSE_EXTRA:-}" ]; then compose_args+=(-f "$KAMALI_COMPOSE_EXTRA"); fi
compose() { docker compose "${compose_args[@]}" "$@"; }

# Local integration test: no volumes or application data are removed.
bash scripts/docker.sh up
web_before="$(compose ps -q web)"
app_before="$(compose ps -q app)"
network="$(docker inspect --format '{{range $name, $value := .NetworkSettings.Networks}}{{$name}}{{end}}' "$app_before")"
old_ip="$(docker inspect --format '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' "$app_before")"
app_image="$(docker inspect --format '{{.Config.Image}}' "$app_before")"
holder="kamali-address-holder-${app_before:0:12}"
health_log="$(mktemp)"
cleanup() {
  docker rm -f "$holder" >/dev/null 2>&1 || true
  rm -f "$health_log"
}
trap cleanup EXIT

# Occupy the released address so the new app definitely receives a different IP.
compose rm -sf app >/dev/null
docker run -d --name "$holder" --network "$network" --ip "$old_ip" \
  --entrypoint php "$app_image" -r 'sleep(300);' >/dev/null
compose up -d --no-deps --wait app
app_after="$(compose ps -q app)"
new_ip="$(docker inspect --format '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' "$app_after")"
if [ "$old_ip" = "$new_ip" ]; then echo 'Test did not change the app address.' >&2; exit 1; fi
docker rm -f "$holder" >/dev/null

# Docker DNS may retain the former address briefly; HTTP must recover on its own.
ready=false
for attempt in {1..15}; do
  if compose exec -T app php docker/check-ready.php >"$health_log" 2>&1; then ready=true; break; fi
  sleep 1
done
if [ "$ready" != true ]; then cat "$health_log" >&2; exit 1; fi
if [ "$(compose ps -q web)" != "$web_before" ]; then echo 'Web was unexpectedly recreated during the DNS test.' >&2; exit 1; fi
echo 'PASS: HTTP recovers after the PHP address changes without restarting Nginx.'

# ZIP extraction replaces file inodes. Directory mounts must keep the current file accessible.
replacement="$(mktemp docker/nginx/nginx.conf.XXXXXX)"
cp docker/nginx/nginx.conf "$replacement"
chmod 0644 "$replacement"
mv "$replacement" docker/nginx/nginx.conf
compose restart web
compose up -d --no-deps --wait web
compose exec -T web nginx -t -c /etc/kamali-nginx/nginx.conf
compose exec -T app php docker/check-ready.php
echo 'PASS: Nginx restarts after the config file is replaced, as during ZIP extraction.'

# The supported startup command must also replace an existing web container.
bash scripts/docker.sh up
if [ "$(compose ps -q web)" = "$web_before" ]; then echo 'Startup did not recreate web.' >&2; exit 1; fi
echo 'PASS: startup recreates web and checks PHP HTTP readiness; application data is retained.'
