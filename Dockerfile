FROM php:8.4-fpm-alpine

# Dependências do sistema
RUN apk add --no-cache \
    nginx \
    supervisor \
    redis \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    icu-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev

# Extensões PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    mbstring \
    pcntl \
    bcmath \
    zip \
    opcache \
    intl \
    gd
RUN docker-php-ext-enable sodium

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dependências PHP (só produção)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Código da aplicação
COPY . .

# Executa scripts pós-install do composer (discovery de providers, etc.)
RUN composer run-script post-autoload-dump

# Permissões
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Configs do nginx e supervisor
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
COPY docker/release.sh /var/www/html/docker/release.sh
RUN chmod +x /entrypoint.sh /var/www/html/docker/release.sh

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
