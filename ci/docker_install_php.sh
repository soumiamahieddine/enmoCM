#!/bin/bash

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && exit 0

set -xe

apt-get install --no-install-recommends -y libpq-dev libxml2-dev libxslt1-dev libpng-dev \
&& docker-php-ext-install pdo pgsql pdo_pgsql \
&& docker-php-ext-install xsl \
&& pecl install xdebug-2.7.0RC2 \
&& docker-php-ext-enable xdebug \
&& docker-php-ext-install gd


#&& docker-php-ext-install pdo_pgsql pgsql xsl zip \
