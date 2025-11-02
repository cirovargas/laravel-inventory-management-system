FROM php:8.4-fpm-alpine

WORKDIR /app

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions  \
    && sync  \
    && apk update  \
    && apk add --no-cache \
      zip \
      wget \
      curl \
      unzip \
      git \
      bash \
    && install-php-extensions \
      intl \
      pdo_pgsql \
      pcntl \
      zip \
      pcov \
      xdebug \
      sockets \
      @composer \
    && curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony \
    && rm -rf /root/.symfony5 \
    && chmod +x /usr/local/bin/symfony \
    && rm -rf /var/cache/apk/*

ENV COMPOSER_ALLOW_SUPERUSER=1
#
#COPY . .
#RUN  composer install && \
#  composer dump-env prod && \
#  php bin/console c:w && \
#  mkdir var/storage && \
#  chmod -R 777 var/*
#
#CMD php bin/console d:m:m --no-interaction && php-fpm -D && /usr/bin/supervisord -n -c /etc/supervisord.conf
#CMD php-fpm -D && /usr/bin/supervisord -n -c /etc/supervisord.conf
