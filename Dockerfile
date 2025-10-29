FROM php:8.2-apache

# Enable rewrite module for .htaccess
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy everything into the container
COPY . .

# Tell Apache to look in the public folder for the site
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
 && sed -i 's|<Directory /var/www/>|<Directory /var/www/html/public/>|g' /etc/apache2/apache2.conf || true
RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/app.conf \
 && a2enconf app

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
