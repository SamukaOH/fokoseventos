FROM php:8.2-apache

# Extensões do MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Habilitar mod_rewrite (para o roteamento) e mod_headers
RUN a2enmod rewrite headers

# Configurar o Apache para usar a porta dinâmica da Railway
# e permitir .htaccess (AllowOverride All)
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copiar o app
COPY . /var/www/html/
WORKDIR /var/www/html

# Uploads graváveis
RUN mkdir -p public/uploads && chmod -R 777 public/uploads /tmp

# Script de start (ajusta a porta da Railway)
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

CMD ["start.sh"]
