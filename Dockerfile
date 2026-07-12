# ════════════════════════════════════════════════
# FOKOS EVENTOS — Railway (PHP 8.2 built-in server)
# ════════════════════════════════════════════════
FROM php:8.2-cli

RUN docker-php-ext-install pdo pdo_mysql

COPY . /app
WORKDIR /app

RUN mkdir -p public/uploads && chmod 777 public/uploads

EXPOSE ${PORT:-8080}

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} router.php"]
