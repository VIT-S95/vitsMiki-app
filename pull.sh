#!/bin/bash
set -e

cd ~/public_html/vits/vits

git fetch origin production
git reset --hard origin/production

php artisan migrate --force
php artisan cache:clear
php artisan config:clear
php artisan view:clear

echo "Déploiement terminé."
