#!/bin/sh
# Railway: porta dinâmica
sed -i "s/NGINX_PORT/${PORT:-8080}/g" /etc/nginx/http.d/default.conf

# PHP-FPM em background
php-fpm -D

# Nginx em foreground
exec nginx -g "daemon off;"
