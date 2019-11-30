FROM php:7.2-apache
COPY . /var/www/html/ticket-system-api
#Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=. --filename=composer
RUN mv composer /usr/local/bin/

RUN a2enmod rewrite
#Install PHP Extensions
RUN docker-php-ext-install pdo_mysql

WORKDIR /var/www/html/ticket-system-api
RUN composer install