#!/usr/bin/env bash
# Start (or restart) the local demo: MariaDB + PHP built-in server.
# Safe to run repeatedly. Used for the sandbox preview; production uses Apache.
set -u

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PORT="${DEMO_PORT:-12000}"

# --- MariaDB -----------------------------------------------------------------
if ! mysqladmin ping --silent 2>/dev/null; then
    sudo mkdir -p /run/mysqld
    sudo chown mysql:mysql /run/mysqld 2>/dev/null
    sudo mariadbd-safe --user=mysql > /tmp/mariadb.log 2>&1 &
    for _ in $(seq 1 30); do
        mysqladmin ping --silent 2>/dev/null && break
        sleep 1
    done
fi

# --- PHP built-in server -----------------------------------------------------
if ! pgrep -f "php -S 0.0.0.0:${PORT}" >/dev/null 2>&1; then
    cd "$ROOT" || exit 1
    nohup php -S "0.0.0.0:${PORT}" scripts/dev-router.php \
        > /tmp/php-server.log 2>&1 &
    sleep 2
fi

curl -s -o /dev/null -w "demo on port ${PORT} -> %{http_code}\n" \
    "http://127.0.0.1:${PORT}/"
