#!/bin/bash
set -e

php -S api.tenfour.local:80 -t public public/index.php &
composer install --no-interaction
cp .env.docker.run .env
./artisan migrate

exec "$@"
