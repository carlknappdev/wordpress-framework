FROM composer:latest

ENV DEBIAN_FRONTEND=noninteractive

RUN apk update && apk upgrade && \
    apk add --no-cache build-base autoconf

RUN pecl install ast \
    && docker-php-ext-enable ast
