#!/bin/bash
set -e

function sync {
  echo "Sync files from /vols/src"
  rsync -ar --delete-during /vols/src/ ./
  echo "Clean uncommited files"
  git clean -fx
}

function wait_for_mysql {
  until nc -z mysql 3306; do
    >&2 echo "Mysql is unavailable - sleeping"
    sleep 1
  done
}

sync
php -S api.rollcall.dev:80 -t public public/index.php &
wait_for_mysql
composer install --no-interaction
cp .env.testing .env
./artisan migrate

exec $*
