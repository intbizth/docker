FROM nginx:1.15.3-alpine

COPY ./conf.d /etc/nginx/conf.d/
COPY ./entrypoint.sh /usr/local/bin/

WORKDIR /home/www-data

ENTRYPOINT ["entrypoint.sh"]

CMD ["nginx", "-g", "daemon off;"]
