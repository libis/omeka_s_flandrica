FROM php:8.4-apache

# Enable Apache modules
RUN a2enmod rewrite

# Set environment variables
ENV DEBIAN_FRONTEND=noninteractive

# Combine apt-get operations to reduce layers and improve caching
RUN apt-get update && apt-get upgrade -y && apt-get install -y --no-install-recommends \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libpng-dev \
    libjpeg-dev \
    libmemcached-dev \
    zlib1g-dev \
    imagemagick \
    libmagickwand-dev \
    wget \
    ghostscript \
    cron \
    ffmpeg \
    rsync \
    dos2unix \
    net-tools \
    rsyslog \
    mailutils \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) iconv pdo pdo_mysql mysqli

# Configure and install GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Set working directory
WORKDIR /var/www/html/

# Update www-data user ID
RUN usermod -u 10000 www-data

RUN wget --no-verbose "https://github.com/omeka/omeka-s/releases/download/v4.2.0/omeka-s-4.2.0.zip" -O /var/www/omeka-s.zip
RUN unzip -q /var/www/omeka-s.zip -d /var/www/ \
&&  rm /var/www/omeka-s.zip \
&&  rm -rf /var/www/html/flandrica \
&&  mv /var/www/omeka-s /var/www/html/flandrica/ \
&&  chown -R www-data:www-data /var/www/html/

# Content
COPY .htaccess /var/www/html/flandrica
COPY robots.txt /var/www/html/flandrica
COPY themes /var/www/html/flandrica/themes
COPY modules /var/www/html/flandrica/modules

# Cron
#COPY import-cron /etc/cron.d/import-cron
#RUN chmod 0744 /etc/cron.d/import-cron

#RUN touch /var/log/cron.log

# PHP settings
COPY extra.ini /usr/local/etc/php/conf.d/

# ImageMagick settings - flexible approach for different versions
RUN find /etc -name policy.xml -exec sed -i 's/^.*policy.*coder.*none.*PDF.*//' {} \;

# Mail configuration
RUN apt-get update && apt-get install -y --no-install-recommends \
    exim4 \
    exim4-daemon-light \
    && rm -rf /var/lib/apt/lists/*
COPY update-exim4.conf.conf /etc/exim4/update-exim4.conf.conf
RUN chmod -R 775 /etc/exim4/ \
    && update-exim4.conf

CMD ["apache2-foreground"]

