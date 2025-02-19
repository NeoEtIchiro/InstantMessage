FROM php:8.1-apache

# Modifie la configuration d'Apache pour écouter sur le port 8080
RUN sed -ri 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/*.conf

COPY . /var/www/html/

EXPOSE 8080