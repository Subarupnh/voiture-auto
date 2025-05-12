# Utiliser l'image officielle PHP avec Apache comme base
FROM php:8.4-apache

# Installer les dépendances nécessaires et pdo_mysql
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev \ 
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql gd \
    && apt-get clean

# Activer le module apache rewrite
RUN a2enmod rewrite

# Copier le fichier SQL dans le conteneur
COPY ./database/schema.sql /docker-entrypoint-initdb.d/schema.sql

