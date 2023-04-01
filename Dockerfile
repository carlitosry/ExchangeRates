FROM php:8.1-apache

RUN a2enmod ssl && a2enmod socache_shmcb

RUN apt-get update && \
    apt-get install -y curl git unzip redis-tools cron && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer self-update --2 

RUN docker-php-ext-install pdo_mysql
RUN pecl install redis && docker-php-ext-enable redis

WORKDIR /var/www/html

COPY docker/web/apache.conf /etc/apache2/sites-available/000-default.conf

