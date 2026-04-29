#!/bin/bash
set -e

echo "=== entrypoint.sh started ==="
echo "PORT env value: '${PORT}'"

# Жёстко берём порт или fallback
if [ -z "${PORT}" ]; then
    echo "WARNING: PORT is empty, using 8080"
    APP_PORT="8080"
else
    APP_PORT="${PORT}"
fi

echo "Using port: ${APP_PORT}"

# Перезаписываем файлы полностью — никакого sed
echo "Listen ${APP_PORT}" > /etc/apache2/ports.conf

cat > /etc/apache2/sites-available/000-default.conf <<EOF
<VirtualHost *:${APP_PORT}>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html/public

    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined

    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
        FallbackResource /index.php
    </Directory>
</VirtualHost>
EOF

echo "=== Apache config written ==="
echo "ports.conf: $(cat /etc/apache2/ports.conf)"

exec "$@"