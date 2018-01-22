apk add --no-cache \
    openssl \
    ca-certificates \
    php7 \
    php7-xdebug \
    php7-redis \
    php7-json \
    php7-mbstring \
    php7-phar \
    php7-openssl \
    php7-tokenizer \
    php7-dom \
    php7-xml \
    php7-xmlwriter \
    curl

curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/local/bin --filename=composer

apk del curl

sed -i 's/;//' /etc/php7/conf.d/xdebug.ini
