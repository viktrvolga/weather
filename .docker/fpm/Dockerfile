FROM php:8.0-fpm-alpine

RUN set -xe \
  && apk update \
    && apk add oniguruma-dev autoconf zlib-dev make gcc g++ libzip-dev \
    && docker-php-ext-install \
        bcmath \
        pcntl \
        mbstring \
        sysvsem \
        zip

# Install Composer
RUN curl --silent --show-error https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer && \
    # composer global require hirak/prestissimo && \
    composer clear-cache

ENV HOME /var/www
WORKDIR /var/www

COPY ./boot.sh /usr/local/bin/boot_fpm

CMD ["sh", "/usr/local/bin/boot_fpm"]
