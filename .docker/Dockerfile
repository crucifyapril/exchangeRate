FROM registry.gitlab.com/img-docker/php:8.3.15-fpm-01016ecf

COPY ./config/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

USER www

COPY --chown=www:www . /var/www
