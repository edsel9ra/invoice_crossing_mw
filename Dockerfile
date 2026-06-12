# =========================
# 1. Imagen base PHP
# =========================
FROM php:8.4-fpm-bookworm AS php-base

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    bash git curl ca-certificates unzip zip gnupg2 \
    autoconf g++ gcc make pkg-config re2c \
    libzip-dev libicu-dev libonig-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libxml2-dev libcurl4-openssl-dev libltdl-dev \
    && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://packages.microsoft.com/keys/microsoft.asc \
    | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg \
    && echo "deb [signed-by=/usr/share/keyrings/microsoft-prod.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" \
    > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql18 unixodbc-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        zip \
        bcmath \
        intl \
        gd \
        opcache

ARG SQLSRV_VERSION=5.13.1

RUN pecl install sqlsrv-${SQLSRV_VERSION} pdo_sqlsrv-${SQLSRV_VERSION} \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# =========================
# 2. Dependencias Composer
# =========================
FROM php-base AS vendor

WORKDIR /build

COPY src/composer.json src/composer.lock ./

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress \
    --no-scripts

# =========================
# 3. Build de Vite
# =========================
FROM node:22-alpine AS frontend

WORKDIR /build

COPY src/package.json src/pnpm-lock.yaml ./

RUN corepack enable pnpm \
    && pnpm install --frozen-lockfile

COPY src/ ./

COPY --from=vendor /build/vendor /build/vendor

RUN pnpm run build


# =========================
# 4. Imagen final PHP-FPM
# =========================
FROM php-base AS app

WORKDIR /var/www

COPY --from=vendor /build/vendor /var/www/vendor
COPY src/ /var/www
COPY --from=frontend /build/public/build /var/www/public/build

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN mkdir -p \
    storage/app/public \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs \
    bootstrap/cache \
    && ln -sfn /var/www/storage/app/public /var/www/public/storage \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/public \
    && chmod +x /usr/local/bin/entrypoint.sh

USER www-data

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]

# =========================
# 5. Imagen Nginx
# =========================
FROM nginx:alpine AS nginx

WORKDIR /var/www

COPY --from=app /var/www/public /var/www/public
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

RUN echo "server_tokens off;" > /etc/nginx/conf.d/security.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
