FROM composer
FROM php:7.2-fpm-alpine3.8

RUN apk add --no-cache \
        git

ENV VERSION=v10.10.0 NPM_VERSION=6 YARN_VERSION=latest

# For base builds
# ENV CONFIG_FLAGS="--fully-static --without-npm" DEL_PKGS="libstdc++" RM_DIRS=/usr/include

RUN apk add --no-cache curl make gcc g++ python linux-headers binutils-gold gnupg libstdc++ && \
  for server in ipv4.pool.sks-keyservers.net keyserver.pgp.com ha.pool.sks-keyservers.net; do \
    gpg --keyserver $server --recv-keys \
      94AE36675C464D64BAFA68DD7434390BDBE9B9C5 \
      B9AE9905FFD7803F25714661B63B535A4C206CA9 \
      77984A986EBC2AA786BC0F66B01FBB92821C587A \
      71DCFD284A79C3B38668286BC97EC7A07EDE3FC1 \
      FD3A5288F042B6850C66B31F09FE44734EB7990E \
      8FCCA13FEF1D0C2E91008E09770F7A9A5AE15600 \
      C4F0DFFF4E8C1A8236409D08E73BC641CC11F4C8 \
      DD8F2338BAE7501E3DD5AC78C273792F7D83545D && break; \
  done && \
  curl -sfSLO https://nodejs.org/dist/${VERSION}/node-${VERSION}.tar.xz && \
  curl -sfSL https://nodejs.org/dist/${VERSION}/SHASUMS256.txt.asc | gpg --batch --decrypt | \
    grep " node-${VERSION}.tar.xz\$" | sha256sum -c | grep ': OK$' && \
  tar -xf node-${VERSION}.tar.xz && \
  cd node-${VERSION} && \
  ./configure --prefix=/usr ${CONFIG_FLAGS} && \
  make -j$(getconf _NPROCESSORS_ONLN) && \
  make install && \
  cd / && \
  if [ -z "$CONFIG_FLAGS" ]; then \
    if [ -n "$NPM_VERSION" ]; then \
      npm install -g npm@${NPM_VERSION}; \
    fi; \
    find /usr/lib/node_modules/npm -name test -o -name .bin -type d | xargs rm -rf; \
    if [ -n "$YARN_VERSION" ]; then \
      for server in ipv4.pool.sks-keyservers.net keyserver.pgp.com ha.pool.sks-keyservers.net; do \
        gpg --keyserver $server --recv-keys \
          6A010C5166006599AA17F08146C2130DFD2497F5 && break; \
      done && \
      curl -sfSL -O https://yarnpkg.com/${YARN_VERSION}.tar.gz -O https://yarnpkg.com/${YARN_VERSION}.tar.gz.asc && \
      gpg --batch --verify ${YARN_VERSION}.tar.gz.asc ${YARN_VERSION}.tar.gz && \
      mkdir /usr/local/share/yarn && \
      tar -xf ${YARN_VERSION}.tar.gz -C /usr/local/share/yarn --strip 1 && \
      ln -s /usr/local/share/yarn/bin/yarn /usr/local/bin/ && \
      ln -s /usr/local/share/yarn/bin/yarnpkg /usr/local/bin/ && \
      rm ${YARN_VERSION}.tar.gz*; \
    fi; \
  fi && \
  apk del curl make gcc g++ python linux-headers binutils-gold gnupg ${DEL_PKGS} && \
  rm -rf ${RM_DIRS} /node-${VERSION}* /usr/share/man /tmp/* /var/cache/apk/* \
    /root/.npm /root/.node-gyp /root/.gnupg /usr/lib/node_modules/npm/man \
    /usr/lib/node_modules/npm/doc /usr/lib/node_modules/npm/html /usr/lib/node_modules/npm/scripts

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

ENTRYPOINT ["entrypoint.sh"]

CMD ["php-fpm"]
