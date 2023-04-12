FROM php:8.2-alpine

ENV PATH="$PATH:/app/vendor/bin"

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY xdebug.ini "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini"

RUN apk add --update --no-cache autoconf gcc make g++ zlib-dev linux-headers; \
    wget https://github.com/FriendsOfPHP/pickle/releases/latest/download/pickle.phar -O /usr/local/bin/pickle; \
    chmod +x /usr/local/bin/pickle; \
    pickle install --no-interaction xdebug