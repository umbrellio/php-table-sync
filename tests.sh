#!/usr/bin/env bash

psql postgres -U user -tc "SELECT 1 FROM pg_database WHERE datname = 'testing'" | grep -q 1 || psql postgres -U user -c "CREATE DATABASE testing"
sed -e "s/\${USERNAME}/postgres/" \
    -e "s/\${PASSWORD}//" \
    -e "s/\${DATABASE}/testing/" \
    -e "s/\${HOST}/127.0.0.1/" \
    phpunit.xml.dist > phpunit.xml
composer update
composer lint-fix
php -d pcov.directory='.' vendor/bin/phpunit --coverage-html build --coverage-text
