FROM php:7.2-fpm
RUN apt-get update && apt-get install -y \
&& docker-php-ext-install pdo pdo_mysql \
&& apt-get install -y git \
&& apt-get install -y zip unzip \
&& curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
&& composer --version

RUN apt-get install -y gnupg build-essential libssl-dev zlib1g-dev libpng-dev libjpeg-dev libfreetype6-dev
RUN docker-php-ext-install gd

WORKDIR /var/www/russia_bank_docker



