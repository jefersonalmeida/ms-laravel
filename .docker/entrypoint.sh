#!/bin/sh

#On error no such file entrypoint.sh, execute in terminal - dos2unix .docker\entrypoint.sh

# FRONTEND
cd /var/www/frontend && npm install && cd ..

# BACKEND
cd backend || exit
cp -R .env.example .env
cp -R .env.testing.example .env.testing
composer install
php artisan key:generate
php artisan migrate
chmod -R 777 ./storage/
chmod -R 777 ./bootstrap/cache/
php-fpm
