FROM php:8.3-fpm

RUN set -eux; \
  apt-get update; \
  apt-get install -y --no-install-recommends ghostscript; \
  rm -rf /var/lib/apt/lists/*

# Install PHP extensions
# https://make.wordpress.org/hosting/handbook/handbook/server-environment/#php-extensions
RUN set -ex; \
  apt-get update; \
  apt-get install -y --no-install-recommends \
  libfreetype6-dev \
  libicu-dev \
  libjpeg-dev \
  libmagickwand-dev \
  libpng-dev \
  libwebp-dev \
  libzip-dev; \
  docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
  docker-php-ext-install -j "$(nproc)" \
  bcmath \
  exif \
  gd \
  intl \
  mysqli \
  zip; \
  apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
  rm -rf /var/lib/apt/lists/*;

RUN pecl install xdebug

# Set recommended PHP.ini settings
# See https://secure.php.net/manual/en/opcache.installation.php
RUN set -eux; docker-php-ext-enable opcache xdebug;
COPY ./docker/php/error-reporting.ini /usr/local/etc/php/conf.d/error-logging.ini
COPY ./docker/php/opcache-recommended.ini /usr/local/etc/php/conf.d/opcache-recommended.ini
COPY ./docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
COPY ./docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

WORKDIR /var/www/html
VOLUME /var/www/html

CMD ["php-fpm"]