#!/bin/sh
set -e

# Railway may inject SERVER_NAME=0.0.0.0:$PORT (literal $PORT) — remove it
unset SERVER_NAME

PORT="${PORT:-8080}"

echo "Starting FrankenPHP on 0.0.0.0:${PORT}"

cat > /etc/caddy/Caddyfile <<EOF
{
	admin off
	persist_config off
	auto_https off

	frankenphp
	order php_server before file_server
}

http://0.0.0.0:${PORT} {
	root * /app

	handle /health {
		respond "OK" 200
	}

	encode gzip
	php_server
	file_server
}
EOF

exec frankenphp run --config /etc/caddy/Caddyfile
