# plademar base LAP (Linux Apache PHP)
FROM ubuntu:18.04

MAINTAINER Sergi<sergi.argerich@gmail.com>

ENV DEBIAN_FRONTEND noninteractive

# Install basics
RUN apt-get update && apt-get install -y --force-yes software-properties-common

RUN apt-get install -my --force-yes \
  curl \
  wget \
  git \
  subversion \
  openssl \
  unzip \
  acl

#Install PHP7
RUN apt-get install -y \
  php \
  php7.2-xml

#Install and configure Apache
RUN apt-get install -y apache2

# Manually set up the apache environment variables
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_RUN_DIR /var/run
ENV APACHE_PID_FILE /var/run/apache2.pid

# Expose apache.
EXPOSE 80
EXPOSE 8080
EXPOSE 443

# Add SSL certificate
#RUN mkdir /etc/apache2/ssl && mkdir /etc/apache2/ssl/private
#RUN chmod 755 /etc/apache2/ssl
#RUN chmod 710 /etc/apache2/ssl/private
#ADD certs/_.plademar.com_private_key.key /etc/apache2/ssl/private
#ADD certs/plademar.com_ssl_certificate.cer /etc/apache2/ssl
#ADD certs/-.plademar.com_ssl_certificate_INTERMEDIATE.cer /etc/apache2/ssl
#RUN chown -R root:root /etc/apache2/ssl
#RUN chown -R root:ssl-cert /etc/apache2/ssl/private

# Update the default apache site with the config we created.
#RUN a2dissite 000-default

ADD .docker/lap/plademar.conf /etc/apache2/sites-available/001-plademar.conf
RUN a2ensite 001-plademar
#ADD plademar-ssl.conf /etc/apache2/sites-available/002-plademar-ssl.conf
#RUN a2ensite 002-plademar-ssl

# Prepare code directory
#RUN mkdir /var/www/plademar # Do not create the app dir because the code will already be in VCS
RUN chown -R www-data:www-data /var/www
RUN chmod -R 775 /var/www

# Install composer
WORKDIR /var/www
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer
RUN ln -snf /usr/share/zoneinfo/Europe/Madrid /etc/localtime

# Install Symfony
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony

ENTRYPOINT ["/usr/sbin/apache2"]
CMD ["-D", "FOREGROUND"]
