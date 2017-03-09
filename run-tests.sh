#!/usr/bin/env bash
set -e
export SYMFONY_ENV=test
export SYMFONY_DEBUG=0

cd /var/www/crm/

cp docker/php/php.ini /usr/local/etc/php/php.ini

composer install
php bin/console doctrine:schema:update --force
php bin/console doctrine:migrations:migrate --no-interaction
php vendor/bin/simple-phpunit
