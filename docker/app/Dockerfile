FROM php:7.2-fpm

RUN apt-get update && apt-get install -y libmcrypt-dev mysql-client libsodium-dev libzip-dev zip \
    && pecl install mcrypt-1.0.1 \
    && docker-php-ext-enable mcrypt \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install sodium \
    && docker-php-ext-install bcmath \
    && docker-php-ext-configure zip --with-libzip \
    && docker-php-ext-install zip

# Install Composer
RUN apt-get update && \
    apt-get install -y --no-install-recommends git zip

RUN curl --silent --show-error https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www
