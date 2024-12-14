FROM php:8.2-fpm

# Instalar dependencias necesarias
RUN apt-get update \
    && apt-get install -y build-essential \
    && apt-get install -y libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libonig-dev libxml2-dev \
    && apt-get install -y locales zip \
    && apt-get install -y nano git nginx cron \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones de PHP necesarias
RUN docker-php-ext-install pdo_mysql zip exif pcntl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl
RUN docker-php-ext-install mysqli

# Copiar archivos y configuración
COPY . /var/www
COPY ./docker/nginx/shask.conf /etc/nginx/sites-enabled/default
COPY ./docker/entrypoint.sh /etc/entrypoint.sh

# Instalar Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Agregar configuración para git
RUN git config --system --add safe.directory /var/www

# Cambiar la propiedad y permisos de la carpeta storage y bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

WORKDIR /var/www

# Establecer el punto de entrada
ENTRYPOINT ["sh", "/etc/entrypoint.sh"]