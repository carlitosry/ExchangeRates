FROM php:8.1-apache

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN a2enmod ssl && a2enmod socache_shmcb

RUN apt-get update && \
    apt-get install -y curl git unzip redis-tools

RUN apt-get update && apt-get install -y cron
RUN apt-get install -y systemd

RUN curl -sS https://getcomposer.org/installer | php -- \
        --install-dir=/usr/bin --filename=composer && chmod +x /usr/bin/composer

RUN alias composer='php /usr/bin/composer'

RUN docker-php-ext-install pdo_mysql
RUN pecl install redis && docker-php-ext-enable redis

COPY docker/app/apache.conf /etc/apache2/sites-available/000-default.conf

# Set the working directory to the root of the Symfony application
WORKDIR /var/www/html/

COPY . /var/www/html

# Set the user ID and group ID of the www-data user to match the host system user ID and group ID
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data && chown -R www-data:www-data /var/www/html