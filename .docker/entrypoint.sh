#!/bin/bash

### frontend
npm config set cache /var/www/.npm-cache --global
cd /var/www/frontend && npm install

#On error no such file entrypoint.sh, execute in terminal - dos2unix .docker\entrypoint.sh
### Backend
cd backend
# shellcheck disable=SC1073
# shellcheck disable=SC1072
if [ ! f ".env" ]; then
    cp .env.example .env
fi
if [ ! f ".env.testing" ]; then
    cp .env.example.testing .env.testing
fi

#chown -R www-data:www-data .
chmod /// -R storage/
composer install
php artisan key:generate
php artisan migrate
php artisan cache:clear

php-fpm
