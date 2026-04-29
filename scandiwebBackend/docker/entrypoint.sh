#!/bin/bash
set -e

echo "=== entrypoint.sh started ==="
echo "PORT env value: '${PORT}'"

APP_PORT="${PORT:-8080}"
echo "Using port: ${APP_PORT}"

# Удаляем симлинки лишних MPM напрямую
rm -f /etc/apache2/mods-enabled/mpm_event.load
rm -f /etc/apache2/mods-enabled/mpm_event.conf
rm -f /etc/apache2/mods-enabled/mpm_worker.load
rm -f /etc/apache2/mods-enabled/mpm_worker.conf

# Включаем prefork
a2enmod mpm_prefork 2>/dev/null || true

# Перезаписываем конфиги портов и виртуального хоста
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