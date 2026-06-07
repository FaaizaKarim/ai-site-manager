#!/bin/sh
set -e

# Railway may inject SERVER_NAME=0.0.0.0:$PORT (literal $PORT) — remove it
unset SERVER_NAME

PORT="${PORT:-8080}"
export SERVER_NAME=":${PORT}"
export SERVER_ROOT="/app"

echo "Starting FrankenPHP — PORT=${PORT} SERVER_NAME=${SERVER_NAME}"

mkdir -p /app/assets/uploads
chmod 777 /app/assets/uploads

cat > /etc/caddy/Caddyfile <<EOF
{
	admin off
	persist_config off
	auto_https off

	# Railway routes traffic over IPv4 and IPv6 — bind both
	default_bind [::] 0.0.0.0

	frankenphp
	order php_server before file_server
}

{\$SERVER_NAME::8080} {
	root * {\$SERVER_ROOT:/app}

	handle /health {
		respond "OK" 200
	}

	encode gzip
	php_server
	file_server
}
EOF

exec frankenphp run --config /etc/caddy/Caddyfile
