#!/bin/sh
set -e

if [ "${HOST_MAPs}" != "" ]; then
    # HOST_MAPs=abc.com 8888; def.com 9999;
    sed -i -- 's/#HOST_MAPs/'${HOST_MAPs}'/g' /etc/nginx/nginx.conf
fi

if [ "${NO_PROXY}" == "1" ]; then
    sed -i -- 's/http_x_forwarded_for/remote_addr/g' /etc/nginx/vhost.d/default.conf
    rm -rf /etc/nginx/http.d/cloudflare.conf
fi

if [ "${SERVER_NAME}" != "" ]; then
    sed -i -- 's/SERVER_NAME/'${SERVER_NAME}'/g' /etc/nginx/vhost.d/default.conf
    sed -i -- 's/#return 444;/return 444;/g' /etc/nginx/vhost.d/default.conf
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
