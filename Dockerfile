# Stage 1: Build Node.js assets
FROM node:20 AS node_build
WORKDIR /app
# Copy package files and install dependencies
COPY package.json package-lock.json ./
RUN npm install
# Copy the rest of the application to run Vite build (Tailwind needs to scan blade files)
COPY . .
RUN npm run build

# Stage 2: Install PHP dependencies via Composer
FROM composer:2 AS composer_build
WORKDIR /app
# Copy composer files
COPY composer.json composer.lock ./
# Copy the rest of the application (Composer may need artisan for post-install scripts)
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs

# Stage 3: Final Image
FROM php:8.2-apache

# Install system dependencies and PHP extensions required by Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libpq-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Update Apache document root to point to Laravel's public directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Set working directory
WORKDIR /var/www/html

# Copy all application files (excluding those in .dockerignore)
COPY . .

# Copy vendor and frontend build artifacts from previous stages
COPY --from=composer_build /app/vendor /var/www/html/vendor
COPY --from=node_build /app/public/build /var/www/html/public/build

# Set correct permissions for Laravel storage and bootstrap/cache directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 80
EXPOSE 80

# The default command 'apache2-foreground' is already set in the base image
