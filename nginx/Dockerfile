FROM nginx:1.15.2-alpine

COPY ./conf.d /etc/nginx/conf.d/
COPY entrypoint /usr/local/bin/

WORKDIR /home/www-data

ENTRYPOINT ["entrypoint"]
