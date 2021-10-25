#!/usr/bin/env bash

# composer
cd /var/www && composer install --no-scripts --no-suggest -o

# clear var
rm -rf /var/www/var

php-fpm