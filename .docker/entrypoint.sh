#!/bin/bash

#On error no such file entrypoint.sh, execute in terminal - dos2unix .docker\entrypoint.sh
if [ ! -f ".env" ]; then
    cp .env.example .env
fi
if [ ! -f ".env.testing" ]; then
    cp .env.testing.example .env.testing
fi
composer install
php artisan key:generate
php artisan migrate
chmod -R 777 ./storage/
chmod -R 777 ./bootstrap/cache/

php-fpm
