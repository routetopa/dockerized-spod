#Preparare i file di oxwall nella cartella "html7.4"
FROM php:7.4-apache

RUN apt-get update \
	&& apt-get upgrade -y \
	&& apt-get install -y git nano libpng-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev libxml2-dev libzip-dev \
	&& docker-php-ext-install pdo pdo_mysql zip \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install gd

#RUN apt-get install -y msmtp
#COPY msmtprc /etc/msmtprc
#RUN echo 'sendmail_path = "/usr/bin/msmtp -t"' > /usr/local/etc/php/conf.d/sendmail.ini

ADD html7.4 /var/www/html/
RUN cd ow_plugins \
	#&& git clone https://github.com/routetopa/spod-plugin-ode.git ode \
    && git clone https://github.com/routetopa/spod-plugin-openid.git openidconnect \
    && git clone https://github.com/routetopa/spod-plugin-myspace.git spodpr \
    && git clone https://github.com/routetopa/spod-plugin-new-agora.git agora \
    && git clone https://github.com/routetopa/spod-plugin-openwall.git openwall \
    && git clone https://github.com/routetopa/spod-plugin-tchat.git tchat \
    && git clone https://github.com/routetopa/spod-plugin-widgets.git spodwidgets \
    #&& git clone https://github.com/routetopa/spod-plugin-notification-system.git notification_system \
    && git clone https://github.com/routetopa/spod-plugin-api.git spodapi \
    && git clone https://github.com/routetopa/spod-plugin-blog.git spod_blog \
    && git clone https://github.com/routetopa/spod-plugin-agora-exporter.git agoraexporter \
    #&& git clone https://github.com/routetopa/spod-plugin-cocreation.git cocreation \
    && git clone https://github.com/routetopa/spod-plugin-showcase.git showcase \
    && git clone https://github.com/routetopa/spod-plugin-privacy.git spodprivacy \
    && git clone https://github.com/routetopa/spod-plugin-discussion.git spod_plugin_discussion \
    && git clone https://github.com/routetopa/spod-plugin-oauth2.git spodoauth2connect \
    && rm -rf slideshow \
    && git clone https://github.com/routetopa/spod-plugin-slideshow.git slideshow \
    && git clone https://github.com/vincios/Extfeed spod_extfeed \
    && git clone https://github.com/thaolt/cacheextreme cacheextreme \
    && cd ../ow_themes \
    && git clone https://github.com/routetopa/spod-theme-matter.git spod_theme_matter
RUN chown -R www-data:www-data /var/www/html/ && a2enmod rewrite