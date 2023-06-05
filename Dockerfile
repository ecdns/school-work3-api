# Dockerfile for CDM
# Requires Docker 20.10 or higher

# Use the latest stable version of PHP with Apache
FROM php:8.2-apache

# Allow composer to be run as the root user
ENV COMPOSER_ALLOW_SUPERUSER=1

# Expose the default port for Apache
EXPOSE 8080

# Copy the application source code to the container
COPY . /var/www/back

# Set the working directory to the application directory
WORKDIR /var/www/back

# give permission to the log folder to write logs
RUN chmod -R 777 /var/www/back/log

# Install dependencies required for composer
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    libzip-dev \
    mariadb-client \
    git \
    gnupg \
    unzip \
    zip

# Install the PDO MySQL extension for PHP
RUN docker-php-ext-install pdo_mysql


# Install Composer and project dependencies
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    php -r "unlink('composer-setup.php');" && \
    composer install --no-dev --no-interaction --no-progress --prefer-dist

# Copy the PHP configuration file to the container
COPY docker/php.ini /usr/local/etc/php/conf.d/

# Copy the Apache virtual host configuration file to the container
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# Enable necessary Apache modules and virtual host
RUN a2enmod headers rewrite remoteip && \
    a2ensite 000-default

# Restart Apache
RUN service apache2 restart