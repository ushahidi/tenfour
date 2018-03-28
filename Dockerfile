FROM ushahidi/php-fpm-nginx:php-7.2

WORKDIR /var/www

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-autoloader --no-scripts

COPY ./ /var/www/
COPY docker/run.run.sh /run.run.sh
RUN $DOCKERCES_MANAGE_UTIL add /run.run.sh

ENV VHOST_ROOT=/var/www \
    VHOST_INDEX=server.php
