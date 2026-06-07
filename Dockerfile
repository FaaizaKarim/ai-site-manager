FROM dunglas/frankenphp:latest

RUN install-php-extensions pdo_mysql mysqli curl mbstring openssl

WORKDIR /app
COPY . .