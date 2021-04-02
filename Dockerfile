FROM php:7.4-fpm

ARG USER_ID
ARG GROUP_ID

RUN curl -sL https://deb.nodesource.com/setup_12.x  | bash -
RUN apt-get update && apt-get install -y libzip-dev nodejs git
RUN docker-php-ext-install zip  pdo_mysql mysqli sockets
RUN docker-php-ext-enable  mysqli
RUN usermod -u ${USER_ID} www-data && groupmod -g ${GROUP_ID} www-data

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN npm install -g yarn

WORKDIR /var/www/app

RUN chown www-data:www-data /var/www

USER www-data:www-data

CMD ["php-fpm"]