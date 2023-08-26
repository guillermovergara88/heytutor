# Build Stage
FROM composer:2.1 AS build

WORKDIR /app

# Copy only the composer files first and install dependencies without dev dependencies
COPY composer.json composer.lock /app/
RUN composer self-update
RUN composer install --no-dev --no-scripts --no-autoloader

# Copy the rest of the application code
COPY . /app

# Generate optimized autoload files
RUN composer dump-autoload --optimize --no-dev

# Production Stage
FROM php:8.1-fpm-alpine AS production

# Install necessary packages and extensions
RUN apk --no-cache add \
    libzip \
    libpng \
    libjpeg-turbo \
    libwebp \
    zlib \
    libxml2 \
    freetype-dev # Add freetype-dev package here

# Install GD extension with freetype support
RUN apk --no-cache add --virtual .build-deps \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    zlib-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql \
    && apk del .build-deps

# Copy application artifacts from the build stage
COPY --from=build /app /var/www

# Create a user and group for the application
RUN addgroup -g 1000 www && adduser -u 1000 -S -D -G www www

# Set permissions and ownership
RUN chown -R www:www /var/www

# Expose port 9000 and start php-fpm server
EXPOSE 9000

# Switch to the non-root user
USER www

CMD ["php-fpm"]
