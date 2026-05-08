FROM php:8.3-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip curl libzip-dev libpng-dev libxml2-dev libonig-dev \
    && docker-php-ext-install pdo_sqlite zip mbstring xml \
    && rm -rf /var/lib/apt/lists/*

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /workspace

CMD ["bash"]
