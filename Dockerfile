FROM php:8.2-fpm

RUN docker-php-ext-install mysqli
RUN apt-get update \
    && apt-get install -y build-essential \
    && apt-get install -y libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libonig-dev libxml2-dev \
    && apt-get install -y locales zip \
    && apt-get install -y nano git nginx cron \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql zip exif pcntl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl

COPY . /var/www
COPY ./docker/nginx/apishhask.conf /etc/nginx/sites-enabled/default
COPY ./docker/entrypoint.sh /etc/entrypoint.sh

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Activa el cron
RUN echo "* * * * * root /usr/local/bin/php /var/www/artisan schedule:run >> /var/log/cron.log 2>&1" >> /etc/crontab
RUN touch /var/log/cron.log

# Agrega configuración git
RUN git config --system --add safe.directory /var/www

WORKDIR /var/www

ENTRYPOINT ["sh", "/etc/entrypoint.sh"]
