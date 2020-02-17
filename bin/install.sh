pecl install redis-5.1.1 \
    && pecl install xdebug-2.8.1 \
    && docker-php-ext-enable redis xdebug

apt-get update && \

apt-get install openssl \
    zip \
    ca-certificates \
    gcc \
    musl-dev \
    make \
    curl

curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/local/bin --filename=composer
