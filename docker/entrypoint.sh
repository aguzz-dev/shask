#!/bin/bash

# Establecer permisos correctos para los directorios necesarios
echo "Ajustando permisos de los directorios..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Ejecutar Composer para instalar dependencias
echo "Instalando dependencias con Composer..."
composer install --no-interaction --prefer-dist

# Optimizar autoload de Composer
echo "Generando autoload optimizado..."
composer dump-autoload --optimize

# Optimizar la configuración de Laravel
echo "Ejecutando optimización de Laravel..."
su -s /bin/sh -c "php artisan optimize" www-data

# Ejecutar migraciones de base de datos (si es necesario)
echo "Ejecutando migraciones de base de datos..."
php artisan session:table
php artisan migrate --force

# Limpiar caché de configuración y otros optimizados
echo "Limpiando caché de Laravel..."
php artisan optimize:clear
php artisan config:clear

# Iniciar el servicio de nginx
echo "Iniciando Nginx..."
service nginx start

# Iniciar PHP-FPM
echo "Iniciando PHP-FPM..."
php-fpm