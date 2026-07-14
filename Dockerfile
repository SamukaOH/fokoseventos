FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql

# Habilitar rewrite e headers para o roteamento e MIME
RUN a2enmod rewrite headers

# Permitir .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

COPY . /var/www/html/
WORKDIR /var/www/html
RUN mkdir -p public/uploads && chmod -R 777 public/uploads /tmp

COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

CMD ["start.sh"]
