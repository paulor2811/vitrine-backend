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
    postgresql-dev

# Extensões PHP
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    mbstring \
    pcntl \
    bcmath \
    zip \
    opcache

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
RUN chmod +x /entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
