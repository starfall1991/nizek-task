ARG PHP_VERSION=8.4

ARG COMPOSER_VERSION=latest

FROM composer:${COMPOSER_VERSION} AS vendor
WORKDIR /var/www/html
COPY composer* ./
RUN composer install \
  --no-interaction \
  --prefer-dist \
  --ignore-platform-reqs \
  --optimize-autoloader \
  --apcu-autoloader \
  --ansi \
  --no-scripts \
  --no-dev \
  --audit

FROM php:${PHP_VERSION}-cli AS php

ENV DEBIAN_FRONTEND=noninteractive \
    TERM=xterm-color \
    CONTAINER_MODE=${CONTAINER_MODE} \
    APP_WITH_HORIZON=${APP_WITH_HORIZON} \
    APP_WITH_SCHEDULER=${APP_WITH_SCHEDULER}

ENV ROOT=/var/www/html

ARG WWWUSER=1000
ARG WWWGROUP=1000
ARG TZ=UTC

WORKDIR $ROOT

SHELL ["/bin/bash", "-eou", "pipefail", "-c"]

RUN apt-get update; \
    apt-get upgrade -yqq; \
    apt-get install -yqq --no-install-recommends --show-progress \
          supervisor \
          gnumeric; \
    rm -rf /var/lib/apt/lists/*

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime \
    && echo $TZ > /etc/timezone

RUN curl -sSLf \
        -o /usr/local/bin/install-php-extensions \
        https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions

RUN install-php-extensions gd  \
    bcmath  \
    zip  \
    intl  \
    igbinary  \
    pcntl \
    opcache \
    openswoole

RUN set -xe; php -v | head -n 1 | grep -q "PHP ${PHP_VERSION}."

RUN groupadd --force -g $WWWGROUP octane \
    && useradd -ms /bin/bash --no-log-init --no-user-group -g $WWWGROUP -u $WWWUSER octane

RUN apt-get clean && \
    apt-get -y autoremove && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \
    rm /var/log/lastlog /var/log/faillog

COPY .. .
COPY --from=vendor ${ROOT}/vendor vendor
COPY --from=vendor ${ROOT}/composer.lock composer.lock

WORKDIR /var/www/html

RUN rm -rf bootstrap/cache
RUN mkdir -p bootstrap/cache

RUN chown -R octane:octane bootstrap/cache

RUN chmod -R ug+rwx bootstrap/cache

COPY ./.docker/php.ini /usr/local/etc/php/conf.d/octane.ini
COPY ./.docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY ./.docker/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY ./.docker/supervisord.conf /etc/supervisor/conf.d/

RUN chmod 777 /usr/local/bin/entrypoint.sh
RUN cat ./.docker/utilities.sh >> ~/.bashrc

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]

HEALTHCHECK --start-period=5s --interval=2s --timeout=5s --retries=8 CMD php artisan octane:status || exit 1
