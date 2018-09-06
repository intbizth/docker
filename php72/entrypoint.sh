#!/bin/sh
set -e

if [[ -z "${PHP_INIs}" ]]; then
  echo ${PHP_INIs} > /usr/local/etc/php/zzzz.ini
fi

if [[ -z "${FPM_CONFs}" ]]; then
  echo ${FPM_CONFs} > /usr/local/etc/php-fpm.d/zzzz.conf
fi

## Github connect
# git config --global url."https://${GITHUB_ACCESS_TOKEN}:@github.com/".insteadOf "https://github.com/"
if [[ -z "${GITHUB_REPOSITORY}" ]]; then
  echo 'WARN: There are no `GITHUB_REPOSITORY` env defined.';
else
  rm -rf /home/www-data/*
  rm -rf /home/www-data/.*
  echo "git clone ${GITHUB_REPOSITORY} ."
  exec "git clone ${GITHUB_REPOSITORY} ."
fi

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
    set -- php-fpm "$@"
fi

exec "$@"