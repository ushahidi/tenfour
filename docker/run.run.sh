#!/bin/bash
set -e

function wait_for_mysql {
  until nc -z ${DB_HOST} 3306; do
    >&2 echo "Mysql is unavailable - sleeping"
    sleep 1
  done
}

php -S api.rollcall.dev:80 -t public public/index.php &
wait_for_mysql
composer install --no-interaction
cp .env.docker.run .env
./artisan migrate

exec $*
