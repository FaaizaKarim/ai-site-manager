FROM dunglas/frankenphp:latest

RUN install-php-extensions pdo_mysql mysqli curl mbstring openssl

WORKDIR /app

# Caddyfile must live outside /app so COPY . . does not overwrite it
COPY Caddyfile /etc/caddy/Caddyfile
COPY . .

ENV SERVER_NAME=":8080"

# Railway sets PORT at runtime — inject it into SERVER_NAME before Caddy starts
CMD ["sh", "-c", "export SERVER_NAME=:${PORT:-8080} && exec frankenphp run --config /etc/caddy/Caddyfile"]
