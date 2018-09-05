FROM composer
FROM php:7.2-fpm-alpine3.8

RUN apk add --no-cache \
        git

RUN set -xe \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        gmp-dev \
        icu-dev \
        util-linux-dev \
        libpng-dev \
        jpeg-dev \
        freetype-dev \
        imagemagick-dev \
        rabbitmq-c-dev \
        zlib-dev \
    && docker-php-ext-configure gd \
        --enable-gd-native-ttf \
        --with-jpeg-dir=/usr/lib \
        --with-freetype-dir=/usr/include/freetype2 \
    && docker-php-ext-install \
        bcmath \
        gd \
        gmp \
        intl \
        exif \
        pdo_mysql \
        zip \
    && pecl install amqp && docker-php-ext-enable amqp \
    && pecl install apcu && docker-php-ext-enable --ini-name 20-apcu.ini apcu \
    && pecl install imagick && docker-php-ext-enable imagick \
    && docker-php-ext-enable --ini-name 05-opcache.ini opcache \
    && pecl install redis && docker-php-ext-enable redis \
    && pecl install uuid && docker-php-ext-enable uuid \
    && runDeps="$( \
        scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
            | tr ',' '\n' \
            | sort -u \
            | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
    )" \
    && apk add --no-cache --virtual .php-phpexts-rundeps $runDeps \
    && apk del .build-deps

COPY entrypoint /usr/local/bin/
ENTRYPOINT ["entrypoint"]
