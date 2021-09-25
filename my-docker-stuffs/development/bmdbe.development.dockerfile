# NOTE: This runs in development using php artisan serve command.
FROM php:7.4-fpm-alpine

RUN apk add nano vim
RUN docker-php-ext-install pdo pdo_mysql sockets
RUN curl -sS https://getcomposer.org/installer​ | php -- \
     --install-dir=/usr/local/bin --filename=composer

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .
RUN composer install


RUN php artisan passport:keys
# COPY ./my-shell-scripts/set-env.sh .
# RUN chmod 777 set-env.dev.sh