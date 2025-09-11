FROM php:8.3-fpm

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y git unzip nodejs \
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

ENV COMPOSER_HOME=/tmp/composer
RUN composer global require laravel/installer
ENV PATH="$PATH:/tmp/composer/vendor/bin"

WORKDIR /var/www

CMD ["php-fpm"]
