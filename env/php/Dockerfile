FROM php:8.4-cli-alpine

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
      swoole \
      opcache \
      zip \
      pcov \
      xdebug \
      sockets \
      @composer \
    && rm -rf /var/cache/apk/*


ENV COMPOSER_ALLOW_SUPERUSER=1

COPY ./env/php/php.ini /usr/local/etc/php/php.ini
COPY . /app

RUN composer install --no-scripts --no-progress --no-suggest --prefer-dist --optimize-autoloader && \
    composer dump-autoload -o && \
    composer clear-cache && \
    php artisan optimize && \
    php artisan config:cache && \
    php artisan event:cache && \
    php artisan route:cache
#php artisan queue:work


CMD [  "php", "-d", "variables_order=EGPCS", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=3000", "--workers=4", "--task-workers=6"]
