# ---------- Stage: runtime with Apache + PHP ----------
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system deps & PHP extensions commonly needed (adjust to your needs)
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libpng-dev \
 && docker-php-ext-install zip intl pdo pdo_mysql mbstring opcache \
 && docker-php-ext-configure gd --with-jpeg \
 && docker-php-ext-install gd \
 && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite module for pretty URLs
RUN a2enmod rewrite

# Install Composer (copying official installer)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy only composer files first for dependency caching
COPY composer.json composer.lock* /var/www/html/

# Install PHP dependencies
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

# Copy application code
COPY . /var/www/html

# Ensure var/cache and var/logs (or storage) have proper permissions if used
# Adjust folder names to match your app (e.g., var, storage)
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html

# Expose standard HTTP port
EXPOSE 80

# Default command (runs Apache in foreground)
CMD ["apache2-foreground"]
