#!/bin/bash

#RUN usermod -u 1000 /var/www/

#USER /var/www/
#chown /var/www/
chmod -R 777 /var/www/backend/storage && chmod -R 777 /var/www/backend/bootstrap/cache
#chmod u+rwx backend

### frontend
npm config set cache /var/www/.npm-cache --global
cd /var/www/frontend && npm install && cd ..

#On error no such file entrypoint.sh, execute in terminal - dos2unix .docker\entrypoint.sh
### Backend
cd backend
if [ ! -f ".env" ]; then
    cp .env.example .env
fi
if [ ! -f ".env.testing" ]; then
    cp .env.example.testing .env.testing
fi

chown -R www-data:www-data .
#chmod 777 -R storage/
composer install
php artisan key:generate
php artisan migrate
php artisan cache:clear
php artisan config:cache
#php artisan serve

php-fpm
