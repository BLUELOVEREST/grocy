FROM node:22-alpine AS frontend-deps
WORKDIR /app
RUN apk add --no-cache git ca-certificates
COPY package.json yarn.lock .yarnrc ./
RUN corepack enable \
	&& yarn install --frozen-lockfile

FROM php:8.5-cli-bookworm AS php-deps
WORKDIR /app
RUN apt-get update \
	&& apt-get install -y --no-install-recommends git unzip ca-certificates \
	&& rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader \
	--ignore-platform-req=ext-gd \
	--ignore-platform-req=ext-intl

FROM php:8.5-apache-bookworm AS runtime
ARG ERIC_GROCY_VERSION=4.6.0-eric.1
ARG GROCY_BASE_VERSION=4.6.0
LABEL org.opencontainers.image.title="Eric Grocy"
LABEL org.opencontainers.image.description="Personal Grocy build with Eric shopping list customizations"
LABEL org.opencontainers.image.version="${ERIC_GROCY_VERSION}"
LABEL org.opencontainers.image.base.version="${GROCY_BASE_VERSION}"

ENV GROCY_DATAPATH=/var/www/html/data \
	APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
		curl \
		libfreetype6-dev \
		libicu-dev \
		libjpeg62-turbo-dev \
		libonig-dev \
		libpng-dev \
		libsqlite3-dev \
		libzip-dev \
		zlib1g-dev \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install -j"$(nproc)" gd intl mbstring pdo_sqlite \
	&& a2enmod rewrite headers \
	&& sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
	&& printf '<Directory /var/www/html/public>\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n' > /etc/apache2/conf-available/grocy-public.conf \
	&& a2enconf grocy-public \
	&& rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . .
COPY --from=php-deps /app/packages ./packages
COPY --from=frontend-deps /app/public/packages ./public/packages
COPY docker/entrypoint.sh /usr/local/bin/eric-grocy-entrypoint
RUN chmod +x /usr/local/bin/eric-grocy-entrypoint \
	&& mkdir -p data/viewcache data/settingoverrides data/storage \
	&& chown -R www-data:www-data data

EXPOSE 80
VOLUME ["/var/www/html/data"]
HEALTHCHECK --interval=30s --timeout=5s --retries=5 CMD curl -fsS http://127.0.0.1/ || exit 1
ENTRYPOINT ["eric-grocy-entrypoint"]
CMD ["apache2-foreground"]
