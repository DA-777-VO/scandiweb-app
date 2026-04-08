# Используем официальный PHP 8.2 с Apache
FROM php:8.2-apache

# ── Системные зависимости и PHP расширения ──────────────────────────────────

# Устанавливаем зависимости для pdo_mysql и других расширений
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mysqli \
        zip \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ── Composer ────────────────────────────────────────────────────────────────

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# ── Apache конфигурация ─────────────────────────────────────────────────────

# Устанавливаем DocumentRoot на папку public/
# и разрешаем .htaccess переопределения
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/ports.conf /etc/apache2/ports.conf

# ── Рабочая директория ──────────────────────────────────────────────────────

WORKDIR /var/www/html

# ── Устанавливаем зависимости Composer ──────────────────────────────────────
# Копируем только composer.json сначала — слой кэшируется если файл не менялся

COPY composer.json ./

RUN composer install --no-dev --optimize-autoloader --no-scripts

# ── Копируем весь код ────────────────────────────────────────────────────────
# При использовании bind mount в docker-compose это не используется для разработки,
# но нужно для сборки standalone образа

COPY . .

# ── Права на файлы ───────────────────────────────────────────────────────────

RUN chown -R www-data:www-data /var/www/html

# ── Открываем порт ───────────────────────────────────────────────────────────

EXPOSE 8000

# Apache слушает на 80 по умолчанию, но мы переключим на 8000
CMD ["apache2-foreground"]
