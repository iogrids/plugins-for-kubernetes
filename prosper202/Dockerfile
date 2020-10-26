FROM php:7.3-apache

LABEL vendor="Prosper202"

LABEL maintainer="Jeril <johcp.com>"

# Install PHP extensions
RUN apt-get update && apt-get install --no-install-recommends -y \    
    build-essential  \
    software-properties-common \
    git \
    htop \
    wget \
    dos2unix \
    curl \
    libcurl4-gnutls-dev \
    sudo \
    libc-client-dev \
    libkrb5-dev \
    libssl-dev \
    libxml2-dev \
    libzip-dev \
    libjpeg-dev \
    libmagickwand-dev \
    libgif-dev \
    libtiff-dev \
    libz-dev \
    imagemagick \
    graphicsmagick \
    libwebp-dev \
    libxpm-dev \
    libmemcached-dev \
    libaprutil1-dev \
    libzip-dev \
    zlibc \
    zlib1g \
    unzip \
    mariadb-client \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype-dir=/usr/lib --with-png-dir=/usr/lib --with-jpeg-dir=/usr/lib \
    && docker-php-ext-install gd \
    && docker-php-ext-configure opcache --enable-opcache \
    && docker-php-ext-install intl mbstring mysqli curl pdo_mysql zip opcache bcmath gd \
    && docker-php-ext-enable intl mbstring mysqli curl pdo_mysql zip opcache bcmath gd \
    && docker-php-ext-configure zip --with-libzip \
    && docker-php-ext-install zip 
    
# Mcrypt Installation
RUN apt-get update && apt-get install -y libmcrypt-dev \
    && pecl install mcrypt-1.0.2 \
    && docker-php-ext-enable mcrypt

# Install Memcached for php 7
RUN mkdir -p /usr/src/php/ext/memcached
WORKDIR /usr/src/php/ext/memcached
RUN wget https://github.com/php-memcached-dev/php-memcached/archive/v3.1.3.zip; unzip /usr/src/php/ext/memcached/v3.1.3.zip
RUN mv /usr/src/php/ext/memcached/php-memcached-3.1.3/* /usr/src/php/ext/memcached/
RUN docker-php-ext-configure memcached && docker-php-ext-install memcached 

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Define Prosper202 volume to persist data
VOLUME /var/www/html

# Define Prosper202 version and expected SHA1 signature
ENV PROSPER202_VERSION 1.9.55

# Setting an Default database user for Mysql
ENV PROSPER202_DB_USER root

# Setting an Default database name for Mysql
ENV PROSPER202_DB_NAME prosper202

# Setting an Default database user for Mysql
ENV PROSPER202_DB_HOST=galera:3306 \
    PROSPER202_DB_USER=root \
    PROSPER202_DB_PASSWORD=changeit \
    PROSPER202_DB_NAME=prosper202

# Setting PHP properties
ENV PHP_INI_DATE_TIMEZONE='UTC' \
    PHP_MEMORY_LIMIT=512M \
    PHP_MAX_UPLOAD=512M \
    PHP_MAX_EXECUTION_TIME=300

# Download package and extract to web volume
RUN curl -o prosper202.zip -SL https://github.com/jerilcj1/prosper202/releases/download/${PROSPER202_VERSION}/prosper202.zip \
    && mkdir /usr/src/prosper202 \
    && unzip prosper202.zip -d /usr/src/prosper202 \
    && rm prosper202.zip \
    && chown -R www-data:www-data /usr/src/prosper202

# Copy init scripts and custom .htaccess
COPY docker-entrypoint.sh /entrypoint.sh
COPY makeconfig.php /makeconfig.php
COPY makedb.php /makedb.php

# Enable Apache Rewrite Module
RUN a2enmod rewrite

# Apply necessary permissions
RUN ["chmod", "+x", "/entrypoint.sh"]
ENTRYPOINT ["/entrypoint.sh"]

CMD ["apache2-foreground"]
