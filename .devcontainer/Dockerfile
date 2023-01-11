# [Choice] PHP version (use -bullseye variants on local arm64/Apple Silicon): 8-apache-bullseye, 8.1-apache-bullseye, 8.0-apache-bullseye, 7-apache-bullseye, 7.4-apache-bullseye, 8-apache-buster, 8.1-apache-buster, 8.0-apache-buster, 7-apache-buster, 7.4-apache-buster
ARG VARIANT=8.1-apache-bullseye
FROM php:${VARIANT}

ENV DEBIAN_FRONTEND=noninteractive 
ENV IPE_GD_WITHOUTAVIF=1

RUN apt-get update && \
apt-get -y install --no-install-recommends zlib1g-dev libpng-dev

# Install gd
RUN docker-php-ext-install gd

# Install composer
RUN curl -sSL https://getcomposer.org/installer | php \
    && chmod +x composer.phar \
    && mv composer.phar /usr/local/bin/composer