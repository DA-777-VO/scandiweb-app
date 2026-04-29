FROM php:8.2-apache

# ── System dependencies & PHP extensions ──────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev \
        zip \
        unzip \
        curl \
        dos2unix \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mysqli \
        zip \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ── Composer ──────────────────────────────────────────────────────────────────
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# ── Apache configuration ──────────────────────────────────────────────────────
# These are patched at runtime by entrypoint.sh to use Railway's $PORT
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/ports.conf  /etc/apache2/ports.conf

# ── Entrypoint: patches Apache port from $PORT at container start ─────────────
COPY docker/entrypoint.sh /entrypoint.sh
# Strip Windows CRLF line endings (file may be created on Windows)
RUN dos2unix /entrypoint.sh && chmod +x /entrypoint.sh

# ── Application code ──────────────────────────────────────────────────────────
WORKDIR /var/www/html

# Copy dependency manifests first (better layer caching)
COPY composer.json ./

RUN composer install \
        --no-dev \
        --optimize-autoloader \
        --no-scripts \
        --no-interaction

# Copy the rest of the application
COPY . .

RUN chown -R www-data:www-data /var/www/html

# Railway will override this with its own $PORT at runtime
EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]
