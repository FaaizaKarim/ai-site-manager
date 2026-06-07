FROM dunglas/frankenphp:latest

RUN install-php-extensions pdo_mysql mysqli curl mbstring openssl

WORKDIR /app
COPY . .

ENV SERVER_NAME=":80"

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]