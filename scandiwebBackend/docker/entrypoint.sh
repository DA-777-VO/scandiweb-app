#!/bin/bash
set -e

# Railway injects $PORT dynamically. Default to 8080 if not set.
PORT=${PORT:-8080}

echo "[entrypoint] Starting Apache on port $PORT"

# Patch ports.conf — replace any existing Listen directive
sed -i "s/Listen [0-9]*/Listen $PORT/" /etc/apache2/ports.conf

# Patch VirtualHost port in the default site config
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:$PORT>/" \
    /etc/apache2/sites-available/000-default.conf

# Pass execution to the CMD (apache2-foreground)
exec "$@"
