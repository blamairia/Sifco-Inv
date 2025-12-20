# Stage 1: Final PHP + Nginx image
FROM php:8.2-fpm

# Arguments for user/group IDs
ARG USER=www-data
ARG GROUP=www-data

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    gnupg2 \
    lsb-release \
    libicu-dev \
    libzip-dev

# Install Microsoft ODBC Driver for SQL Server (needed for sqlsrv)
RUN curl https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor > /usr/share/keyrings/microsoft-archive-keyring.gpg && \
    echo "deb [arch=amd64,arm64,armhf signed-by=/usr/share/keyrings/microsoft-archive-keyring.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" > /etc/apt/sources.list.d/mssql-release.list && \
    apt-get update && \
    ACCEPT_EULA=Y apt-get install -y msodbcsql18 mssql-tools18 unixodbc-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip && \
    pecl install sqlsrv pdo_sqlsrv && \
    docker-php-ext-enable sqlsrv pdo_sqlsrv

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application code
COPY . .

# Install Composer dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Copy pre-built assets (make sure to run npm run build locally first)
COPY public/build ./public/build

# Copy Nginx config
COPY nginx.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Copy PHP config
COPY php.ini /usr/local/etc/php/conf.d/custom-php.ini

# Set permissions
RUN chown -R ${USER}:${GROUP} /var/www/html/storage /var/www/html/bootstrap/cache

# Create a docker-entrypoint script
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Laravel bootstrap\n\
php artisan cache:clear || true\n\
php artisan config:cache || true\n\
php artisan route:cache || true\n\
php artisan view:cache || true\n\
php artisan storage:link || true\n\
\n\
# Start PHP-FPM in the background\n\
php-fpm -D\n\
\n\
# Start Nginx in the foreground\n\
echo "Starting Nginx..."\n\
nginx -g "daemon off;"\n' > /usr/local/bin/docker-entrypoint.sh && \
    chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose port 8080 (matching current Azure setup)
EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
