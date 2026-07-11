FROM php:8.2-apache

# Desabilita MPMs que podem causar conflito
RUN a2dismod mpm_event || true
RUN a2dismod mpm_worker || true
RUN a2enmod mpm_prefork

# Extensões PHP
RUN docker-php-ext-install pdo pdo_mysql

# Apache
RUN a2enmod rewrite
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Projeto
COPY . /var/www/html/

RUN mkdir -p /var/www/html/public/uploads \
    && chown -R www-data:www-data /var/www/html/public/uploads

CMD sh -c "sed -i 's/Listen 80/Listen ${PORT:-80}/' /etc/apache2/ports.conf && \
sed -i 's/:80>/:${PORT:-80}>/' /etc/apache2/sites-available/000-default.conf && \
apache2-foreground"