FROM php:7.4-apache
RUN a2enmod rewrite
ADD . /var/www
ADD ./public /var/www/html

WORKDIR "/var/www"

# composer
RUN apt-get update && \
    apt-get install -y --no-install-recommends git zip curl

RUN apt-get install -y \
    dnsutils \
    libfreetype6-dev \
    libmcrypt-dev \
    zlib1g-dev \
    libcurl4 \
    libcurl4-openssl-dev \
    libxml2-dev \
    libzip-dev \
    libonig-dev

# npm
RUN curl -sL https://deb.nodesource.com/setup_10.x | bash -
RUN apt-get install -y nodejs
RUN npm install

RUN docker-php-ext-install zip
RUN docker-php-ext-install curl

RUN curl --silent --show-error https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer
RUN composer install