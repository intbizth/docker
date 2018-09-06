#!/bin/sh
set -e

if [ "${PHP_INIs}" != "" ]; then
  echo ${PHP_INIs} > /usr/local/etc/php/conf.d/zzzz.ini
  cat /usr/local/etc/php/conf.d/zzzz.ini
fi

if [ "${FPM_CONFs}" != "" ]; then
  echo ${FPM_CONFs} > /usr/local/etc/php-fpm.d/zzzz.conf
  cat /usr/local/etc/php-fpm.d/zzzz.conf
fi

## Github connect
# git config --global url."https://${GITHUB_ACCESS_TOKEN}:@github.com/".insteadOf "https://github.com/"
if [ "${PHP_REPOSITORY}" == "" ]; then
  echo 'WARN: There are no `PHP_REPOSITORY` env defined.';
  echo '<?php phpinfo();' > /home/www-data/www/public/index.php
else
  rm -rf /home/www-data/www
  git --version
  git clone ${PHP_REPOSITORY} ./www
  cd ./www

  # install
  composer install --no-ansi --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader

  if [ "${PHP_INSTALLER_CMD}" != "" ]; then
    echo "Runing .. ${PHP_INSTALLER_CMD}" && ${PHP_INSTALLER_CMD}
  fi

  cd /home/www-data
  chown -R www-data:www-data ./www
fi

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
    set -- php-fpm "$@"
fi

echo "$@"
exec "$@"
