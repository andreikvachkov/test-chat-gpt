#!/bin/sh
set -eu
psql --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" --set ON_ERROR_STOP=1 <<'SQL'
SELECT format('CREATE DATABASE %I OWNER %I', 'kamali_test', current_user)
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'kamali_test')
\gexec
SQL
