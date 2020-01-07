ARG CI_REGISTRY
ARG PHP_VERSION
FROM ${CI_REGISTRY}/auxmoney/docker-images/composer-${PHP_VERSION}:latest

ENV APP_ENV=prod

RUN composer global require hirak/prestissimo

COPY . /app
WORKDIR /app

ARG SYMFONY_VERSION

RUN sed -i "s/\^3\.4|\^4\.2|\^5\.0/${SYMFONY_VERSION}/g" composer.json \
    && composer install \
    && composer dump-autoload --optimize
