FROM php:8.0-apache
RUN docker-php-ext-install mysqli
RUN pecl install xdebug && docker-php-ext-enable xdebug
COPY ./php.ini /usr/local/etc/php/

