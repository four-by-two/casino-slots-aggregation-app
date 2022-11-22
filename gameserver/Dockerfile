FROM php:8.1-fpm

WORKDIR /var/www

RUN rm -rf /var/www/html \
  && apt-get update -y \
  && apt-get install -y \
  autoconf \
  build-essential \
  curl \
  git \
  libicu-dev \
  libmpdec-dev \
  libpq-dev \
  libjpeg-dev \
  libpng-dev \
  libzip-dev \
  unzip \
  vim \
  wget \
  zip \
  zlib1g-dev \
  && apt autoremove -y \
  && apt clean \
  && pecl install redis \
  && docker-php-ext-install \
  bcmath \
  gd \
  intl \
  opcache \
  pcntl \
  pdo_mysql \
  pdo_pgsql \
  pgsql \
  zip \
  && curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer

EXPOSE 9000