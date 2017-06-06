FROM ushahidi/php-ci:php-5.6.30

WORKDIR /var/www

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-autoloader --no-scripts

COPY docker/test.run.sh /test.run.sh

ENTRYPOINT [ "/bin/bash", "/test.run.sh" ]
