#!/bin/sh
set -e

if [[ -z "${FPM_UPSTREAM_URL}" ]]; then
    sed -i -- 's/php:9000/'${FPM_UPSTREAM_URL}'/g' /etc/nginx/conf.d/default.conf
fi

if [[ -z "${SOCKET_IO_URL}" ]]; then
    sed -i -- 's/node:80/'${SOCKET_IO_URL}'/g' /etc/nginx/conf.d/default.conf
fi

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
    set -- nginx "$@"
fi

exec "$@"
