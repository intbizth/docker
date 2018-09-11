FROM nginx:1.15.3-alpine

COPY ./bots.d /etc/nginx/bots.d/
COPY ./conf.d /etc/nginx/conf.d/
COPY ./http.d /etc/nginx/http.d/
COPY ./server.d /etc/nginx/server.d/
COPY ./vhost.d /etc/nginx/vhost.d/
COPY ./nginx.conf /etc/nginx/nginx.conf

COPY ./entrypoint.sh /usr/local/bin/

# http://blog.tobiasforkel.de/en/2016/09/10/nginx-docker-container-and-getpwnamwww-data-problem/
RUN addgroup -g 1000 -S www-data && adduser -u 1000 -D -S -G www-data www-data

WORKDIR /home/www-data

ENTRYPOINT ["entrypoint.sh"]

CMD ["nginx", "-g", "daemon off;"]
