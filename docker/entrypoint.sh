#!/bin/bash
chown -R www-data:www-data /var/www/storage/
chown -R www-data:www-data /var/www/bootstrap/cache/
composer install
composer dump-autoload
su -s /bin/sh -c "php artisan optimize" www-data
php artisan migrate

service nginx start
