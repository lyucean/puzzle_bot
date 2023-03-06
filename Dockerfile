FROM php:7.4-cli-alpine
RUN mkdir -p /var/log/app && chown www-data:www-data /var/log/app
WORKDIR /app
COPY . .
RUN chown -R www-data:www-data /app
USER www-data
