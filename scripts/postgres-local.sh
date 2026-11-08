#!/usr/bin/env bash
# Private development PostgreSQL cluster; no TCP listener and no stored password.
set -euo pipefail
pg_root="${KAMALI_PG_ROOT:-/workspace/.toolchain/postgres-local}"
pg_port="${KAMALI_PG_PORT:-55432}"
pg_user="$(id -un)"
pg_data="$pg_root/data"
pg_socket="$pg_root/run"
pg_bindir="${KAMALI_PG_BINDIR:-/workspace/.toolchain/postgres/usr/lib/postgresql/17/bin}"
pg_share=/workspace/.toolchain/postgres/usr/share/postgresql/17
if [ ! -x "$pg_bindir/pg_ctl" ]; then
  if command -v pg_config >/dev/null 2>&1; then pg_bindir="$(pg_config --bindir)"; else
    echo 'Install PostgreSQL 17 server and client, or set KAMALI_PG_BINDIR.' >&2
    exit 1
  fi
fi
export LD_LIBRARY_PATH="/workspace/.toolchain/postgres/usr/lib/x86_64-linux-gnu${LD_LIBRARY_PATH:+:$LD_LIBRARY_PATH}"
case "${1:-start}" in
  start)
    mkdir -p "$pg_root" "$pg_socket"
    chmod 700 "$pg_root" "$pg_socket"
    if [ ! -f "$pg_data/PG_VERSION" ]; then
      pg_init_args=()
      if [ -d "$pg_share" ]; then pg_init_args+=(-L "$pg_share"); fi
      "$pg_bindir/initdb" -D "$pg_data" -U "$pg_user" --auth-local=peer --auth-host=scram-sha-256 --encoding=UTF8 --locale=C.UTF-8 "${pg_init_args[@]}"
    fi
    if ! "$pg_bindir/pg_ctl" -D "$pg_data" status >/dev/null 2>&1; then
      printf -v pg_options '%q ' -h '' -k "$pg_socket" -p "$pg_port" -c unix_socket_permissions=0700
      "$pg_bindir/pg_ctl" -D "$pg_data" -l "$pg_root/server.log" -o "$pg_options" -w start
    fi
    for pg_database in kamali kamali_test; do
      pg_exists="$("$pg_bindir/psql" -h "$pg_socket" -p "$pg_port" -U "$pg_user" -d postgres -Atqc "SELECT 1 FROM pg_database WHERE datname='$pg_database'")"
      if [ "$pg_exists" != '1' ]; then
        "$pg_bindir/createdb" -h "$pg_socket" -p "$pg_port" -U "$pg_user" "$pg_database"
      fi
    done
    "$pg_bindir/psql" -h "$pg_socket" -p "$pg_port" -U "$pg_user" -d kamali -Atqc 'SELECT 1' >/dev/null
    echo "PostgreSQL ready: private socket $pg_socket, port $pg_port; databases kamali and kamali_test."
    ;;
  stop) "$pg_bindir/pg_ctl" -D "$pg_data" -m fast -w stop ;;
  status) "$pg_bindir/pg_ctl" -D "$pg_data" status ;;
  *) echo 'Usage: scripts/postgres-local.sh [start|stop|status]' >&2; exit 2 ;;
esac
