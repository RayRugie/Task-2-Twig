FROM php:8.2-apache

# Upgrade OS packages to pick up security fixes (fixes high vulnerability)
RUN set -eux; \
    apt-get update; \
    apt-get -y upgrade; \
    apt-get -y dist-upgrade; \
    apt-get -y autoremove; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/*

# Enable rewrite module for .htaccess
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Install Composer (copy from official image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install system deps commonly needed by Composer and PHP libs
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends git unzip; \
    rm -rf /var/lib/apt/lists/*

# Leverage Docker layer caching: install PHP deps first
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction --optimize-autoloader

# Copy the application source
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
