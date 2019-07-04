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

test_reporter() {
  local _ret=0;
  "$@" || _ret=$?
  if [ $_ret -ne 0 ]; then
    echo -e "\n\n* Test run failed, output of logs in storage/logs follows:"
    ls -la storage/logs/*.log
    echo -e "-------------------- BEGIN LOG OUTPUT --------------------"
    cat storage/logs/*.log
    echo -e "--------------------- END LOG OUTPUT ---------------------"
    return 1
  else
    echo -e "\n* Successful test run"
    return 0
  fi
}

sync
php -S api.tenfour.local:80 -t public public/index.php &
wait_for_mysql
composer install --no-interaction
cp .env.testing .env
./artisan migrate


case "$1" in
  test_reporter)
    shift
    test_reporter "$@"
    ;;
  *)
    exec "$@"
    ;;
esac
