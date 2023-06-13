FROM php:7.4.33-fpm-alpine

RUN set -eux; \
    apk add --no-cache --virtual .build-deps $PHPIZE_DEPS libxml2-dev oniguruma-dev \
    && docker-php-ext-install -j$(nproc) \
        dom \
        json \
        mbstring \
        tokenizer \
        xml \
        xmlwriter \
    && apk del -f .build-deps \
    && rm -rf /var/cache/apk/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html
