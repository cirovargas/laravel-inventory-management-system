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
      supervisor \
      dcron \
      libcap \
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

# Copy configuration files
COPY ./env/php/php.ini /usr/local/etc/php/php.ini
COPY ./env/php/supervisord.conf /etc/supervisord.conf
COPY ./env/php/supervisor.d/ /etc/supervisor/conf.d/
COPY . /app

RUN cp .env.example .env && \
    composer install --no-scripts --no-progress --no-suggest --prefer-dist --optimize-autoloader && \
    composer dump-autoload -o && \
    composer clear-cache && \
    php artisan key:generate && \
    php artisan optimize && \
    php artisan config:cache && \
    php artisan event:cache && \
    php artisan route:cache

# Set up cron for Laravel scheduler
RUN echo "* * * * * cd /app && php artisan schedule:run >> /dev/null 2>&1" > /etc/crontabs/root \
    && mkdir -p /var/log/supervisor \
    && mkdir -p /etc/supervisor/conf.d

# Create entrypoint script
RUN echo '#!/bin/bash' > /usr/local/bin/docker-entrypoint.sh \
    && echo 'set -e' >> /usr/local/bin/docker-entrypoint.sh \
    && echo '' >> /usr/local/bin/docker-entrypoint.sh \
    && echo '# Run migrations' >> /usr/local/bin/docker-entrypoint.sh \
    && echo 'php artisan migrate --force --no-interaction' >> /usr/local/bin/docker-entrypoint.sh \
    && echo '' >> /usr/local/bin/docker-entrypoint.sh \
    && echo '# Start cron in background' >> /usr/local/bin/docker-entrypoint.sh \
    && echo 'crond -b -l 2' >> /usr/local/bin/docker-entrypoint.sh \
    && echo '' >> /usr/local/bin/docker-entrypoint.sh \
    && echo '# Start supervisord in foreground' >> /usr/local/bin/docker-entrypoint.sh \
    && echo 'exec /usr/bin/supervisord -c /etc/supervisord.conf' >> /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
