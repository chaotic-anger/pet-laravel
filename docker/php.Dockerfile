FROM php:8.3-fpm

RUN apt-get update && apt-get install -y git unzip \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sSLf -o /usr/local/bin/install-php-extensions \
    https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions

RUN install-php-extensions \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    zip \
    xdebug-^3@stable \
    @composer

WORKDIR /var/www

CMD ["php-fpm"]
