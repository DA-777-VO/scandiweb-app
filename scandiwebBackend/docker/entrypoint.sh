#!/bin/bash
set -e

# Railway передаёт свой порт через $PORT, локально используем 8000
PORT="${PORT:-8000}"

sed -i "s/__PORT__/${PORT}/g" /etc/apache2/sites-available/000-default.conf
sed -i "s/__PORT__/${PORT}/g" /etc/apache2/ports.conf

exec "$@"