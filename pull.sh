#!/bin/bash
set -e

cd ~/public_html/vits/vits

git fetch origin production
git reset --hard origin/production
composer install --no-dev --ignore-platform-req=ext-gd

php artisan migrate --force
php artisan cache:clear
php artisan config:clear
php artisan view:clear

echo "Déploiement terminé."
