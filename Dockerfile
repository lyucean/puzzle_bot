FROM php:7.4-fpm

# Update packages and install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    curl \
    git

# Install PHP extensions
RUN docker-php-ext-install zip pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
