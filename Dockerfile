FROM node:22-alpine AS frontend
WORKDIR /build
COPY src/package.json src/pnpm-lock.yaml ./
RUN corepack enable pnpm && pnpm install --frozen-lockfile
COPY src/ .
RUN pnpm run build

FROM composer:2 AS vendor
WORKDIR /build
COPY src/composer.json src/composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

FROM php:8.4-fpm AS php-base
RUN apt-get update && apt-get install -y \
    bash git curl unzip zip \
    libicu-dev libonig-dev libzip-dev libpng-dev \
    libjpeg62-turbo-dev libfreetype6-dev libxml2-dev \
    libcurl4-openssl-dev gnupg2 dirmngr \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    bcmath curl exif gd intl mbstring opcache pcntl pdo_mysql zip

RUN curl -sSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg \
    && curl -sSL https://packages.microsoft.com/config/debian/12/prod.list > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql18 unixodbc-dev \
    && pecl install pdo_sqlsrv sqlsrv \
    && docker-php-ext-enable pdo_sqlsrv sqlsrv \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini

FROM php-base AS app
COPY --from=vendor /build/vendor /var/www/vendor
COPY --from=frontend /build/public/build /var/www/public/build
COPY --from=frontend /build/node_modules /var/www/node_modules
COPY src/ /var/www
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
EXPOSE 9000

FROM nginx:alpine AS nginx
COPY --from=app /var/www/public /var/www/public
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
RUN echo "server_tokens off;" > /etc/nginx/conf.d/security.conf
WORKDIR /var/www
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
