#Preparare i file di oxwall nella cartella "html"
FROM php:5.6-apache

RUN apt-get update \
	&& apt-get upgrade -y \
	&& apt-get install -y nano libpng-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev libxml2-dev \
	&& docker-php-ext-install mysql pdo pdo_mysql zip \
	&& docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
	&& docker-php-ext-install -j$(nproc) gd
ADD html /var/www/html/
RUN chown -R www-data:www-data /var/www/html/ \
	&& a2enmod rewrite
