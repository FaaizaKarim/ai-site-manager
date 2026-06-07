#!/bin/sh
set -e

# Railway often injects SERVER_NAME=0.0.0.0:$PORT — the $PORT is NOT expanded
# and Caddy fails with: Invalid address: 0.0.0.0:$PORT
unset SERVER_NAME

PORT="${PORT:-8080}"

cat > /etc/caddy/Caddyfile <<EOF
{
	admin off
	persist_config off
	auto_https off

	frankenphp
	order php_server before file_server
}

:${PORT} {
	root * /app
	encode gzip

	php_server
	file_server
}
EOF

exec frankenphp run --config /etc/caddy/Caddyfile
