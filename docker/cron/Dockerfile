FROM php:8.1-apache

RUN a2enmod ssl && a2enmod socache_shmcb

RUN apt-get update && \
    apt-get install -y curl git unzip

RUN apt-get update && apt-get install -y cron
RUN apt-get install -y systemd
RUN systemctl enable cron

COPY docker/app/apache.conf /etc/apache2/sites-available/000-default.conf

# Set the working directory to the root of the Symfony application
WORKDIR /var/www/html/

COPY . /var/www/html

# Set the user ID and group ID of the www-data user to match the host system user ID and group ID
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data && chown -R www-data:www-data /var/www/html


USER root

# Add cron job
COPY /docker/cron/crontab /etc/cron.d/symfonycrontab
RUN chmod 0644 /etc/cron.d/symfonycrontab
RUN crontab /etc/cron.d/symfonycrontab
RUN touch /var/www/html/var/log/cron.log

CMD ["cron", "-f"]