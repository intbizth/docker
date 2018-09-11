#!/bin/sh
set -e

if [ "${SERVER_NAME}" != "" ]; then
    sed -i -- 's/SERVER_NAME/'${SERVER_NAME}'/g' /etc/nginx/vhost.d/default.conf
fi

if [ "${FPM_UPSTREAM_URL}" != "" ]; then
    sed -i -- 's/php:9000/'${FPM_UPSTREAM_URL}'/g' /etc/nginx/vhost.d/default.conf
fi

if [ "${SOCKET_IO_URL}" != "" ]; then
    sed -i -- 's/node:80/'${SOCKET_IO_URL}'/g' /etc/nginx/vhost.d/default.conf
fi

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
    set -- nginx "$@"
fi

nginx -t

echo "$@"
exec "$@"
