# Use official PHP + Apache image
FROM php:8.1-apache

# Copy your project files into the server
COPY . /var/www/html/

# Expose port 80 for web access
EXPOSE 80
