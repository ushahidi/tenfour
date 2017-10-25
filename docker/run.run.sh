#!/bin/bash
set -e

function wait_for_mysql {
  until nc -z ${DB_HOST} 3306; do
    >&2 echo "Mysql is unavailable - sleeping"
    sleep 1
  done
}

function test_redis {
  redis-cli -h "${REDIS_HOST}" PING
}

count=0
# Chain tests together by using &&
until ( test_redis )
do
  ((count++))
  if [ ${count} -gt 50 ]
  then
    echo "Services didn't become ready in time"
    exit 1
  fi
  sleep 0.1
done

php -S api.rollcall.dev:80 -t public public/index.php &
wait_for_mysql
composer install --no-interaction
cp .env.docker.run .env
./artisan migrate

exec $*
