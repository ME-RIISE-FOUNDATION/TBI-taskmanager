# PHP + Apache image for the TBI Task Manager.
# Serves the static front-end and the /api PHP endpoints from one container.
FROM php:8.2-apache

# mod_rewrite (.htaccess rules) and mod_headers (security headers)
RUN a2enmod rewrite headers

# Allow .htaccess overrides for the web root
RUN sed -ri 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# App code
COPY . /var/www/html/

# The data store and uploads must be writable by Apache (www-data)
RUN chown -R www-data:www-data /var/www/html/data /var/www/html/uploads 2>/dev/null || true

COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]
