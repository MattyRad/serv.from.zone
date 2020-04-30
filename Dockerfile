FROM php:7.4-apache
RUN a2enmod rewrite
ADD . /var/www
ADD ./public /var/www/html

RUN curl -sL https://deb.nodesource.com/setup_10.x | bash -
RUN apt-get install -y nodejs