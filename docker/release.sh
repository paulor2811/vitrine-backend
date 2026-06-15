#!/bin/sh
set -e

php artisan migrate --force
php artisan db:seed --class=NicheSeeder --force
php artisan db:seed --class=StoreSeeder --force
