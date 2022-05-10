FROM node:16-alpine AS js-builder
WORKDIR /build
COPY ./preview /build
COPY ./preview/.env.docker /app/preview/.env
RUN npm ci && npm run build

FROM composer:2 AS php-builder
WORKDIR /build
COPY ./computed /build
COPY ./computed/config.docker.inc.php /app/computed/config.inc.php
RUN composer install

FROM alpine:latest
RUN apk add nginx php8-fpm supervisor
COPY ./conf/supervisord.conf /etc/supervisord.conf
COPY ./conf/nginx.conf /etc/nginx/http.d/default.conf
COPY --from=js-builder /build/dist /app/preview
COPY --from=php-builder /build /app/computed
WORKDIR /app
EXPOSE 8888
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
