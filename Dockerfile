# ════════════════════════════════════════════════
# FOKOS EVENTOS — Railway (PHP 8.2 + Apache)
# ════════════════════════════════════════════════
FROM php:8.2-apache

# Extensões: PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Apache: rewrite + garantir apenas 1 MPM
RUN a2dismod mpm_event 2>/dev/null; \
    a2dismod mpm_worker 2>/dev/null; \
    a2enmod mpm_prefork rewrite

# Permitir .htaccess em todo lugar
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# App
COPY . /var/www/html/
RUN mkdir -p /var/www/html/public/uploads \
 && chown -R www-data:www-data /var/www/html/public/uploads

# Railway injeta $PORT em runtime
CMD ["sh", "-c", "sed -i \"s/Listen 80/Listen ${PORT:-80}/\" /etc/apache2/ports.conf && sed -i \"s/:80>/:${PORT:-80}>/\" /etc/apache2/sites-available/000-default.conf && apache2-foreground"]
