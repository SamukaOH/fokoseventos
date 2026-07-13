FROM php:8.2-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql
RUN apk add --no-cache nginx
RUN mkdir -p /run/nginx

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/fokos.ini

COPY . /var/www/html
WORKDIR /var/www/html
RUN chmod 1777 /tmp && mkdir -p public/uploads && chown -R www-data:www-data public/uploads

COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]
