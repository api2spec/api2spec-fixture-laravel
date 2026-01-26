FROM php:8.4-cli

WORKDIR /app

RUN apt-get update && apt-get install -y git unzip zip
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files first for caching
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader

# Copy source
COPY . .
RUN composer dump-autoload --optimize

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
