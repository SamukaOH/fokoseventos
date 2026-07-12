# ════════════════════════════════════════════════
# FOKOS EVENTOS — Railway (Nginx + PHP-FPM)
# ════════════════════════════════════════════════
FROM php:8.2-fpm-alpine

# Extensões
RUN docker-php-ext-install pdo pdo_mysql

# Nginx
RUN apk add --no-cache nginx

# Config Nginx
RUN mkdir -p /run/nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# App
COPY . /var/www/html
WORKDIR /var/www/html
RUN mkdir -p public/uploads && chown -R www-data:www-data public/uploads

# Startup: Nginx + PHP-FPM
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]
