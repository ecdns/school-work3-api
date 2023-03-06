# Dockerfile
FROM php:8.2-apache

ENV COMPOSER_ALLOW_SUPERUSER=1

EXPOSE 8080

WORKDIR /var/www/back

COPY . /var/www/back

# git, unzip & zip are for composer
RUN apt-get update -qq && \
    apt-get install -qy \
    mariadb-client \
    libzip-dev \
    git \
    gnupg \
    unzip \
    zip \
    && docker-php-ext-install pdo_mysql

# Install composer dependencies
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && composer install --no-dev -d /var/www/back

# PHP configuration
COPY conf/php.ini /usr/local/etc/php/conf.d/app.ini

# Apache configuration
COPY conf/vhost.conf /etc/apache2/sites-available/000-default.conf

# Enable apache modules and site
RUN a2enmod headers rewrite remoteip && \
    a2ensite 000-default

# Restart apache
RUN service apache2 restart
