FROM nginx:1.15.3-alpine

COPY ./bots.d /etc/nginx/bots.d/
COPY ./conf.d /etc/nginx/conf.d/
COPY ./http.d /etc/nginx/http.d/
COPY ./server.d /etc/nginx/server.d/
COPY ./vhost.d /etc/nginx/vhost.d/

COPY ./entrypoint.sh /usr/local/bin/

WORKDIR /home/www-data

ENTRYPOINT ["entrypoint.sh"]

CMD ["nginx", "-g", "daemon off;"]
