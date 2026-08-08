#!/bin/sh
set -e

# Ensure the named Docker volumes exist if the Docker socket is available in this container.
if command -v curl >/dev/null 2>&1 && [ -S /var/run/docker.sock ]; then
  project_prefix="${COMPOSE_PROJECT_NAME:-eblogger}"

  for v in sail-mysql sail-redis; do
    if [ "$(curl --unix-socket /var/run/docker.sock -s -o /dev/null -w '%{http_code}' http://localhost/volumes/$v/json)" = "200" ] || [ "$(curl --unix-socket /var/run/docker.sock -s -o /dev/null -w '%{http_code}' http://localhost/volumes/${project_prefix}_$v/json)" = "200" ]; then
      echo "Docker volume already exists for: $v"
      continue
    fi

    target_volume="${project_prefix}_$v"
    echo "Creating Docker volume: $target_volume"
    curl --unix-socket /var/run/docker.sock -sS -X POST http://localhost/volumes/create -H 'Content-Type: application/json' -d "{\"Name\":\"${target_volume}\"}" >/dev/null
  done
else
  echo "Docker socket unavailable in container. Skipping named volume creation check."
fi

# Wait for redis to be ready before running artisan commands.
until php -r '$host = getenv("REDIS_HOST") ?: "redis"; $port = getenv("REDIS_PORT") ?: "6379"; $fp = @fsockopen($host, $port, $errno, $errstr, 2); if ($fp) { fclose($fp); exit(0); } exit(1);'; do
  echo "Waiting for Redis to accept connections..."
  sleep 5
done

# Wait for MySQL to be ready before running artisan commands.
until mysqladmin ping -h "${DB_HOST:-mysql}" -P "${DB_PORT:-3306}" --silent; do
  echo "Waiting for MySQL to accept connections..."
  sleep 5
done

# Run migrations and seeders once MySQL is ready.
php artisan migrate --force
php artisan db:seed --force

# clear the telescope db entries while starting the container
php artisan telescope:prune --hours=24

# Clear the cache and config to ensure the application is using the latest settings.
php artisan config:clear
php artisan cache:clear

# Start the built-in Laravel development server so the container keeps running.
exec /usr/bin/php -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=80
