# Use official PHP Apache image
FROM php:7.4-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite (important for some PHP frameworks)
RUN a2enmod rewrite

# Set the working directory in the container
WORKDIR /var/www/html

# Copy project files into the container
COPY . /var/www/html/

# Expose port 80
EXPOSE 80
