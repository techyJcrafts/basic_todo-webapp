# Use official PHP Apache image
FROM php:7.4-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Install required system packages
RUN apt-get update && apt-get install -y unzip zip && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html/

# Run composer install to generate the vendor/ folder and autoloader
RUN composer install --no-dev --optimize-autoloader

# Expose port 80
EXPOSE 80
