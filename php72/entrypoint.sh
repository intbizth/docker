#!/bin/sh
set -e

function releases_list()
{
  cd /home/www-data

  if [ -d releases ] && [ "$(ls -A releases)" ]; then echo 'true'; fi
}

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
if [ "${REPOSITORY}" == "" ]; then
  echo 'WARN: There are no `REPOSITORY` env defined.';
  mkdir -p /home/www-data/current/public
  echo '<?php phpinfo();' > /home/www-data/current/public/index.php
else
  /var/etc/vendor/bin/dep -vvv --file=/home/www-data/deploy.php
fi

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
    set -- php-fpm "$@"
fi

echo "$@"
exec "$@"
