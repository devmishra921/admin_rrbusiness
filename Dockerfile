FROM php:8.1-apache

# Install mysqli and other needed extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy project files into the container
COPY . /var/www/html/

# Enable Apache rewrite module (optional)
RUN a2enmod rewrite

