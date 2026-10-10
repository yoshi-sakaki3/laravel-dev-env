# PHP-FPM（開発用）
# 拡張機能は docker-php-extension-installer でまとめて入れる
ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-fpm

COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
# mail() を Mailpit に届けるための sendmail 代わり
COPY --from=axllent/mailpit:latest /mailpit /usr/local/bin/mailpit

# Laravel でよく使う拡張機能
RUN install-php-extensions \
      pdo_mysql mysqli gd zip intl bcmath exif opcache pcntl redis xdebug \
 && apt-get update \
 && apt-get install -y --no-install-recommends git unzip \
 && rm -rf /var/lib/apt/lists/*

COPY php.ini /usr/local/etc/php/conf.d/zz-dev.ini
COPY xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini
COPY fpm-env.conf /usr/local/etc/php-fpm.d/zz-env.conf

WORKDIR /var/www/html
