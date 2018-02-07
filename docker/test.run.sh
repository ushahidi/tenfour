#!/bin/bash
set -e

set

sleep 10

function sync {
  echo "Sync files from /vols/src"
  rsync -ar --exclude vendor --exclude storage/logs --delete-during /vols/src/ ./
  echo "Clean uncommited files"
  git clean -fx
}

function wait_for_mysql {
  [ -z "${DB_HOST}" ] && return 0;
  until nc -z ${DB_HOST} 3306; do
    >&2 echo "Mysql is unavailable - sleeping"
    sleep 1
  done
}

function wait_for_redis {
  [ -z "${REDIS_HOST} "] && return 0;
  until nc -z ${REDIS_HOST} 6379; do
    >&2 echo "Redis is unavailable - sleeping"
    sleep 1
  done
}

sync
php -S api.tenfour.local:80 -t public public/index.php &
wait_for_mysql
composer install --no-interaction
cp .env.testing .env
./artisan migrate

exec "$@"
