FROM php:8.1-apache

RUN apt-get update -y
RUN apt-get install cron -y
RUN apt-get install curl -y

WORKDIR /var/www/html/

# Setup cron
COPY /docker/cron/crontab /etc/cron.d/crontab
RUN chmod 0644 /etc/cron.d/crontab
RUN crontab /etc/cron.d/crontab

COPY . /var/www/html

