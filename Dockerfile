FROM dunglas/frankenphp:latest

RUN install-php-extensions pdo_mysql mysqli curl mbstring openssl

WORKDIR /app

COPY docker-entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

COPY . .

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
