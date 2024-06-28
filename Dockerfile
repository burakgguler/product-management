FROM php:8.1-fpm

WORKDIR /var/www

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get update && \
    apt-get install -y \
    libzip-dev \
    zip \
    git \
    unzip \
    wget \
    && docker-php-ext-install zip pdo pdo_mysql

RUN composer global config --no-plugins allow-plugins.symfony/flex true && \
    composer global require symfony/flex && \
    wget https://get.symfony.com/cli/installer -O - | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

COPY . .

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-scripts --no-interaction && \
    composer run-script auto-scripts

EXPOSE 8000

CMD ["symfony", "server:start", "--port=8000", "--no-tls", "--dir=/var/www/public"]
