FROM php:7.4-apache

RUN apt-get update \
	&& apt-get upgrade -y \
	&& apt-get install -y git nano libpng-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev libxml2-dev libzip-dev \
	&& docker-php-ext-install pdo pdo_mysql zip \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install gd

ADD www-oauth2 /var/www-oauth2/
WORKDIR /var/www-oauth2

ENV APACHE_DOCUMENT_ROOT=/var/www-oauth2/
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN chown -R www-data:www-data /var/www-oauth2/ \
	&& a2enmod rewrite

# Composer setup. used to install PHP dependencies
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
	&& php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
	&& php composer-setup.php \
	&& php -r "unlink('composer-setup.php');" \
	&& mv composer.phar /usr/local/bin/composer

RUN composer update

RUN cp .env.example .env \
	&& php artisan key:generate


ADD db.sql /var/www-oauth2
RUN apt-get install default-mysql-client -y
# RUN mysql -h mysql -p < db.sql && rm -f db.sql

ADD config-apache /etc/apache2/sites-available/000-default.conf
ADD init.php /var/www-oauth2/init.php
ADD init.sql /var/www-oauth2/init.sql
ADD init.sh /var/www-oauth2/init.sh

