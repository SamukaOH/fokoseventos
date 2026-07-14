#!/bin/sh
# Railway: porta dinâmica + inicialização
echo "=== FOKOS START v7.3.9 ==="
sed -i "s/NGINX_PORT/${PORT:-8080}/g" /etc/nginx/http.d/default.conf
php-fpm -D
exec nginx -g "daemon off;"
