FROM php:7.4-apache

RUN apt-get update \
	&& apt-get upgrade -y \
	&& apt-get install -y git nano

#RUN mkdir deep2t && cd deep2t
RUN git clone https://github.com/routetopa/deep2-components.git COMPONENTS \
    && git clone https://github.com/routetopa/deep.git DEEP \
    && git clone https://github.com/routetopa/deep2-client.git DEEPCLIENT

COPY configuration.xml DEEP/configuration.xml
RUN chown -R www-data:www-data /var/www/html/ && a2enmod rewrite