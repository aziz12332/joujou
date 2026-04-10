FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql

RUN sed -i 's/^ServerName.*//' /etc/apache2/apache2.conf || true

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]