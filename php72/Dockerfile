FROM composer
FROM php:7.2-fpm-alpine3.8

RUN apk add --no-cache \
        git \
        fcgi \
        imagemagick

RUN set -xe \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        gmp-dev \
        icu-dev \
        util-linux-dev \
        libpng-dev \
        jpeg-dev \
        freetype-dev \
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

# `zzz` to make sure it's end of files.
COPY ./php.ini /usr/local/etc/php/conf.d/zzz.ini
COPY ./fpm.conf /usr/local/etc/php-fpm.d/zzz.conf

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER 1
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer global require "hirak/prestissimo:^0.3" --prefer-dist --no-progress --no-suggest --classmap-authoritative

COPY ./composer.json /var/etc/composer.json
RUN cd /var/etc && composer install

WORKDIR /home/www-data

COPY ./entrypoint.sh /usr/local/bin/
COPY ./deploy.php /var/etc

ENTRYPOINT ["entrypoint.sh"]

HEALTHCHECK --interval=15s --retries=30 \
    CMD \
    SCRIPT_NAME=/pingpong \
    SCRIPT_FILENAME=/pingpong \
    REQUEST_METHOD=GET \
    cgi-fcgi -bind -connect 127.0.0.1:9000 || exit 1

CMD ["php-fpm"]
