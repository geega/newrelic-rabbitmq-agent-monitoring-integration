# PHP dependency manager
FROM composer:2.1.9 AS composer

FROM php:7.4.3-cli

# Copies dependency manager into PHP image
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

# Install packages
RUN apt-get -qq update && apt-get -qq -y install \
    git \
    libzip-dev \
    unzip \
    && docker-php-ext-install \
       sockets \
       zip
